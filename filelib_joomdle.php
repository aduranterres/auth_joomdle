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

/**
 * Restricted file serving for content exposed through Joomdle.
 *
 * This is intentionally limited to the file areas whose URLs are rewritten to
 * pluginfile_joomdle.php by auth_joomdle. Other Moodle file areas must continue
 * to use the standard pluginfile.php endpoint and its access checks.
 *
 * @package   auth_joomdle
 * @copyright 2009 Antonio Duran Terres
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Serves the Moodle files explicitly exposed by Joomdle.
 *
 * Course summaries and overview files are deliberately public here. This is
 * the reason for the separate endpoint: Joomla must be able to render them
 * even when the browser does not have a Moodle session and forcelogin is set.
 * Category descriptions and course sections retain Moodle's normal checks.
 *
 * @param string $relativepath File path beginning with the context id.
 * @param bool $forcedownload Whether to force downloading the file.
 * @param null|string $preview Preview mode, or null for the original file.
 * @return never
 */
function joomdle_file_pluginfile($relativepath, $forcedownload, $preview = null) {
    global $CFG, $DB;

    if (!$relativepath) {
        throw new \moodle_exception('invalidargorconf');
    } else if ($relativepath[0] !== '/') {
        throw new \moodle_exception('pathdoesnotstartslash');
    }

    $args = explode('/', ltrim($relativepath, '/'));
    if (count($args) < 3) {
        throw new \moodle_exception('invalidarguments');
    }

    $contextid = (int) array_shift($args);
    $component = clean_param(array_shift($args), PARAM_COMPONENT);
    $filearea = clean_param(array_shift($args), PARAM_AREA);
    [$context, $course] = get_context_info_array($contextid);

    $fs = get_file_storage();
    $sendfileoptions = ['preview' => $preview];

    if ($component === 'coursecat') {
        if ($context->contextlevel !== CONTEXT_COURSECAT || $filearea !== 'description') {
            send_file_not_found();
        }

        if ($CFG->forcelogin) {
            require_login();
        }

        // This also verifies that the current user may view the category.
        if (!core_course_category::get($context->instanceid, IGNORE_MISSING)) {
            send_file_not_found();
        }

        $filename = array_pop($args);
        $filepath = $args ? '/' . implode('/', $args) . '/' : '/';
        $file = $fs->get_file($context->id, 'coursecat', 'description', 0, $filepath, $filename);
        if (!$file || $file->is_directory()) {
            send_file_not_found();
        }

        \core\session\manager::write_close();
        send_stored_file($file, HOURSECS, 0, $forcedownload, $sendfileoptions);
    }

    if ($component !== 'course' || $context->contextlevel !== CONTEXT_COURSE) {
        send_file_not_found();
    }

    if ($filearea === 'summary' || $filearea === 'overviewfiles') {
        $filename = array_pop($args);
        $filepath = $args ? '/' . implode('/', $args) . '/' : '/';
        $file = $fs->get_file($context->id, 'course', $filearea, 0, $filepath, $filename);
        if (!$file || $file->is_directory()) {
            send_file_not_found();
        }

        \core\session\manager::write_close();
        send_stored_file($file, HOURSECS, 0, $forcedownload, $sendfileoptions);
    }

    if ($filearea === 'section') {
        require_login($course);

        $sectionid = (int) array_shift($args);
        if (!$DB->record_exists('course_sections', ['id' => $sectionid, 'course' => $course->id])) {
            send_file_not_found();
        }

        $filename = array_pop($args);
        $filepath = $args ? '/' . implode('/', $args) . '/' : '/';
        $file = $fs->get_file($context->id, 'course', 'section', $sectionid, $filepath, $filename);
        if (!$file || $file->is_directory()) {
            send_file_not_found();
        }

        \core\session\manager::write_close();
        send_stored_file($file, HOURSECS, 0, $forcedownload, $sendfileoptions);
    }

    send_file_not_found();
}
