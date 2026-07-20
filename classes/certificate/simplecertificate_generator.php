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
 * Generates certificates using the simple certificate activity module.
 *
 * @package    auth_joomdle
 * @copyright  2009 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class simplecertificate_generator implements generator_interface {
    /**
     * Generates a simple certificate.
     *
     * @param \stdClass $user Target Moodle user.
     * @param int $cmid Course module id.
     * @return array{filename: string, mimetype: string, content: string}
     */
    public function generate(\stdClass $user, int $cmid): array {
        global $USER;

        // This module uses the global user for notifications generated while issuing.
        $serviceuser = $USER;
        $USER = $user;
        $previouslanguage = force_current_language($user->lang);
        try {
            return $this->generate_for_user($user, $cmid);
        } finally {
            $USER = $serviceuser;
            force_current_language($previouslanguage);
        }
    }

    /**
     * Generates the certificate after establishing the target user context.
     *
     * @param \stdClass $user Target Moodle user.
     * @param int $cmid Course module id.
     * @return array{filename: string, mimetype: string, content: string}
     */
    private function generate_for_user(\stdClass $user, int $cmid): array {
        global $CFG, $DB;

        if (!\core_component::get_plugin_directory('mod', 'simplecertificate')) {
            throw new \invalid_parameter_exception('The simple certificate module is not installed');
        }

        require_once($CFG->dirroot . '/mod/simplecertificate/locallib.php');

        $cm = get_coursemodule_from_id('simplecertificate', $cmid, 0, false, MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
        $certificate = $DB->get_record('simplecertificate', ['id' => $cm->instance], '*', MUST_EXIST);
        $context = \context_module::instance($cm->id);

        if (!has_capability('mod/simplecertificate:view', $context, $user)) {
            throw new \required_capability_exception($context, 'mod/simplecertificate:view', 'nopermissions', '');
        }

        if (!\core_availability\info_module::is_user_visible($cm, $user->id)) {
            throw new \moodle_exception('cantissue', 'simplecertificate');
        }

        $canmanage = has_capability('mod/simplecertificate:manage', $context, $user);
        if ((int) $certificate->delivery === 3 && !$canmanage) {
            throw new \moodle_exception('nodelivering', 'simplecertificate');
        }

        $simplecertificate = new \simplecertificate($context, $cm, $course);
        $simplecertificate->set_instance($certificate);

        $completion = new \completion_info($course);
        if (!$canmanage && $completion->is_enabled($cm) && $certificate->requiredtime) {
            if ($simplecertificate->get_course_time($user) < $certificate->requiredtime) {
                $a = (object) ['requiredtime' => $certificate->requiredtime];
                throw new \moodle_exception('requiredtimenotmet', 'simplecertificate', '', $a);
            }
            $completion->update_state($cm, COMPLETION_COMPLETE, $user->id);
        }

        $completion->set_module_viewed($cm, $user->id);

        $issue = $simplecertificate->get_issue($user);
        $file = $simplecertificate->get_issue_file($issue);
        if (!$file) {
            throw new \moodle_exception('cannotgeneratecertificate', 'auth_joomdle');
        }

        $content = $file->get_content();
        $filename = $file->get_filename();
        $mimetype = $file->get_mimetype() ?: 'application/pdf';

        // Managers receive a temporary issue in this module; match its normal download cleanup.
        if ($canmanage) {
            $file->delete();
        }

        if (!is_string($content) || !str_starts_with($content, '%PDF-')) {
            throw new \moodle_exception('cannotgeneratecertificate', 'auth_joomdle');
        }

        return [
            'filename' => $filename,
            'mimetype' => $mimetype,
            'content' => $content,
        ];
    }
}
