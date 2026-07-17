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
 * Joomdle web services definitions
 *
 * @package    auth_joomdle
 * @copyright  2009 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(
    'joomdle_user_id' => array(     // Web service function name.
        'classname'   => 'joomdle_helpers_external', // Class containing the external function.
        'methodname'  => 'user_id', // External function name.
        'classpath'   => 'auth/joomdle/helpers/externallib.php', // File containing the class/external function.
        'description' => 'Get user id.', // Human readable description of the web service function.
        'type'        => 'read', // Database rights of the web service function (read, write).
    ),
    'joomdle_list_courses' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'list_courses',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'List courses',
        'type'        => 'read',
    ),
    'joomdle_my_courses' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'my_courses',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get user courses',
        'type'        => 'read',
    ),
    'joomdle_get_course_info' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_course_info',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get course info',
        'type'        => 'read',
    ),
    'joomdle_get_course_contents' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_course_contents',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get course topics',
        'type'        => 'read',
    ),
    'joomdle_courses_by_category' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'courses_by_category',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get courses from a category',
        'type'        => 'read',
    ),
    'joomdle_get_course_categories' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_course_categories',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get course categories',
        'type'        => 'read',
    ),
    'joomdle_get_course_editing_teachers' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_course_editing_teachers',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get course editing teachers',
        'type'        => 'read',
    ),
    'joomdle_get_upcoming_events' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_upcoming_events',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get upcoming events',
        'type'        => 'read',
    ),
    'joomdle_get_course_grade_categories' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_course_grade_categories',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get  grades categories of course',
        'type'        => 'read',
    ),
    'joomdle_search_courses' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'search_courses',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Search courses',
        'type'        => 'read',
    ),
    'joomdle_search_categories' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'search_categories',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Search course categories',
        'type'        => 'read',
    ),
    'joomdle_search_topics' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'search_topics',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Search course topics',
        'type'        => 'read',
    ),
    'joomdle_get_moodle_only_users' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_moodle_only_users',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get users existing only in moodle',
        'type'        => 'read',
    ),
    'joomdle_get_moodle_users' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_moodle_users',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get moodle users',
        'type'        => 'read',
    ),
    'joomdle_get_moodle_users_number' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_moodle_users_number',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get moodle users',
        'type'        => 'read',
    ),
    'joomdle_user_exists' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'user_exists',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Check if username exists',
        'type'        => 'read',
    ),
    'joomdle_create_joomdle_user' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'create_joomdle_user',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Creates user account',
        'type'        => 'write',
    ),
    'joomdle_enrol_user' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'enrol_user',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Enrols user into course',
        'type'        => 'write',
    ),
    'joomdle_user_details' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'user_details',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get user details',
        'type'        => 'read',
    ),
    'joomdle_user_details_by_id' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'user_details_by_id',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get user details',
        'type'        => 'read',
    ),
    'joomdle_migrate_to_joomdle' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'migrate_to_joomdle',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get user details',
        'type'        => 'write',
    ),
    'joomdle_my_events' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'my_events',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get user events',
        'type'        => 'read',
    ),
    'joomdle_delete_user' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'delete_user',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get user events',
        'type'        => 'write',
    ),
    'joomdle_system_check' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'system_check',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Joomdle system check',
        'type'        => 'read',
    ),
    'joomdle_update_session' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'update_session',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Updates user session',
        'type'        => 'write',
    ),
    'joomdle_get_cat_name' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_cat_name',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get category name',
        'type'        => 'read',
    ),
    'joomdle_courses_abc' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'courses_abc',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get course list for courses starting with chars',
        'type'        => 'read',
    ),
    'joomdle_teachers_abc' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'teachers_abc',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get teachers list for teacher names starting with chars',
        'type'        => 'read',
    ),
    'joomdle_teacher_courses' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'teacher_courses',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get courses teached by user',
        'type'        => 'read',
    ),
    'joomdle_user_custom_fields' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'user_custom_fields',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get custom fields',
        'type'        => 'read',
    ),
    'joomdle_course_enrol_methods' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'course_enrol_methods',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get course available enrolment methods',
        'type'        => 'read',
    ),
    'joomdle_get_course_students' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_course_students',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get course students',
        'type'        => 'read',
    ),
    'joomdle_suspend_enrolment' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'suspend_enrolment',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Suspend enrolment',
        'type'        => 'write',
    ),
    'joomdle_my_certificates' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'my_certificates',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get user certificates',
        'type'        => 'read',
    ),
    'joomdle_get_my_grades' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_my_grades',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get user grades',
        'type'        => 'read',
    ),
    'joomdle_multiple_enrol' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'multiple_enrol',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Enrol user in multiple courses',
        'type'        => 'write',
    ),
    'joomdle_multiple_suspend_enrolment' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'multiple_suspend_enrolment',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Un-enrol user from multiple courses',
        'type'        => 'write',
    ),
    'joomdle_get_cohorts' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_cohorts',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get  cohorts',
        'type'        => 'read',
    ),
    'joomdle_add_cohort_member' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'add_cohort_member',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Add user to cohort',
        'type'        => 'write',
    ),
    'joomdle_get_rubrics' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_rubrics',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get assignment rubrics',
        'type'        => 'read',
    ),
    'joomdle_get_grade_user_report' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_grade_user_report',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get  user grade report for a course',
        'type'        => 'read',
    ),
    'joomdle_get_my_grade_user_report' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_my_grade_user_report',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get  user grade report for all courses',
        'type'        => 'read',
    ),
    'joomdle_get_course_groups' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_course_groups',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get  course groups',
        'type'        => 'read',
    ),
    'joomdle_get_group_members' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_group_members',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get  group members',
        'type'        => 'read',
    ),
    'joomdle_remove_cohort_member' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'remove_cohort_member',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Remove user from cohort',
        'type'        => 'write',
    ),
    'joomdle_multiple_add_cohort_member' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'multiple_add_cohort_member',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Add user to cohorts',
        'type'        => 'write',
    ),
    'joomdle_multiple_remove_cohort_member' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'multiple_remove_cohort_member',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Remove user from cohorts',
        'type'        => '  ',
    ),
    'joomdle_get_courses_and_groups' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_courses_and_groups',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get courses and their groups',
        'type'        => 'read',
    ),
    'joomdle_multiple_enrol_to_course_and_group' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'multiple_enrol_to_course_and_group',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Add user to courses and groups',
        'type'        => 'write',
    ),
    'joomdle_multiple_remove_from_group' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'multiple_remove_from_group',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Removes user from groups',
        'type'        => 'write',
    ),
    'joomdle_unenrol_user' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'unenrol_user',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Unenrol user',
        'type'        => 'write',
    ),
    'joomdle_multiple_unenrol_user' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'multiple_unenrol_user',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Multiple unenrol user',
        'type'        => 'write',
    ),
    'joomdle_get_themes' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_themes',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get themes list',
        'type'        => 'read',
    ),
    'joomdle_enrol_user_with_start_and_end_date' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'enrol_user_with_start_and_end_date',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Enrols user into course with  defined start and end date',
        'type'        => 'write',
    ),
    'joomdle_my_badges' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'my_badges',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get user badges',
        'type'        => 'read',
    ),
    'joomdle_get_events' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_events',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get events',
        'type'        => 'read',
    ),
    'joomdle_get_event' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_event',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get event',
        'type'        => 'read',
    ),
    'joomdle_my_completed_courses' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'my_completed_courses',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get user completed courses',
        'type'        => 'read',
    ),
    'joomdle_get_completed_course_users' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_completed_course_users',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get list of users that completed a course',
        'type'        => 'read',
    ),
    'joomdle_change_username' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'change_username',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Change username',
        'type'        => 'write',
    ),
    'joomdle_get_moodle_version' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_moodle_version',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get Moodle version',
        'type'        => 'read',
    ),
    'joomdle_enable_user' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'enable_user',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Enable/Disable user',
        'type'        => 'write',
    ),
    'joomdle_get_course_users' => array(
        'classname'   => 'joomdle_helpers_external',
        'methodname'  => 'get_course_users',
        'classpath'   => 'auth/joomdle/helpers/externallib.php',
        'description' => 'Get course users',
        'type'        => 'read',
    ),

);
