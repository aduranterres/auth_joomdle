<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace auth_joomdle\certificate;

defined('MOODLE_INTERNAL') || die();

/**
 * Generates certificates using the custom certificate activity module.
 *
 * @package    auth_joomdle
 * @copyright  2009 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class customcert_generator implements generator_interface {
    /**
     * Generates a custom certificate.
     *
     * @param \stdClass $user Target Moodle user.
     * @param int $cmid Course module id.
     * @return array{filename: string, mimetype: string, content: string}
     */
    public function generate(\stdClass $user, int $cmid): array {
        global $DB;

        if (!\core_component::get_plugin_directory('mod', 'customcert')) {
            throw new \invalid_parameter_exception('The custom certificate module is not installed');
        }

        $cm = get_coursemodule_from_id('customcert', $cmid, 0, false, MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
        $customcert = $DB->get_record('customcert', ['id' => $cm->instance], '*', MUST_EXIST);
        $template = $DB->get_record(
            'customcert_templates',
            ['id' => $customcert->templateid],
            '*',
            MUST_EXIST
        );
        $context = \context_module::instance($cm->id);

        if (!\core_availability\info_module::is_user_visible($cm, $user->id)) {
            throw new \required_capability_exception($context, 'mod/customcert:view', 'nopermissions', '');
        }

        if (!has_capability('mod/customcert:view', $context, $user)) {
            throw new \required_capability_exception($context, 'mod/customcert:view', 'nopermissions', '');
        }

        if (!has_capability('mod/customcert:receiveissue', $context, $user)) {
            throw new \required_capability_exception($context, 'mod/customcert:receiveissue', 'nopermissions', '');
        }

        $canmanage = has_capability('mod/customcert:manage', $context, $user);
        if (
            $customcert->requiredtime && !$canmanage &&
            \mod_customcert\certificate::get_course_time($course->id, $user->id) < ($customcert->requiredtime * 60)
        ) {
            $a = (object) ['requiredtime' => $customcert->requiredtime];
            throw new \moodle_exception('requiredtimenotmet', 'customcert', '', $a);
        }

        if (!$DB->record_exists('customcert_issues', [
            'userid' => $user->id,
            'customcertid' => $customcert->id,
        ])) {
            \mod_customcert\certificate::issue_certificate($customcert->id, $user->id);
        }

        $completion = new \completion_info($course);
        $completion->set_module_viewed($cm, $user->id);

        $event = \mod_customcert\event\course_module_viewed::create([
            'objectid' => $customcert->id,
            'context' => $context,
            'userid' => $user->id,
        ]);
        $event->add_record_snapshot('course', $course);
        $event->add_record_snapshot('customcert', $customcert);
        $event->trigger();

        $template = new \mod_customcert\template($template);
        $content = $template->generate_pdf(false, $user->id, true);
        if (!is_string($content) || !str_starts_with($content, '%PDF-')) {
            throw new \moodle_exception('cannotgeneratecertificate', 'auth_joomdle');
        }

        $name = rtrim(format_string($customcert->name, true, ['context' => $context]), '.');
        $filename = clean_filename($name . '.pdf');
        if ($filename === '.pdf') {
            $filename = 'certificate.pdf';
        }

        return [
            'filename' => $filename,
            'mimetype' => 'application/pdf',
            'content' => $content,
        ];
    }
}
