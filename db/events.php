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
 * Joomdle event handlers
 *
 * @package    auth_joomdle
 * @copyright  2009 Qontori Pte Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

$observers = array(

    array(
        'eventname' => '\core\event\user_created',
        'callback' => 'auth_joomdle_handler::user_created',
        'includefile' => '/auth/joomdle/locallib.php'
    ),

    array(
        'eventname' => '\core\event\user_updated',
        'callback' => 'auth_joomdle_handler::user_updated',
        'includefile' => '/auth/joomdle/locallib.php'
    ),

    array(
        'eventname' => '\core\event\user_deleted',
        'callback' => 'auth_joomdle_handler::user_deleted',
        'includefile' => '/auth/joomdle/locallib.php'
    ),

    array(
        'eventname' => '\core\event\course_created',
        'callback' => 'auth_joomdle_handler::course_created',
        'includefile' => '/auth/joomdle/locallib.php'
    ),

    array(
        'eventname' => '\core\event\course_updated',
        'callback' => 'auth_joomdle_handler::course_updated',
        'includefile' => '/auth/joomdle/locallib.php'
    ),

    array(
        'eventname' => '\core\event\course_deleted',
        'callback' => 'auth_joomdle_handler::course_deleted',
        'includefile' => '/auth/joomdle/locallib.php'
    ),

    array(
        'eventname' => '\core\event\course_restored',
        'callback' => 'auth_joomdle_handler::course_restored',
        'includefile' => '/auth/joomdle/locallib.php'
    ),

    array(
        'eventname' => '\core\event\role_assigned',
        'callback' => 'auth_joomdle_handler::role_assigned',
        'includefile' => '/auth/joomdle/locallib.php'
    ),

    array(
        'eventname' => '\core\event\role_unassigned',
        'callback' => 'auth_joomdle_handler::role_unassigned',
        'includefile' => '/auth/joomdle/locallib.php'
    ),

    array(
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'callback' => 'auth_joomdle_handler::attempt_submitted',
        'includefile' => '/auth/joomdle/locallib.php',
        'internal' => false
    ),

    array(
        'eventname' => '\core\event\course_module_created',
        'callback' => 'auth_joomdle_handler::course_module_created',
        'includefile' => '/auth/joomdle/locallib.php'
    ),

    array(
        'eventname' => '\core\event\course_module_deleted',
        'callback' => 'auth_joomdle_handler::course_module_deleted',
        'includefile' => '/auth/joomdle/locallib.php'
    ),

    array(
        'eventname' => '\core\event\course_module_updated',
        'callback' => 'auth_joomdle_handler::course_module_updated',
        'includefile' => '/auth/joomdle/locallib.php'
    ),

    array(
        'eventname' => '\core\event\course_completed',
        'callback' => 'auth_joomdle_handler::course_completed',
        'includefile' => '/auth/joomdle/locallib.php'
    ),

    array(
        'eventname' => '\core\event\user_password_updated',
        'callback' => 'auth_joomdle_handler::user_password_updated',
        'includefile' => '/auth/joomdle/locallib.php'
    ),

);
