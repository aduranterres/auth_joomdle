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

/**
 * Generates certificates using the course certificate activity module.
 *
 * @package    auth_joomdle
 * @copyright  2009 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class coursecertificate_generator implements generator_interface {
    /**
     * Generates a course certificate.
     *
     * @param \stdClass $user Target Moodle user.
     * @param int $cmid Course module id.
     * @return array{filename: string, mimetype: string, content: string}
     */
    public function generate(\stdClass $user, int $cmid): array {
        global $DB;

        if (!\core_component::get_plugin_directory('mod', 'coursecertificate')) {
            throw new \invalid_parameter_exception('The course certificate module is not installed');
        }

        $cm = get_coursemodule_from_id('coursecertificate', $cmid, 0, false, MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
        $certificate = $DB->get_record('coursecertificate', ['id' => $cm->instance], '*', MUST_EXIST);
        $context = \context_module::instance($cm->id);

        if (!has_capability('mod/coursecertificate:view', $context, $user)) {
            throw new \required_capability_exception(
                $context,
                'mod/coursecertificate:view',
                'nopermissions',
                ''
            );
        }

        if (!\core_availability\info_module::is_user_visible($cm, $user->id)) {
            throw new \required_capability_exception(
                $context,
                'mod/coursecertificate:view',
                'nopermissions',
                ''
            );
        }

        if (!has_capability('mod/coursecertificate:receive', $context, $user)) {
            throw new \required_capability_exception(
                $context,
                'mod/coursecertificate:receive',
                'nopermissions',
                ''
            );
        }

        if (empty($certificate->template)) {
            throw new \moodle_exception('cannotgeneratecertificate', 'auth_joomdle');
        }

        \mod_coursecertificate\helper::issue_certificate($user, $certificate, $course);
        $issue = \mod_coursecertificate\helper::get_user_certificate(
            $user->id,
            $course->id,
            $certificate->template
        );
        if (!$issue) {
            throw new \moodle_exception('cannotgeneratecertificate', 'auth_joomdle');
        }

        $template = \tool_certificate\template::instance($certificate->template);
        $file = $template->get_issue_file($issue);
        $content = $file->get_content();

        if (!is_string($content) || !str_starts_with($content, '%PDF-')) {
            throw new \moodle_exception('cannotgeneratecertificate', 'auth_joomdle');
        }

        $completion = new \completion_info($course);
        $completion->set_module_viewed($cm, $user->id);

        $event = \mod_coursecertificate\event\course_module_viewed::create([
            'objectid' => $certificate->id,
            'context' => $context,
            'userid' => $user->id,
        ]);
        $event->add_record_snapshot('course', $course);
        $event->add_record_snapshot('coursecertificate', $certificate);
        $event->trigger();

        return [
            'filename' => $file->get_filename(),
            'mimetype' => $file->get_mimetype() ?: 'application/pdf',
            'content' => $content,
        ];
    }
}
