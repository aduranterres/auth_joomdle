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
 * Library functions for the Joomdle authentication plugin.
 *
 * @package   auth_joomdle
 * @copyright  2009 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Returns the available connection methods.
 *
 * @return array<string, string> Connection method names indexed by identifier.
 */
function joomdle_get_connection_methods() {
    $cms = [ 'fgc' => 'file_get_contents', 'curl' => 'cURL' ];

    return $cms;
}

/**
 * Returns a safe SQL expression for ordering course queries.
 *
 * @param string $sortby Requested sort order.
 * @return string SQL expression selected from the allowlist.
 */
function joomdle_get_course_sort_order($sortby) {
    $allowedsortfields = [
        'date' => 'co.timecreated DESC',
        'sortorder' => 'co.sortorder ASC',
        'fullname' => 'co.fullname ASC',
        'shortname' => 'co.shortname',
        'idnumber' => 'co.idnumber',
        'startdate' => 'co.startdate',
        'created' => 'co.timecreated',
        'modified' => 'co.timemodified',
        'cat_name' => 'ca.name',
        'created DESC' => 'co.timecreated DESC',
        'sortorder ASC' => 'co.sortorder ASC',
        'fullname ASC' => 'co.fullname ASC',
    ];

    return $allowedsortfields[$sortby] ?? $allowedsortfields['created'];
}
