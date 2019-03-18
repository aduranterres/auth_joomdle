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
 * Joomdle web services helper file
 *
 * @package    auth_joomdle
 * @copyright  2009 Qontori Pte Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot.'/auth/joomdle/auth.php');

class joomdle_helpers_external extends external_api {

    /* user_id */
    public static function user_id_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'multilang compatible name, course unique'),
                        )
        );
    }

    public static function user_id_returns() {
        return new  external_value(PARAM_INT, 'multilang compatible name, course unique');
    }

    public static function user_id($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::user_id_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->user_id ($username);

        return $id;
    }

    /* list_courses */
    public static function list_courses_parameters() {
        return new external_function_parameters(
                        array(
                            'enrollable_only' => new external_value(PARAM_INT, 'Return only enrollable courses'),
                            'sortby' => new external_value(PARAM_TEXT, 'Order field'),
                            'guest' => new external_value(PARAM_INT, 'Return only courses for guests'),
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function list_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'remoteid' => new external_value(PARAM_INT, 'course id'),
                    'cat_id' => new external_value(PARAM_INT, 'category id'),
                    'cat_name' => new external_value(PARAM_TEXT, 'cartegory name'),
                    'cat_description' => new external_value(PARAM_RAW, 'category description'),
                    'sortorder' => new external_value(PARAM_TEXT, 'sortorder'),
                    'fullname' => new external_value(PARAM_TEXT, 'course name'),
                    'shortname' => new external_value(PARAM_TEXT, 'course shortname'),
                    'idnumber' => new external_value(PARAM_RAW, 'idnumber'),
                    'summary' => new external_value(PARAM_RAW, 'summary'),
                    'startdate' => new external_value(PARAM_INT, 'start date'),
                    'created' => new external_value(PARAM_INT, 'created'),
                    'modified' => new external_value(PARAM_INT, 'modified'),
                    'cost' => new external_value(PARAM_FLOAT, 'cost', VALUE_OPTIONAL),
                    'currency' => new external_value(PARAM_TEXT, 'currency', VALUE_OPTIONAL),
                    'self_enrolment' => new external_value(PARAM_INT, 'self enrollable'),
                    'enroled' => new external_value(PARAM_INT, 'user enroled'),
                    'in_enrol_date' => new external_value(PARAM_BOOL, 'in enrol date'),
                    'guest' => new external_value(PARAM_INT, 'guest access'),
                    'summary_files' => new external_multiple_structure(
                        new external_single_structure(
                           array(
                              'url' => new external_value(PARAM_TEXT, 'item url'),
                           )
                        )
                     )
                )
            )
        );
    }

    public static function list_courses($enrollable_only, $sortby, $guest, $username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::list_courses_parameters(),
              array('enrollable_only' => $enrollable_only, 'sortby' => $sortby, 'guest' => $guest, 'username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->list_courses ($enrollable_only, $sortby, $guest, $username);

        return $id;
    }

    /* test */
    public static function test_parameters() {
        return new external_function_parameters(
                        array(
                        )
        );
    }

    public static function test_returns() {
        return  new external_value(PARAM_TEXT, 'test data');
    }

    public static function test() {
        global $CFG, $DB;

        $params = self::validate_parameters(self::test_parameters(), array());

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->test ();

        return $return;
    }

    /* my_courses */
    public static function my_courses_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'Username'),
                            'order_by_cat' => new external_value(PARAM_INT, 'order by category'),
                        )
        );
    }

    public static function my_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'group record id'),
                    'fullname' => new external_value(PARAM_TEXT, 'course name'),
                    'summary' => new external_value(PARAM_RAW, 'summary'),
                    'category' => new external_value(PARAM_INT, 'course category id'),
                    'cat_name' => new external_value(PARAM_TEXT, 'course category name'),
                    'can_unenrol' => new external_value(PARAM_INT, 'user can self unenrol'),
                    'summary_files' => new external_multiple_structure(
                        new external_single_structure(
                           array(
                              'url' => new external_value(PARAM_TEXT, 'item url'),
                           )
                        )
                     )
                )
            )
        );
    }

    public static function my_courses($username, $order_by_cat) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::my_courses_parameters(),
                array('username' => $username, 'order_by_cat' => $order_by_cat));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->my_courses ($username, $order_by_cat);

        return $return;
    }


    /* get_course_info */
    public static function get_course_info_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function get_course_info_returns() {
            return new external_single_structure(
                array(
                    'remoteid' => new external_value(PARAM_INT, 'course id'),
                    'cat_id' => new external_value(PARAM_INT, 'category id'),
                    'cat_name' => new external_value(PARAM_TEXT, 'category name'),
                    'cat_description' => new external_value(PARAM_RAW, 'category description'),
                    'sortorder' => new external_value(PARAM_TEXT, 'category name'),
                    'fullname' => new external_value(PARAM_TEXT, 'course name'),
                    'shortname' => new external_value(PARAM_TEXT, 'course name'),
                    'idnumber' => new external_value(PARAM_RAW, 'category name'),
                    'summary' => new external_value(PARAM_RAW, 'summary'),
                    'startdate' => new external_value(PARAM_INT, 'start date'),
                    'enddate' => new external_value(PARAM_INT, 'end date'),
                    'numsections' => new external_value(PARAM_INT, 'number of sections'),
                    'lang' => new external_value(PARAM_RAW, 'lang'),
                    'cost' => new external_value(PARAM_FLOAT, 'cost', VALUE_OPTIONAL),
                    'currency' => new external_value(PARAM_TEXT, 'currency', VALUE_OPTIONAL),
                    'enrolstartdate' => new external_value(PARAM_INT, 'enrol start date', VALUE_OPTIONAL),
                    'enrolenddate' => new external_value(PARAM_INT, 'enrol end date', VALUE_OPTIONAL),
                    'enrolperiod' => new external_value(PARAM_INT, 'enrol duration', VALUE_OPTIONAL),
                    'self_enrolment' => new external_value(PARAM_INT, 'self enrollable'),
                    'enroled' => new external_value(PARAM_INT, 'user enroled'),
                    'in_enrol_date' => new external_value(PARAM_BOOL, 'in enrol date'),
                    'guest' => new external_value(PARAM_INT, 'guest access'),
                    'summary_files' => new external_multiple_structure(
                        new external_single_structure(
                           array(
                              'url' => new external_value(PARAM_TEXT, 'item url'),
                           )
                        )
                     )
                )
            );
    }

    public static function get_course_info($id, $username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_course_info_parameters(), array('id' => $id, 'username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_course_info ($id, $username);

        return $return;
    }

    /* get_course_contents */
    public static function get_course_contents_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                        )
        );
    }

    public static function get_course_contents_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'section' => new external_value(PARAM_INT, 'section id'),
                  'name' => new external_value(PARAM_TEXT, 'section name'),
                  'summary' => new external_value(PARAM_RAW, 'summary'),
               )
            )
            );
    }

    public static function get_course_contents($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_course_contents_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_course_contents ($id);

        return $return;
    }

    /* courses_by_category */
    public static function courses_by_category_parameters() {
        return new external_function_parameters(
                        array(
                            'category' => new external_value(PARAM_INT, 'category id'),
                            'enrollable_only' => new external_value(PARAM_INT, 'Return only enrollable courses'),
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function courses_by_category_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'remoteid' => new external_value(PARAM_INT, 'course id'),
                  'cat_id' => new external_value(PARAM_INT, 'category id'),
                  'cat_name' => new external_value(PARAM_TEXT, 'category name'),
                  'cat_description' => new external_value(PARAM_RAW, 'category description'),
                  'fullname' => new external_value(PARAM_TEXT, 'course name'),
                  'summary' => new external_value(PARAM_RAW, 'course summary'),
                        'idnumber' => new external_value(PARAM_RAW, 'idnumber'),
                  'startdate' => new external_value(PARAM_INT, 'start date'),
                  'cost' => new external_value(PARAM_FLOAT, 'cost', VALUE_OPTIONAL),
                  'currency' => new external_value(PARAM_TEXT, 'currency', VALUE_OPTIONAL),
                  'self_enrolment' => new external_value(PARAM_INT, 'self enrollable'),
                  'enroled' => new external_value(PARAM_INT, 'user enroled'),
                  'in_enrol_date' => new external_value(PARAM_BOOL, 'in enrol date'),
                  'guest' => new external_value(PARAM_INT, 'guest access'),
                  'summary_files' => new external_multiple_structure(
                           new external_single_structure(
                              array(
                                 'url' => new external_value(PARAM_TEXT, 'item url'),
                              )
                           )
                        )
               )
            )
        );
    }

    public static function courses_by_category($category, $enrollable_only, $username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::courses_by_category_parameters(),
                array('category' => $category, 'enrollable_only' => $enrollable_only, 'username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->courses_by_category ($category, $enrollable_only, $username);

        return $return;
    }


    /* get_course_categories */
    public static function get_course_categories_parameters() {
        return new external_function_parameters(
                        array(
                            'category' => new external_value(PARAM_INT, 'category id'),
                        )
        );
    }

    public static function get_course_categories_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'id' => new external_value(PARAM_INT, 'category id'),
                  'name' => new external_value(PARAM_TEXT, 'category name'),
                  'description' => new external_value(PARAM_RAW, 'description'),
               )
            )
        );
    }

    public static function get_course_categories($category) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_course_categories_parameters(), array('category' => $category));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_course_categories ($category);

        return $return;
    }

    /* get_course_editing_teachers */
    public static function get_course_editing_teachers_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                        )
        );
    }

    public static function get_course_editing_teachers_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'firstname' => new external_value(PARAM_TEXT, 'firstname'),
                  'lastname' => new external_value(PARAM_TEXT, 'lastname'),
                  'username' => new external_value(PARAM_TEXT, 'username'),
               )
            )
            );
    }

    public static function get_course_editing_teachers($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_course_editing_teachers_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_course_editing_teachers ($id);

        return $return;
    }

    /* get_course_no */
    public static function get_course_no_parameters() {
        return new external_function_parameters(
                        array(
                        )
        );
    }

    public static function get_course_no_returns() {
        return new  external_value(PARAM_INT, 'number of courses');
    }

    public static function get_course_no() {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_course_no_parameters(), array());

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_course_no ();

        return $return;
    }

    /* get_enrollable_course_no */
    public static function get_enrollable_course_no_parameters() {
        return new external_function_parameters(
                        array(
                        )
        );
    }

    public static function get_enrollable_course_no_returns() {
        return new  external_value(PARAM_INT, 'number of courses');
    }

    public static function get_enrollable_course_no() {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_enrollable_course_no_parameters(), array());

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_enrollable_course_no ();

        return $return;
    }


    /* get_student_no */
    public static function get_student_no_parameters() {
        return new external_function_parameters(
                        array(
                        )
        );
    }

    public static function get_student_no_returns() {
        return new  external_value(PARAM_INT, 'number of students');
    }

    public static function get_student_no() {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_student_no_parameters(), array());

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_student_no ();

        return $return;
    }

    /* get_total_assignment_submissions */
    public static function get_total_assignment_submissions_parameters() {
        return new external_function_parameters(
                        array(
                        )
        );
    }

    public static function get_total_assignment_submissions_returns() {
        return new  external_value(PARAM_INT, 'number of submitted assingments');
    }

    public static function get_total_assignment_submissions() {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_total_assignment_submissions_parameters(), array());

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_total_assignment_submissions ();

        return $return;
    }


    /* get_course_student_no */
    public static function get_course_students_no_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                        )
        );
    }

    public static function get_course_students_no_returns() {
        return new  external_value(PARAM_INT, 'number of submitted assingments');
    }

    public static function get_course_students_no($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_course_students_no_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_course_students_no ($id);

        return $return;
    }

    /* get_assignment_submissions */
    public static function get_assignment_submissions_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                        )
        );
    }

    public static function get_assignment_submissions_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'id' => new external_value(PARAM_INT, 'id'),
                  'tarea' => new external_value(PARAM_TEXT, 'task'),
                  'entregados' => new external_value(PARAM_INT, 'submitted'),
               )
            )
        );
    }

    public static function get_assignment_submissions($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_assignment_submissions_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_assignment_submissions ($id);

        return $return;
    }

    /* get_assignment_grades */
    public static function get_assignment_grades_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                        )
        );
    }

    public static function get_assignment_grades_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'tarea' => new external_value(PARAM_TEXT, 'task'),
                  'media' => new external_value(PARAM_FLOAT, 'submitted'),
               )
            )
            );
    }

    public static function get_assignment_grades($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_assignment_grades_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_assignment_grades ($id);

        return $return;
    }


    /* get_upcoming_events */
    public static function get_upcoming_events_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                        )
        );
    }

    public static function get_upcoming_events_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'name' => new external_value(PARAM_TEXT, 'event name'),
                  'timestart' => new external_value(PARAM_INT, 'start time'),
                  'courseid' => new external_value(PARAM_INT, 'course id'),
               )
            )
            );
    }

    public static function get_upcoming_events($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_upcoming_events_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_upcoming_events ($id);

        return $return;
    }

    /* get_news_items */
    public static function get_news_items_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                        )
        );
    }

    public static function get_news_items_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'discussion' => new external_value(PARAM_INT, 'discussion id'),
                  'subject' => new external_value(PARAM_TEXT, 'subject'),
                  'timemodified' => new external_value(PARAM_INT, 'timemodified'),
               )
            )
            );
    }

    public static function get_news_items($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_news_items_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_news_items ($id);

        return $return;
    }

    /* get_user_grades */
    public static function get_user_grades_parameters() {
        return new external_function_parameters(
                        array(
                            'user' => new external_value(PARAM_TEXT, 'username'),
                            'id' => new external_value(PARAM_INT, 'course id'),
                        )
        );
    }

    public static function get_user_grades_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'itemname' => new external_value(PARAM_TEXT, 'item name'),
                  'finalgrade' => new external_value(PARAM_TEXT, 'final grade'),
               )
            )
        );
    }

    public static function get_user_grades($user, $id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_user_grades_parameters(), array('user' => $user, 'id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_user_grades ($user, $id);

        return $return;
    }


    /* get_course_grade_categories */
    public static function get_course_grade_categories_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                        )
        );
    }

    public static function get_course_grade_categories_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'fullname' => new external_value(PARAM_TEXT, 'item name'),
                  'grademax' => new external_value(PARAM_TEXT, 'final grade'),
               )
            )
        );
    }

    public static function get_course_grade_categories($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_course_grade_categories_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_course_grade_categories ($id);

        return $return;
    }

    /* get_course_grade_categories_and_items */
    public static function get_course_grade_categories_and_items_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                        )
        );
    }

    public static function get_course_grade_categories_and_items_returns() {
         return new external_multiple_structure(
                new external_single_structure(
                    array(
                        'fullname' => new external_value(PARAM_TEXT, 'item name'),
                        'grademax' => new external_value(PARAM_TEXT, 'final grade'),
                  'items' => new external_multiple_structure(
                        new external_single_structure(
                           array(
                              'name' => new external_value(PARAM_TEXT, 'item name'),
                              'due' => new external_value(PARAM_INT, 'due date'),
                              'has_rubrics' => new external_value(PARAM_BOOL, 'has rubrics'),
                              'id' => new external_value(PARAM_INT, 'id'),
                           )
                        )
                     )
                    )
                )
            );
    }

    public static function get_course_grade_categories_and_items($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_course_grade_categories_and_items_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_course_grade_categories_and_items ($id);

        return $return;
    }


    /* search_courses */
    public static function search_courses_parameters() {
        return new external_function_parameters(
                        array(
                            'text' => new external_value(PARAM_TEXT, 'text to search'),
                            'phrase' => new external_value(PARAM_TEXT, 'search type'),
                            'ordering' => new external_value(PARAM_TEXT, 'order'),
                            'limit' => new external_value(PARAM_TEXT, 'limit'),
                        )
        );
    }

    public static function search_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'remoteid' => new external_value(PARAM_INT, 'course id'),
                  'cat_id' => new external_value(PARAM_INT, 'category id'),
                  'cat_name' => new external_value(PARAM_TEXT, 'category name'),
                  'cat_description' => new external_value(PARAM_RAW, 'category description'),
                  'sortorder' => new external_value(PARAM_TEXT, 'category name'),
                  'fullname' => new external_value(PARAM_TEXT, 'course name'),
                  'shortname' => new external_value(PARAM_TEXT, 'course name'),
                  'idnumber' => new external_value(PARAM_RAW, 'category name'),
                  'summary' => new external_value(PARAM_RAW, 'summary'),
                  'startdate' => new external_value(PARAM_INT, 'start date'),
               )
            )
        );
    }

    public static function search_courses($text, $phrase, $ordering, $limit) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::search_courses_parameters(),
                array('text' => $text, 'phrase' => $phrase, 'ordering' => $ordering, 'limit' => $limit));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->search_courses ($text, $phrase, $ordering, $limit);

        return $return;
    }


    /* search_courses */
    public static function search_categories_parameters() {
        return new external_function_parameters(
                        array(
                            'text' => new external_value(PARAM_TEXT, 'text to search'),
                            'phrase' => new external_value(PARAM_TEXT, 'search type'),
                            'ordering' => new external_value(PARAM_TEXT, 'order'),
                            'limit' => new external_value(PARAM_TEXT, 'limit'),
                        )
        );
    }

    public static function search_categories_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'cat_id' => new external_value(PARAM_INT, 'category id'),
                  'cat_name' => new external_value(PARAM_TEXT, 'category name'),
                  'cat_description' => new external_value(PARAM_RAW, 'category description'),
               )
            )
            );
    }
    public static function search_categories($text, $phrase, $ordering, $limit) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::search_categories_parameters(),
                array('text' => $text, 'phrase' => $phrase, 'ordering' => $ordering, 'limit' => $limit));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->search_categories ($text, $phrase, $ordering, $limit);

        return $return;
    }

    /* search_topics */
    public static function search_topics_parameters() {
        return new external_function_parameters(
                        array(
                            'text' => new external_value(PARAM_TEXT, 'text to search'),
                            'phrase' => new external_value(PARAM_TEXT, 'search type'),
                            'ordering' => new external_value(PARAM_TEXT, 'order'),
                            'limit' => new external_value(PARAM_TEXT, 'limit'),
                        )
        );
    }

    public static function search_topics_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'remoteid' => new external_value(PARAM_INT, 'course id'),
                  'fullname' => new external_value(PARAM_TEXT, 'course name'),
                  'course' => new external_value(PARAM_TEXT, 'course name'),
                  'section' => new external_value(PARAM_TEXT, 'course name'),
                  'summary' => new external_value(PARAM_RAW, 'summary'),
                  'cat_id' => new external_value(PARAM_INT, 'category id'),
                  'cat_name' => new external_value(PARAM_TEXT, 'category name'),
               )
            )
            );
    }
    public static function search_topics($text, $phrase, $ordering, $limit) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::search_topics_parameters(),
                array('text' => $text, 'phrase' => $phrase, 'ordering' => $ordering, 'limit' => $limit));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->search_topics ($text, $phrase, $ordering, $limit);

        return $return;
    }

    /* get_my_courses_grades */
    public static function get_my_courses_grades_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function get_my_courses_grades_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'id' => new external_value(PARAM_INT, 'course id'),
                  'fullname' => new external_value(PARAM_TEXT, 'course name'),
                  'cat_id' => new external_value(PARAM_INT, 'category id'),
                  'cat_name' => new external_value(PARAM_TEXT, 'category name'),
                  'avg' => new external_value(PARAM_TEXT, 'average grade'),
               )
            )
            );
    }

    public static function get_my_courses_grades($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_my_courses_grades_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_my_courses_grades ($username);

        return $return;
    }

    /* check_moodle_users */
    public static function check_moodle_users_parameters() {
        return new external_function_parameters(
                        array(
                            'users' => new external_multiple_structure(
                              new external_single_structure(
                                 array(
                                    'username' => new external_value(PARAM_TEXT, 'username'),
                                 )
                              )
                        )
            )
        );
    }

    public static function check_moodle_users_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'username' => new external_value(PARAM_TEXT, 'username'),
                  'admin' => new external_value(PARAM_INT, 'admin user'),
                  'auth' => new external_value(PARAM_TEXT, 'auth plugin'),
                  'm_account' => new external_value(PARAM_INT, 'moodle account'),
               )
            )
        );
    }

    public static function check_moodle_users($users) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::check_moodle_users_parameters(), array('users' => $users));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->check_moodle_users ($users);

        return $return;
    }

    /* get_moodle_only_users */
    public static function get_moodle_only_users_parameters() {
        return new external_function_parameters(
                        array(
                            'users' => new external_multiple_structure(
                              new external_single_structure(
                                 array(
                                    'username' => new external_value(PARAM_TEXT, 'username'),
                                 )
                              )
                           ),
                        'search' => new external_value(PARAM_TEXT, 'sarch text'),
                  )
            );
    }

    public static function get_moodle_only_users_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'id' => new external_value(PARAM_INT, 'user id'),
                  'username' => new external_value(PARAM_TEXT, 'username'),
                  'email' => new external_value(PARAM_TEXT, 'email'),
                  'name' => new external_value(PARAM_TEXT, 'name'),
                  'auth' => new external_value(PARAM_TEXT, 'auth plugin'),
                  'admin' => new external_value(PARAM_INT, 'admin user'),
               )
            )
        );
    }

    public static function get_moodle_only_users($users, $search) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_moodle_only_users_parameters(),
                array('users' => $users, 'search' => $search));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_moodle_only_users ($users, $search);

        return $return;
    }

    /* get_moodle_users */
    public static function get_moodle_users_parameters() {
        return new external_function_parameters(
                        array(
                     'limitstart' => new external_value(PARAM_INT, 'limit start'),
                     'limit' => new external_value(PARAM_INT, 'limit'),
                     'order' => new external_value(PARAM_TEXT, 'order'),
                     'order_dir' => new external_value(PARAM_TEXT, 'order dir'),
                     'search' => new external_value(PARAM_TEXT, 'search text'),
                  )
            );
    }

    public static function get_moodle_users_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'id' => new external_value(PARAM_INT, 'user id'),
                  'username' => new external_value(PARAM_TEXT, 'username'),
                  'email' => new external_value(PARAM_TEXT, 'email'),
                  'name' => new external_value(PARAM_TEXT, 'name'),
                  'auth' => new external_value(PARAM_TEXT, 'auth plugin'),
                  'admin' => new external_value(PARAM_INT, 'admin user'),
               )
            )
        );
    }

    public static function get_moodle_users($limitstart, $limit, $order, $order_dir, $search) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_moodle_users_parameters(),
                array('limitstart' => $limitstart, 'limit' => $limit, 'order' => $order,
                    'order_dir' => $order_dir, 'search' => $search));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_moodle_users ($limitstart, $limit, $order, $order_dir, $search);

        return $return;
    }

    /* get_moodle_users_number */
    public static function get_moodle_users_number_parameters() {
        return new external_function_parameters(
                        array(
                     'search' => new external_value(PARAM_TEXT, 'sarch text'),
                  )
            );
    }

    public static function get_moodle_users_number_returns() {
        return new  external_value(PARAM_INT, 'user number');
    }

    public static function get_moodle_users_number($search) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_moodle_users_number_parameters(), array('search' => $search));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_moodle_users_number ($search);

        return $return;
    }

    /* user_exists */
    public static function user_exists_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'multilang compatible name, course unique'),
                        )
        );
    }

    public static function user_exists_returns() {
        return new  external_value(PARAM_INT, 'whether user exists');
    }

    public static function user_exists($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::user_exists_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->user_exists ($username);

        return $id;
    }

    /* create_joomdle_user */
    public static function create_joomdle_user_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function create_joomdle_user_returns() {
        return new  external_value(PARAM_INT, 'user created');
    }

    public static function create_joomdle_user($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::create_joomdle_user_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->create_joomdle_user ($username);

        return $id;
    }

    /* create_joomdle_user_additional */
    public static function create_joomdle_user_additional_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                            'app' => new external_value(PARAM_TEXT, 'app'),
                        )
        );
    }

    public static function create_joomdle_user_additional_returns() {
        return new  external_value(PARAM_INT, 'user created');
    }

    public static function create_joomdle_user_additional($username, $app) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::create_joomdle_user_additional_parameters(),
                array('username' => $username, 'app' => $app));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->create_joomdle_user_additional ($username, $app);

        return $id;
    }

    /* enrol_user */
    public static function enrol_user_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                            'id' => new external_value(PARAM_INT, 'course id'),
                            'roleid' => new external_value(PARAM_INT, 'role id'),
                        )
        );
    }

    public static function enrol_user_returns() {
        return new  external_value(PARAM_INT, 'user created');
    }

    public static function enrol_user($username, $id, $roleid) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::enrol_user_parameters(),
                array('username' => $username, 'id' => $id, 'roleid' => $roleid));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->enrol_user ($username, $id, $roleid);

        return $id;
    }

    /* multiple_enrol_and_addtogroup */
    public static function multiple_enrol_and_addtogroup_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                            'courses' => new external_value(PARAM_TEXT, 'course shortnames'),
                            'groups' => new external_value(PARAM_TEXT, 'group names'),
                            'roleid' => new external_value(PARAM_INT, 'role id'),
                        )
        );
    }

    public static function multiple_enrol_and_addtogroup_returns() {
        return new  external_value(PARAM_INT, 'user enroled');
    }

    public static function multiple_enrol_and_addtogroup($username, $courses, $groups, $roleid) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::multiple_enrol_and_addtogroup_parameters(),
                array('username' => $username, 'courses' => $courses, 'groups' => $groups, 'roleid' => $roleid));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->multiple_enrol_and_addtogroup ($username, $courses, $groups, $roleid);

        return $id;
    }

    /* multiple_enrol */
    public static function multiple_enrol_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                     'courses' => new external_multiple_structure(
                           new external_single_structure(
                              array(
                                 'id' => new external_value(PARAM_INT, 'course id'),
                              )
                           )
                        ),
                            'roleid' => new external_value(PARAM_INT, 'role id'),
                        )
        );
    }

    public static function multiple_enrol_returns() {
        return new  external_value(PARAM_INT, 'user enroled');
    }

    public static function multiple_enrol($username, $courses, $roleid) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::multiple_enrol_parameters(),
                array('username' => $username, 'courses' => $courses, 'roleid' => $roleid));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->multiple_enrol ($username, $courses, $roleid);

        return $id;
    }

    /* user_details */
    public static function user_details_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function user_details_returns() {
        return    new external_single_structure(
               array(
                  'username' => new external_value(PARAM_TEXT, 'username'),
                  'firstname' => new external_value(PARAM_TEXT, 'firstname'),
                  'lastname' => new external_value(PARAM_TEXT, 'lastname'),
                  'email' => new external_value(PARAM_TEXT, 'email'),
                  'id' => new external_value(PARAM_INT, 'id'),
                  'name' => new external_value(PARAM_TEXT, 'name', VALUE_OPTIONAL),
                  'city' => new external_value(PARAM_TEXT, 'city', VALUE_OPTIONAL),
                  'country' => new external_value(PARAM_TEXT, 'country', VALUE_OPTIONAL),
                  'lang' => new external_value(PARAM_TEXT, 'lang', VALUE_OPTIONAL),
                  'timezone' => new external_value(PARAM_TEXT, 'timezone', VALUE_OPTIONAL),
                  'phone1' => new external_value(PARAM_TEXT, 'phone1', VALUE_OPTIONAL),
                  'phone2' => new external_value(PARAM_TEXT, 'phone2', VALUE_OPTIONAL),
                  'address' => new external_value(PARAM_TEXT, 'address', VALUE_OPTIONAL),
                  'description' => new external_value(PARAM_RAW, 'description', VALUE_OPTIONAL),
                  'institution' => new external_value(PARAM_TEXT, 'institution', VALUE_OPTIONAL),
                  'url' => new external_value(PARAM_TEXT, 'url', VALUE_OPTIONAL),
                  'icq' => new external_value(PARAM_TEXT, 'icq', VALUE_OPTIONAL),
                  'skype' => new external_value(PARAM_TEXT, 'skype', VALUE_OPTIONAL),
                  'aim' => new external_value(PARAM_TEXT, 'aim', VALUE_OPTIONAL),
                  'yahoo' => new external_value(PARAM_TEXT, 'yahoo', VALUE_OPTIONAL),
                  'msn' => new external_value(PARAM_TEXT, 'msn', VALUE_OPTIONAL),
                  'idnumber' => new external_value(PARAM_TEXT, 'idnumber', VALUE_OPTIONAL),
                  'department' => new external_value(PARAM_TEXT, 'department', VALUE_OPTIONAL),
                  'picture' => new external_value(PARAM_TEXT, 'picture', VALUE_OPTIONAL),
                  'pic_url' => new external_value(PARAM_TEXT, 'pic url', VALUE_OPTIONAL),
                  'lastnamephonetic' => new external_value(PARAM_TEXT, 'lastnamephonetic', VALUE_OPTIONAL),
                  'firstnamephonetic' => new external_value(PARAM_TEXT, 'firstnamephonetic', VALUE_OPTIONAL),
                  'middlename' => new external_value(PARAM_TEXT, 'middlename', VALUE_OPTIONAL),
                  'alternatename' => new external_value(PARAM_TEXT, 'alternatename', VALUE_OPTIONAL),
                  'password' => new external_value(PARAM_TEXT, 'password', VALUE_OPTIONAL),
                  'custom_fields' => new external_multiple_structure(
                        new external_single_structure(
                           array(
                              'id' => new external_value(PARAM_INT, 'field id'),
                              'data' => new external_value(PARAM_RAW, 'data')
                           )
                        )
                     )
               )
            );
    }

    public static function user_details($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::user_details_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->user_details ($username);

        return $id;
    }

    /* user_details_by_id */
    public static function user_details_by_id_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'user id'),
                        )
        );
    }

    public static function user_details_by_id_returns() {
        return    new external_single_structure(
               array(
                  'username' => new external_value(PARAM_TEXT, 'username'),
               )
            );
    }

    public static function user_details_by_id($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::user_details_by_id_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->user_details_by_id ($id);

        return $id;
    }

    /* migrate_to_joomdle */
    public static function migrate_to_joomdle_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username')
                        )
        );
    }

    public static function migrate_to_joomdle_returns() {
        return new  external_value(PARAM_BOOL, 'user migrated');
    }

    public static function migrate_to_joomdle($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::migrate_to_joomdle_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->migrate_to_joomdle ($username);

        return $id;
    }

    /* my_events */
    public static function my_events_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                            'courses' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                   'id' => new external_value(PARAM_INT, 'course id'),
                                    )
                                 )
                              )
                        )
        );
    }

    public static function my_events_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'name' => new external_value(PARAM_TEXT, 'event name'),
                    'timestart' => new external_value(PARAM_INT, 'start time'),
                    'courseid' => new external_value(PARAM_INT, 'course id'),
                )
            )
        );
    }
    public static function my_events($username, $courses) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::my_events_parameters(),
                array('username' => $username, 'courses' => $courses));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->my_events ($username, $courses);

        return $id;
    }

    /* delete_user */
    public static function delete_user_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function delete_user_returns() {
        return new  external_value(PARAM_BOOL, 'user deleted');
    }

    public static function delete_user($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::delete_user_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->delete_user ($username);

        return $id;
    }

    /* get_mentees */
    public static function get_mentees_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function get_mentees_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'id' => new external_value(PARAM_INT, 'user id'),
                  'username' => new external_value(PARAM_TEXT, 'username'),
                  'name' => new external_value(PARAM_TEXT, 'name'),
               )
            )
        );
    }

    public static function get_mentees($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_mentees_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->get_mentees ($username);

        return $id;
    }

    /* get_roles */
    public static function get_roles_parameters() {
        return new external_function_parameters(
                        array(
                        )
        );
    }

    public static function get_roles_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'id' => new external_value(PARAM_INT, 'role id'),
                  'name' => new external_value(PARAM_TEXT, 'role name'),
               )
            )
        );
    }

    public static function get_roles() {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_roles_parameters(), array());

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->get_roles ();

        return $id;
    }

    /* get_parents */
    public static function get_parents_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function get_parents_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'username' => new external_value(PARAM_TEXT, 'username'),
               )
            )
        );
    }

    public static function get_parents($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_parents_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->get_parents ($username);

        return $id;
    }

    /* get_site_last_week_stats */
    public static function get_site_last_week_stats_parameters() {
        return new external_function_parameters(
                        array(
                        )
        );
    }

    public static function get_site_last_week_stats_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'stat1' => new external_value(PARAM_INT, 'reads'),
                    'stat2' => new external_value(PARAM_INT, 'writes'),
                )
            )
        );
    }

    public static function get_site_last_week_stats() {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_site_last_week_stats_parameters(), array());

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_site_last_week_stats ();

        return $return;
    }

    /* get_course_daily_stats */
    public static function get_course_daily_stats_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                        )
        );
    }

    public static function get_course_daily_stats_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'stat1' => new external_value(PARAM_INT, 'reads'),
                    'stat2' => new external_value(PARAM_INT, 'writes'),
                    'timeend' => new external_value(PARAM_INT, 'time'),
                )
            )
        );
    }

    public static function get_course_daily_stats($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_course_daily_stats_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_course_daily_stats ($id);

        return $return;
    }

    /* get_last_user_grades */
    public static function get_last_user_grades_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                     'limit' => new external_value(PARAM_INT, 'max items to return'),
                        )
        );
    }

    public static function get_last_user_grades_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'itemname' => new external_value(PARAM_TEXT, 'task name'),
                  'finalgrade' => new external_value(PARAM_TEXT, 'grade'),
                  'average' => new external_value(PARAM_TEXT, 'average'),
               )
            )
        );
    }

    public static function get_last_user_grades($username, $limit) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_last_user_grades_parameters(),
                array('username' => $username, 'limit' => $limit));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->get_last_user_grades ($username, $limit);

        return $id;
    }

    /* system_check */
    public static function system_check_parameters() {
        return new external_function_parameters(
                        array(
                        )
        );
    }

    public static function system_check_returns() {
        return new external_single_structure(
                array(
                    'joomdle_auth' => new external_value(PARAM_INT, 'joomdle plugin enabled'),
                    'mnet_auth' => new external_value(PARAM_INT, 'mnet plugin enabled'),
                    'joomdle_configured' => new external_value(PARAM_INT, 'joomdle configured'),
                    'test_data' => new external_value(PARAM_RAW, 'test data', VALUE_OPTIONAL),
                    'release' => new external_value(PARAM_TEXT, 'Joomdle release'),
                )
        );
    }

    public static function system_check() {
        global $CFG, $DB;

        $params = self::validate_parameters(self::system_check_parameters(), array());

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->system_check ();

        return $return;
    }

    /* add_parent_role */
    public static function add_parent_role_parameters() {
        return new external_function_parameters(
                        array(
                            'child' => new external_value(PARAM_TEXT, 'child username'),
                            'parent' => new external_value(PARAM_TEXT, ' parent username'),
                        )
        );
    }

    public static function add_parent_role_returns() {
        return new  external_value(PARAM_TEXT, 'role added');
    }

    public static function add_parent_role($child, $parent) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::add_parent_role_parameters(),
                array('child' => $child, 'parent' => $parent));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->add_parent_role ($child, $parent);

        return $id;
    }

    /* get_paypal_config */
    public static function get_paypal_config_parameters() {
        return new external_function_parameters(
                        array(
                        )
        );
    }

    public static function get_paypal_config_returns() {
        return    new external_single_structure(
               array(
                  'paypalurl' => new external_value(PARAM_TEXT, 'paypal url'),
                  'paypalbusiness' => new external_value(PARAM_TEXT, 'paypal email'),
               )
            );
    }

    public static function get_paypal_config() {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_paypal_config_parameters(), array());

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->get_paypal_config ();

        return $id;
    }

    /* update_session */
    public static function update_session_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function update_session_returns() {
        return new  external_value(PARAM_BOOL, 'session updated');
    }

    public static function update_session($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::update_session_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->update_session ($username);

        return $id;
    }

    /* get_cat_name */
    public static function get_cat_name_parameters() {
        return new external_function_parameters(
                        array(
                            'cat_id' => new external_value(PARAM_INT, 'category id'),
                        )
        );
    }

    public static function get_cat_name_returns() {
        return new  external_value(PARAM_TEXT, 'category name');
    }

    public static function get_cat_name($cat_id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_cat_name_parameters(), array('cat_id' => $cat_id));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->get_cat_name ($cat_id);

        return $id;
    }

    /* courses_abc */
    public static function courses_abc_parameters() {
        return new external_function_parameters(
                        array(
                            'start_chars' => new external_value(PARAM_TEXT, 'Start chars'),
                     'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function courses_abc_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'remoteid' => new external_value(PARAM_INT, 'course id'),
                    'cat_id' => new external_value(PARAM_INT, 'category id'),
                    'cat_name' => new external_value(PARAM_TEXT, 'cartegory name'),
                    'cat_description' => new external_value(PARAM_RAW, 'category description'),
                    'sortorder' => new external_value(PARAM_TEXT, 'sortorder'),
                    'fullname' => new external_value(PARAM_TEXT, 'course name'),
                    'shortname' => new external_value(PARAM_TEXT, 'course shortname'),
                    'idnumber' => new external_value(PARAM_RAW, 'idnumber'),
                    'summary' => new external_value(PARAM_RAW, 'summary'),
                    'startdate' => new external_value(PARAM_INT, 'start date'),
                    'created' => new external_value(PARAM_INT, 'created'),
                    'modified' => new external_value(PARAM_INT, 'modified'),
                    'cost' => new external_value(PARAM_FLOAT, 'cost', VALUE_OPTIONAL),
                    'currency' => new external_value(PARAM_TEXT, 'currency', VALUE_OPTIONAL),
                    'self_enrolment' => new external_value(PARAM_INT, 'self enrollable'),
                    'enroled' => new external_value(PARAM_INT, 'user enroled'),
                    'in_enrol_date' => new external_value(PARAM_BOOL, 'in enrol date'),
                    'guest' => new external_value(PARAM_INT, 'guest access'),
                    'summary_files' => new external_multiple_structure(
                        new external_single_structure(
                           array(
                              'url' => new external_value(PARAM_TEXT, 'item url'),
                           )
                        )
                     )
                )
            )
        );
    }

    public static function courses_abc($start_chars, $username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::courses_abc_parameters(),
                array('start_chars' => $start_chars, 'username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->courses_abc ($start_chars, $username);

        return $id;
    }

    /* teachers_abc */
    public static function teachers_abc_parameters() {
        return new external_function_parameters(
                        array(
                            'start_chars' => new external_value(PARAM_TEXT, 'Start chars'),
                        )
        );
    }

    public static function teachers_abc_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                  'firstname' => new external_value(PARAM_TEXT, 'firstname'),
                  'lastname' => new external_value(PARAM_TEXT, 'lastname'),
                  'username' => new external_value(PARAM_TEXT, 'username'),
                )
            )
        );
    }

    public static function teachers_abc($start_chars) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::teachers_abc_parameters(), array('start_chars' => $start_chars));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->teachers_abc ($start_chars);

        return $id;
    }

    /* teacher_courses */
    public static function teacher_courses_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'Teacher username'),
                        )
        );
    }

    public static function teacher_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                  'remoteid' => new external_value(PARAM_INT, 'course id'),
                  'fullname' => new external_value(PARAM_TEXT, 'course name'),
                  'cat_id' => new external_value(PARAM_INT, 'category id'),
                  'cat_name' => new external_value(PARAM_TEXT, 'category name'),
                )
            )
        );
    }

    public static function teacher_courses($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::teacher_courses_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->teacher_courses ($username);

        return $id;
    }

    /* user_custom_fields */
    public static function user_custom_fields_parameters() {
        return new external_function_parameters(
                        array(
                        )
        );
    }

    public static function user_custom_fields_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                  'id' => new external_value(PARAM_INT, 'field id'),
                  'name' => new external_value(PARAM_TEXT, 'field name'),
                  'shortname' => new external_value(PARAM_TEXT, 'field short name'),
                )
            )
        );
    }

    public static function user_custom_fields() {
        global $CFG, $DB;

        $params = self::validate_parameters(self::user_custom_fields_parameters(), array());

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->user_custom_fields ();

        return $id;
    }

    /* course_enrol_methods */
    public static function course_enrol_methods_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                        )
        );
    }

    public static function course_enrol_methods_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                  'id' => new external_value(PARAM_INT, 'enrol method id'),
                  'enrol' => new external_value(PARAM_TEXT, 'enrol method name'),
                  'enrolstartdate' => new external_value(PARAM_INT, 'enrol start date', VALUE_OPTIONAL),
                  'enrolenddate' => new external_value(PARAM_INT, 'enrol end date', VALUE_OPTIONAL),
                  )
            )
        );
    }

    public static function course_enrol_methods($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::course_enrol_methods_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->course_enrol_methods ($id);

        return $id;
    }

    /* QUIZ FUNCTIONS */

    /* quiz_get_question */
    public static function quiz_get_question_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'question id'),
                        )
        );
    }

    public static function quiz_get_question_returns() {
        return  new external_single_structure(
                array(
                  'id' => new external_value(PARAM_INT, 'question id'),
                  'questiontext' => new external_value(PARAM_RAW, 'question text'),
                  'qtype' => new external_value(PARAM_TEXT, 'question type'),
                )
        );
    }

    public static function quiz_get_question($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::quiz_get_question_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->quiz_get_question ($id);

        return $id;
    }

    /* quiz_random_question */
    public static function quiz_get_random_question_parameters() {
        return new external_function_parameters(
                        array(
                            'cat_id' => new external_value(PARAM_INT, 'category id'),
                     'used_ids' => new external_value(PARAM_TEXT, 'used question ids'),
                        )
        );
    }

    public static function quiz_get_random_question_returns() {
        return  new external_single_structure(
                array(
                  'id' => new external_value(PARAM_INT, 'question id'),
                  'questiontext' => new external_value(PARAM_RAW, 'question text'),
                  'qtype' => new external_value(PARAM_TEXT, 'question type'),
                  'answers' => new external_multiple_structure(
                      new external_single_structure(
                        array(
                              'id' => new external_value(PARAM_INT, 'answer id'),
                              'answer' => new external_value(PARAM_RAW, 'answer text'),
                              'fraction' => new external_value(PARAM_FLOAT, 'answer text'),
                        )
                     )
                  )
            )
        );
    }

    public static function quiz_get_random_question($cat_id, $used_ids) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::quiz_get_random_question_parameters(),
                array('cat_id' => $cat_id, 'used_ids' => $used_ids));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->quiz_get_random_question ($cat_id, $used_ids);

        return $id;
    }

    /* quiz_get_correct_answer */
    public static function quiz_get_correct_answer_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'question id'),
                        )
        );
    }

    public static function quiz_get_correct_answer_returns() {
        return new  external_value(PARAM_INT, 'answer id');
    }

    public static function quiz_get_correct_answer($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::quiz_get_correct_answer_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->quiz_get_correct_answer ($id);

        return $id;
    }

    /* get_question_categories */
    public static function quiz_get_question_categories_parameters() {
        return new external_function_parameters(
                        array(
                        )
        );
    }

    public static function quiz_get_question_categories_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'id' => new external_value(PARAM_INT, 'category id'),
                  'name' => new external_value(PARAM_TEXT, 'category name'),
               )
            )
            );
    }

    public static function quiz_get_question_categories() {
        global $CFG, $DB;

        $params = self::validate_parameters(self::quiz_get_question_categories_parameters(), array());

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->quiz_get_question_categories ();

        return $return;
    }

    /* quiz_get_answers */
    public static function quiz_get_answers_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'question id'),
                        )
        );
    }

    public static function quiz_get_answers_returns() {
        return new external_multiple_structure(
          new external_single_structure(
                array(
                  'id' => new external_value(PARAM_INT, 'answer id'),
                  'answer' => new external_value(PARAM_RAW, 'answer text'),
                )
            )
        );
    }

    public static function quiz_get_answers($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::quiz_get_answers_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->quiz_get_answers ($id);

        return $id;
    }

    /* get_course_students */
    public static function get_course_students_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                            'active' => new external_value(PARAM_INT, 'active'),
                        )
        );
    }

    public static function get_course_students_returns() {
         return new external_multiple_structure(
                new external_single_structure(
                    array(
                        'firstname' => new external_value(PARAM_TEXT, 'firstname'),
                        'lastname' => new external_value(PARAM_TEXT, 'lastname'),
                        'username' => new external_value(PARAM_TEXT, 'username'),
                        'email' => new external_value(PARAM_TEXT, 'email'),
                        'id' => new external_value(PARAM_INT, 'user id'),
                    )
                )
            );
    }

    public static function get_course_students($id, $active) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_course_students_parameters(),
                array('id' => $id, 'active' => $active));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_course_students ($id, "", $active);

        return $return;
    }

    /* my_teachers */
    public static function my_teachers_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'Username'),
                        )
        );
    }

    public static function my_teachers_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'group record id'),
                    'fullname' => new external_value(PARAM_TEXT, 'course name'),
                    'teachers' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'firstname' => new external_value(PARAM_TEXT, 'firstname'),
                                'lastname' => new external_value(PARAM_TEXT, 'lastname'),
                                'username' => new external_value(PARAM_TEXT, 'username'),
                            )
                        )
                    )
                )
            )
        );
    }

    public static function my_teachers($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::my_teachers_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->my_teachers ($username);

        return $return;
    }

    /* my_classmates */
    public static function my_classmates_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function my_classmates_returns() {
         return new external_multiple_structure(
                new external_single_structure(
                    array(
                        'firstname' => new external_value(PARAM_TEXT, 'firstname'),
                        'lastname' => new external_value(PARAM_TEXT, 'lastname'),
                        'username' => new external_value(PARAM_TEXT, 'username'),
                    )
                )
            );
    }

    public static function my_classmates($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::my_classmates_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->my_classmates ($username);

        return $return;
    }

    /* multiple_suspend_enrolment */
    public static function multiple_suspend_enrolment_parameters() {
        return new external_function_parameters(
            array(
                'username' => new external_value(PARAM_TEXT, 'username'),
                'courses' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'course id'),
                        )
                    )
                ),
            )
        );
    }

    public static function multiple_suspend_enrolment_returns() {
        return new  external_value(PARAM_INT, 'user enroled');
    }

    public static function multiple_suspend_enrolment($username, $courses) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::multiple_suspend_enrolment_parameters(),
                array('username' => $username, 'courses' => $courses));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->multiple_suspend_enrolment ($username, $courses);

        return $id;
    }

    /* suspend_enrolment */
    public static function suspend_enrolment_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                            'id' => new external_value(PARAM_INT, 'course id'),
                        )
        );
    }

    public static function suspend_enrolment_returns() {
        return new  external_value(PARAM_INT, 'user created');
    }

    public static function suspend_enrolment($username, $id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::suspend_enrolment_parameters(),
                array('username' => $username, 'id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->suspend_enrolment ($username, $id);

        return $id;
    }

    /* get_course_resources */
    public static function get_course_resources_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'course id'),
                 'username' => new external_value(PARAM_TEXT, 'username'),
            )
        );
    }

    public static function get_course_resources_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'section' => new external_value(PARAM_INT, 'section id'),
                  'summary' => new external_value(PARAM_RAW, 'summary'),
                  'resources' => new external_multiple_structure(
                              new external_single_structure(
                                 array (
                                 'id' => new external_value(PARAM_INT, 'resource id'),
                                 'name' => new external_value(PARAM_RAW, 'name'),
                                 'type' => new external_value(PARAM_RAW, 'type'),
                                 )
                              )
                     ),
               )
            )
        );
    }

    public static function get_course_resources($id, $username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_course_resources_parameters(),
                array('id' => $id, 'username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_course_resources ($id, $username);

        return $return;
    }

    /* get_course_mods */
    public static function get_course_mods_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                     'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function get_course_mods_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'section' => new external_value(PARAM_INT, 'section id'),
                  'name' => new external_value(PARAM_TEXT, 'section name'),
                  'summary' => new external_value(PARAM_RAW, 'summary'),
                  'mods' => new external_multiple_structure(
                              new external_single_structure(
                                 array (
                                 'id' => new external_value(PARAM_INT, 'resource id'),
                                 'name' => new external_value(PARAM_RAW, 'name'),
                                 'mod' => new external_value(PARAM_RAW, 'mod'),
                                 'type' => new external_value(PARAM_RAW, 'type'),
                                            'available' => new external_value(PARAM_INT, 'available'),
                                            'completion_info' => new external_value(PARAM_RAW, 'completion info'),
                                            'display' => new external_value(PARAM_INT, 'display'),
                                            'content' => new external_value(PARAM_RAW, 'content'),
                                 )
                              )
                     ),
               )
            )
        );
    }

    public static function get_course_mods($id, $username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_course_mods_parameters(),
                array('id' => $id, 'username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_course_mods ($id, $username);

        return $return;
    }

    /* get_course_completion */
    public static function get_course_completion_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'course id'),
                'username' => new external_value(PARAM_TEXT, 'username'),
            )
        );
    }

    public static function get_course_completion_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'section' => new external_value(PARAM_INT, 'section id'),
                  'summary' => new external_value(PARAM_RAW, 'summary'),
                  'complete' => new external_value(PARAM_INT, 'complete'),
               )
            )
        );
    }

    public static function get_course_completion($id, $username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_course_completion_parameters(),
                array('id' => $id, 'username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_course_completion ($id, $username);

        return $return;
    }

    /* get_course_quizes */
    public static function get_course_quizes_parameters() {
            return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function get_course_quizes_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'section' => new external_value(PARAM_INT, 'section id'),
                  'summary' => new external_value(PARAM_RAW, 'summary'),
                  'quizes' => new external_multiple_structure(
                              new external_single_structure(
                                 array (
                                 'id' => new external_value(PARAM_INT, 'resource id'),
                                 'name' => new external_value(PARAM_RAW, 'name'),
                                 'grade' => new external_value(PARAM_FLOAT, 'grade'),
                                 'passed' => new external_value(PARAM_BOOL, 'passed'),
                                 )
                              )
                         ),
                   )
                )
            );
    }

    public static function get_course_quizes($id, $username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_course_quizes_parameters(),
                array('id' => $id, 'username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_course_quizes ($id, $username);

        return $return;
    }

    /* my_certificates */
    public static function my_certificates_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                            'type' => new external_value(PARAM_TEXT, 'type'),
                        )
        );
    }

    public static function my_certificates_returns() {
         return new external_multiple_structure(
                new external_single_structure(
                    array(
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        'id' => new external_value(PARAM_INT, 'id'),
                    )
                )
            );
    }

    public static function my_certificates($username, $type) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::my_certificates_parameters(),
                array('username' => $username, 'type' => $type));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->my_certificates ($username, $type);

        return $return;
    }

    /* get_page */
    public static function get_page_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'page id'),
                        )
        );
    }

    public static function get_page_returns() {
         return new external_single_structure(
                    array(
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        'content' => new external_value(PARAM_RAW, 'content'),
                    )
        );
    }

    public static function get_page($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_page_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_page ($id);

        return $return;
    }

    /* get_label */
    public static function get_label_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'label id'),
                        )
        );
    }

    public static function get_label_returns() {
         return new external_single_structure(
                    array(
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        'content' => new external_value(PARAM_RAW, 'content'),
                    )
        );
    }

    public static function get_label($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_label_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_label ($id);

        return $return;
    }

    /* get_news_item */
    public static function get_news_item_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'label id'),
                        )
        );
    }

    public static function get_news_item_returns() {
         return new external_multiple_structure(
                new external_single_structure(
                    array(
                        'subject' => new external_value(PARAM_RAW, 'name'),
                        'message' => new external_value(PARAM_RAW, 'name'),
                    )
                )
            );
    }

    public static function get_news_item($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_news_item_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_news_item ($id);

        return $return;
    }

    /* get_my_news */
    public static function get_my_news_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function get_my_news_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'remoteid' => new external_value(PARAM_INT, 'course id'),
                  'fullname' => new external_value(PARAM_TEXT, 'fullname'),
                  'news' => new external_multiple_structure(
                           new external_single_structure(
                              array (
                                 'discussion' => new external_value(PARAM_INT, 'discussion id'),
                                 'subject' => new external_value(PARAM_TEXT, 'subject'),
                                 'timemodified' => new external_value(PARAM_INT, 'timemodified'),
                              )
                           )
                  ),

               )
            )
        );
    }

    public static function get_my_news($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_my_news_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_my_news ($username);

        return $return;
    }

    /* get_my_events */
    public static function get_my_events_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function get_my_events_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'fullname' => new external_value(PARAM_TEXT, 'fullname'),
                  'remoteid' => new external_value(PARAM_INT, 'course id'),
                  'events' => new external_multiple_structure(
                           new external_single_structure(
                              array (
                                 'name' => new external_value(PARAM_TEXT, 'event name'),
                                 'timestart' => new external_value(PARAM_INT, 'timestart'),
                                 'courseid' => new external_value(PARAM_INT, 'course id'),
                              )
                           )
                  ),

               )
            )
            );
    }

    public static function get_my_events($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_my_events_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_my_events ($username);

        return $return;
    }

    /* get_my_grades */
    public static function get_my_grades_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function get_my_grades_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'fullname' => new external_value(PARAM_TEXT, 'fullname'),
                  'remoteid' => new external_value(PARAM_INT, 'course id'),
                  'grades' => new external_multiple_structure(
                           new external_single_structure(
                              array (
                                 'itemname' => new external_value(PARAM_TEXT, 'item name'),
                                 'finalgrade' => new external_value(PARAM_TEXT, 'final grade'),
                              )
                           )
                  ),

               )
            )
            );
    }

    public static function get_my_grades($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_my_grades_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_my_grades ($username);

        return $return;
    }

    /* get_course_grades_by_category */
    public static function get_course_grades_by_category_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function get_course_grades_by_category_returns() {
         return new external_multiple_structure(
                new external_single_structure(
                    array(
                        'fullname' => new external_value(PARAM_TEXT, 'item name'),
                        'grademax' => new external_value(PARAM_FLOAT, 'grademax'),
                        'finalgrade' => new external_value(PARAM_FLOAT, 'final grade'),
                  'items' => new external_multiple_structure(
                        new external_single_structure(
                           array(
                              'name' => new external_value(PARAM_TEXT, 'item name'),
                              'due' => new external_value(PARAM_INT, 'due date'),
                              'grademax' => new external_value(PARAM_FLOAT, 'grademax'),
                              'finalgrade' => new external_value(PARAM_FLOAT, 'final grade'),
                              'feedback' => new external_value(PARAM_RAW, 'feedback'),
                           )
                        )
                     )
                    )
                )
            );
    }

    public static function get_course_grades_by_category($id, $username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_course_grades_by_category_parameters(),
                array('id' => $id, 'username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_course_grades_by_category ($id, $username);

        return $return;
    }

    /* get_my_grades_by_category */
    public static function get_my_grades_by_category_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function get_my_grades_by_category_returns() {
         return new external_multiple_structure(
                new external_single_structure(
                array(
                    'fullname' => new external_value(PARAM_TEXT, 'fullname'),
                    'remoteid' => new external_value(PARAM_INT, 'course id'),
                    'grades' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'fullname' => new external_value(PARAM_TEXT, 'item name'),
                                'grademax' => new external_value(PARAM_FLOAT, 'grademax'),
                                'finalgrade' => new external_value(PARAM_FLOAT, 'final grade'),
                                'items' => new external_multiple_structure(
                                    new external_single_structure(
                                        array(
                                            'name' => new external_value(PARAM_TEXT, 'item name'),
                                            'due' => new external_value(PARAM_INT, 'due date'),
                                            'grademax' => new external_value(PARAM_FLOAT, 'grademax'),
                                            'finalgrade' => new external_value(PARAM_FLOAT, 'final grade'),
                                            'feedback' => new external_value(PARAM_RAW, 'feedback'),
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
         );
    }

    public static function get_my_grades_by_category($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_my_grades_by_category_parameters(), array( 'username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_my_grades_by_category ($username);

        return $return;
    }

    /* get_cohorts */
    public static function get_cohorts_parameters() {
        return new external_function_parameters(
                        array(
                        )
        );
    }

    public static function get_cohorts_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'id' => new external_value(PARAM_INT, 'cohort id'),
                  'name' => new external_value(PARAM_TEXT, 'name'),
               )
            )
            );
    }

    public static function get_cohorts() {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_cohorts_parameters(), array());

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_cohorts ();

        return $return;
    }

    /* add_cohort_member */
    public static function add_cohort_member_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                            'cohort_id' => new external_value(PARAM_INT, 'cohort id'),
                        )
        );
    }

    public static function add_cohort_member_returns() {
        return new  external_value(PARAM_INT, 'user added');
    }

    public static function add_cohort_member($username, $cohort_id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::add_cohort_member_parameters(),
                array('username' => $username, 'cohort_id' => $cohort_id));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->add_cohort_member ($username, $cohort_id);

        return $id;
    }

    /* get_rubrics */
    public static function get_rubrics_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'id'),
                        )
        );
    }

    public static function get_rubrics_returns() {
        return
            new external_single_structure(
                array(
                    'assign_name' => new external_value(PARAM_TEXT, 'definition name'),
                    'definitions' => new external_multiple_structure(
                                    new external_single_structure(
                                        array(
                                            'definition' => new external_value(PARAM_TEXT, 'definition name'),
                                            'criteria' => new external_multiple_structure(
                                                new external_single_structure(
                                                    array(
                                                        'description' => new external_value(PARAM_TEXT, 'criterion description'),
                                                        'levels' => new external_multiple_structure(
                                                            new external_single_structure(
                                                                array(
                                                                    'definition' => new external_value(PARAM_RAW,
                                                                        'level definition'),
                                                                    'score' => new external_value(PARAM_FLOAT,
                                                                        'grademax'),
                                                                )
                                                            )
                                                         )
                                                   )
                                                )
                                            )
                                        )
                                    )
                                )
                    )
            );
    }

    public static function get_rubrics($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_rubrics_parameters(), array( 'id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_rubrics ($id);

        return $return;
    }

    /* get_grade_user_report */
    public static function get_grade_user_report_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function get_grade_user_report_returns() {
         return
             new external_single_structure(
                  array(
                     'config' => new external_single_structure(
                           array(
                                 'showlettergrade' => new external_value(PARAM_INT, 'showlettergrade'),
                              )
                           ),
                     'data' => new external_multiple_structure(
                           new external_single_structure(
                              array(
                                 'fullname' => new external_value(PARAM_TEXT, 'item name'),
                                 'grademax' => new external_value(PARAM_FLOAT, 'grademax'),
                                 'finalgrade' => new external_value(PARAM_FLOAT, 'final grade'),
                                 'letter' => new external_value(PARAM_TEXT, 'grade letter'),
                                 'items' => new external_multiple_structure(
                                       new external_single_structure(
                                          array(
                                             'name' => new external_value(PARAM_TEXT, 'item name'),
                                             'due' => new external_value(PARAM_INT, 'due date'),
                                             'grademax' => new external_value(PARAM_FLOAT, 'grademax'),
                                             'finalgrade' => new external_value(PARAM_FLOAT, 'final grade'),
                                             'feedback' => new external_value(PARAM_RAW, 'feedback'),
                                             'letter' => new external_value(PARAM_TEXT, 'grade letter'),
                                             'module' => new external_value(PARAM_TEXT, 'module'),
                                             'iteminstance' => new external_value(PARAM_INT, 'item instance'),
                                             'course_module_id' => new external_value(PARAM_INT, 'course module id'),
                                          )
                                       )
                                    )
                              )
                           )
                        )
                   )
               );
    }

    public static function get_grade_user_report($id, $username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_grade_user_report_parameters(),
                array('id' => $id, 'username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_grade_user_report ($id, $username);

        return $return;
    }


    /* get_my_grade_user_report */
    public static function get_my_grade_user_report_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function get_my_grade_user_report_returns() {
         return new external_multiple_structure(
                new external_single_structure(
               array(
                  'fullname' => new external_value(PARAM_TEXT, 'fullname'),
                  'remoteid' => new external_value(PARAM_INT, 'course id'),
                  'grades' => new external_single_structure(
                     array(
                        'config' => new external_single_structure(
                              array(
                                    'showlettergrade' => new external_value(PARAM_INT, 'showlettergrade'),
                                 )
                              ),
                        'data' => new external_multiple_structure(
                              new external_single_structure(
                                 array(
                                    'fullname' => new external_value(PARAM_TEXT, 'item name'),
                                    'grademax' => new external_value(PARAM_FLOAT, 'grademax'),
                                    'finalgrade' => new external_value(PARAM_FLOAT, 'final grade'),
                                    'letter' => new external_value(PARAM_TEXT, 'grade letter'),
                                    'items' => new external_multiple_structure(
                                          new external_single_structure(
                                             array(
                                                'name' => new external_value(PARAM_TEXT, 'item name'),
                                                'due' => new external_value(PARAM_INT, 'due date'),
                                                'grademax' => new external_value(PARAM_FLOAT, 'grademax'),
                                                'finalgrade' => new external_value(PARAM_FLOAT, 'final grade'),
                                                'feedback' => new external_value(PARAM_RAW, 'feedback'),
                                                'letter' => new external_value(PARAM_TEXT, 'grade letter'),
                                                'module' => new external_value(PARAM_TEXT, 'module'),
                                                'iteminstance' => new external_value(PARAM_INT, 'item instance'),
                                                'course_module_id' => new external_value(PARAM_INT, 'course module id'),
                                             )
                                          )
                                       )
                                 )
                              )
                           )
                     )
                  )
                )
            )
        );
    }

    public static function get_my_grade_user_report($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_my_grade_user_report_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_my_grade_user_report ($username);

        return $return;
    }


    /* teacher_get_course_grades */
    public static function teacher_get_course_grades_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                            'search' => new external_value(PARAM_TEXT, 'search text'),
                        )
        );
    }

    public static function teacher_get_course_grades_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'firstname' => new external_value(PARAM_TEXT, 'firstname'),
                    'lastname' => new external_value(PARAM_TEXT, 'lastname'),
                    'username' => new external_value(PARAM_TEXT, 'username'),
                    'group' => new external_value(PARAM_TEXT, 'group name'),
                    'id' => new external_value(PARAM_INT, 'user id'),
                    'grades' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'fullname' => new external_value(PARAM_TEXT, 'item name'),
                                'grademax' => new external_value(PARAM_FLOAT, 'grademax'),
                                'finalgrade' => new external_value(PARAM_FLOAT, 'final grade'),
                                'items' => new external_multiple_structure(
                                    new external_single_structure(
                                        array(
                                           'name' => new external_value(PARAM_TEXT, 'item name'),
                                           'due' => new external_value(PARAM_INT, 'due date'),
                                           'grademax' => new external_value(PARAM_FLOAT, 'grademax'),
                                           'finalgrade' => new external_value(PARAM_FLOAT, 'final grade'),
                                           'feedback' => new external_value(PARAM_RAW, 'feedback'),
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );
    }

    public static function teacher_get_course_grades($id, $search) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::teacher_get_course_grades_parameters(),
                array('id' => $id, 'search' => $search));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->teacher_get_course_grades ($id, $search);

        return $return;
    }

    /* get_group_members */
    public static function get_group_members_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'group id'),
                            'search' => new external_value(PARAM_TEXT, 'search'),
                        )
        );
    }

    public static function get_group_members_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'record id'),
                    'firstname' => new external_value(PARAM_TEXT, 'first name'),
                    'lastname' => new external_value(PARAM_TEXT, 'last name'),
                    'username' => new external_value(PARAM_TEXT, 'username'),
                )
            )
        );
    }

    public static function get_group_members($id, $search) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_group_members_parameters(), array('id' => $id, 'search' => $search));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_group_members ($id, $search);

        return $return;
    }

    /* get_course_groups */
    public static function get_course_groups_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                        )
        );
    }

    public static function get_course_groups_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'group record id'),
                    'name' => new external_value(PARAM_TEXT, 'group name'),
                    'description' => new external_value(PARAM_RAW, 'description'),
                )
            )
        );
    }

    public static function get_course_groups($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_course_groups_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_course_groups ($id);

        return $return;
    }

    /* teacher_get_group_grades */
    public static function teacher_get_group_grades_parameters() {
        return new external_function_parameters(
                        array(
                            'course_id' => new external_value(PARAM_INT, 'course id'),
                            'group_id' => new external_value(PARAM_INT, 'group id'),
                            'search' => new external_value(PARAM_TEXT, 'search'),
                        )
        );
    }

    public static function teacher_get_group_grades_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'firstname' => new external_value(PARAM_TEXT, 'firstname'),
                    'lastname' => new external_value(PARAM_TEXT, 'lastname'),
                    'username' => new external_value(PARAM_TEXT, 'username'),
                    'group' => new external_value(PARAM_TEXT, 'group name'),
                    'id' => new external_value(PARAM_INT, 'user id'),
                    'grades' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'fullname' => new external_value(PARAM_TEXT, 'item name'),
                                'grademax' => new external_value(PARAM_FLOAT, 'grademax'),
                                'finalgrade' => new external_value(PARAM_FLOAT, 'final grade'),
                                'items' => new external_multiple_structure(
                                    new external_single_structure(
                                        array(
                                            'name' => new external_value(PARAM_TEXT, 'item name'),
                                            'due' => new external_value(PARAM_INT, 'due date'),
                                            'grademax' => new external_value(PARAM_FLOAT, 'grademax'),
                                            'finalgrade' => new external_value(PARAM_FLOAT, 'final grade'),
                                            'feedback' => new external_value(PARAM_RAW, 'feedback'),
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );
    }

    public static function teacher_get_group_grades($course_id, $group_id, $search) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::teacher_get_group_grades_parameters(),
                array('course_id' => $course_id, 'group_id' => $group_id, 'search' => $search));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->teacher_get_group_grades ($course_id, $group_id, $search);

        return $return;
    }

    /* create_course */
    public static function create_course_parameters() {
        return new external_function_parameters(
                        array(
                            'course' => new external_single_structure(
                                            array(
                                                'fullname' => new external_value(PARAM_TEXT, 'fullname'),
                                                'shortname' => new external_value(PARAM_TEXT, 'shortname'),
                                                'summary' => new external_value(PARAM_RAW, 'summary'),
                                                'course_lang' => new external_value(PARAM_TEXT, 'lang'),
                                                'startdate' => new external_value(PARAM_INT, 'startdate'),
                                                'idnumber' => new external_value(PARAM_TEXT, 'idnumber'),
                                                'category' => new external_value(PARAM_INT, 'cat id'),
                                            )
                                        )
                        )
        );
    }

    public static function create_course_returns() {
              return new  external_value(PARAM_INT, 'course id');
    }

    public static function create_course($course) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::create_course_parameters(), array('course' => $course));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->create_course ($course);

        return $return;
    }

    /* update_course */
    public static function update_course_parameters() {
        return new external_function_parameters(
                        array(
                            'course' => new external_single_structure(
                                            array(
                                                'id' => new external_value(PARAM_INT, 'id'),
                                                'fullname' => new external_value(PARAM_TEXT, 'fullname'),
                                                'shortname' => new external_value(PARAM_TEXT, 'shortname'),
                                                'summary' => new external_value(PARAM_RAW, 'summary'),
                                                'course_lang' => new external_value(PARAM_TEXT, 'lang'),
                                                'startdate' => new external_value(PARAM_INT, 'startdate'),
                                                'idnumber' => new external_value(PARAM_TEXT, 'idnumber'),
                                                'category' => new external_value(PARAM_INT, 'cat id'),
                                            )
                                        )
                        )
        );
    }

    public static function update_course_returns() {
              return new  external_value(PARAM_INT, 'course id');
    }

    public static function update_course($course) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::update_course_parameters(), array('course' => $course));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->update_course ($course);

        return $return;
    }

    /* add_user_role */
    public static function add_user_role_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                            'id' => new external_value(PARAM_INT, 'course id'),
                            'roleid' => new external_value(PARAM_INT, 'role id'),
                        )
        );
    }

    public static function add_user_role_returns() {
        return new  external_value(PARAM_INT, 'user created');
    }

    public static function add_user_role($username, $id, $roleid) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::add_user_role_parameters(),
                array('username' => $username, 'id' => $id, 'roleid' => $roleid));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->add_user_role ($username, $id, $roleid);

        return $id;
    }

    /* get_course_parents */
    public static function get_course_parents_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                        )
        );
    }

    public static function get_course_parents_returns() {
         return new external_multiple_structure(
                new external_single_structure(
                    array(
                        'firstname' => new external_value(PARAM_TEXT, 'firstname'),
                        'lastname' => new external_value(PARAM_TEXT, 'lastname'),
                        'username' => new external_value(PARAM_TEXT, 'username'),
                    )
                )
            );
    }

    public static function get_course_parents($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_course_parents_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_course_parents ($id);

        return $return;
    }

    /* get_all_parents */
    public static function get_all_parents_parameters() {
        return new external_function_parameters(
                        array(
                        )
        );
    }

    public static function get_all_parents_returns() {
         return new external_multiple_structure(
                new external_single_structure(
                    array(
                        'firstname' => new external_value(PARAM_TEXT, 'firstname'),
                        'lastname' => new external_value(PARAM_TEXT, 'lastname'),
                        'username' => new external_value(PARAM_TEXT, 'username'),
                    )
                )
            );
    }

    public static function get_all_parents() {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_all_parents_parameters(), array());

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_all_parents ();

        return $return;
    }


    /* remove_cohort_member */
    public static function remove_cohort_member_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                            'cohort_id' => new external_value(PARAM_INT, 'cohort id'),
                        )
        );
    }

    public static function remove_cohort_member_returns() {
        return new  external_value(PARAM_INT, 'user added');
    }

    public static function remove_cohort_member($username, $cohort_id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::remove_cohort_member_parameters(),
                array('username' => $username, 'cohort_id' => $cohort_id));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->remove_cohort_member ($username, $cohort_id);

        return $id;
    }

    /* multiple_add_cohort_member */
    public static function multiple_add_cohort_member_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                     'cohorts' => new external_multiple_structure(
                           new external_single_structure(
                              array(
                                 'id' => new external_value(PARAM_INT, 'cohort id'),
                              )
                           )
                        ),
                        )
        );
    }

    public static function multiple_add_cohort_member_returns() {
        return new  external_value(PARAM_INT, 'user enroled');
    }

    public static function multiple_add_cohort_member($username, $cohorts) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::multiple_add_cohort_member_parameters(),
                array('username' => $username, 'cohorts' => $cohorts));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->multiple_add_cohort_member ($username, $cohorts);

        return $id;
    }

    /* multiple_remove_cohort_member */
    public static function multiple_remove_cohort_member_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                             'cohorts' => new external_multiple_structure(
                                   new external_single_structure(
                                      array(
                                         'id' => new external_value(PARAM_INT, 'cohort id'),
                                      )
                                   )
                                ),
                        )
        );
    }

    public static function multiple_remove_cohort_member_returns() {
        return new  external_value(PARAM_INT, 'user enroled');
    }

    public static function multiple_remove_cohort_member($username, $cohorts) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::multiple_remove_cohort_member_parameters(),
                array('username' => $username, 'cohorts' => $cohorts));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->multiple_remove_cohort_member ($username, $cohorts);

        return $id;
    }

    /* get_courses_and_groups */
    public static function get_courses_and_groups_parameters() {
        return new external_function_parameters(
                        array(
                        )
        );
    }

    public static function get_courses_and_groups_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'remoteid' => new external_value(PARAM_INT, 'course id'),
                    'fullname' => new external_value(PARAM_TEXT, 'course name'),
                    'groups' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'group record id'),
                                'name' => new external_value(PARAM_TEXT, 'group name'),
                                'description' => new external_value(PARAM_RAW, 'description'),
                            )
                        )
                    )
                )
            )
        );
    }

    public static function get_courses_and_groups() {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_courses_and_groups_parameters(), array());

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->get_courses_and_groups ();

        return $id;
    }

    /* multiple_enrol_to_course_and_group */
    public static function multiple_enrol_to_course_and_group_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                             'courses' => new external_multiple_structure(
                                   new external_single_structure(
                                      array(
                                         'id' => new external_value(PARAM_INT, 'course id'),
                                         'group_id' => new external_value(PARAM_INT, 'group id'),
                                      )
                                   )
                                ),
                            'roleid' => new external_value(PARAM_INT, 'role id'),
                        )
        );
    }

    public static function multiple_enrol_to_course_and_group_returns() {
        return new  external_value(PARAM_INT, 'user enroled');
    }

    public static function multiple_enrol_to_course_and_group($username, $courses, $roleid) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::multiple_enrol_to_course_and_group_parameters(),
                array('username' => $username, 'courses' => $courses, 'roleid' => $roleid));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->multiple_enrol_to_course_and_group ($username, $courses, $roleid);

        return $id;
    }

    /* multiple_remove_from_group */
    public static function multiple_remove_from_group_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                             'courses' => new external_multiple_structure(
                                   new external_single_structure(
                                      array(
                                         'id' => new external_value(PARAM_INT, 'course id'),
                                         'group_id' => new external_value(PARAM_INT, 'group id'),
                                      )
                                   )
                                ),
                        )
        );
    }

    public static function multiple_remove_from_group_returns() {
        return new  external_value(PARAM_INT, 'user enroled');
    }

    public static function multiple_remove_from_group($username, $courses) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::multiple_remove_from_group_parameters(),
                array('username' => $username, 'courses' => $courses));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->multiple_remove_from_group ($username, $courses);

        return $id;
    }

    /* my_all_courses */
    public static function my_all_courses_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'Username'),
                        )
        );
    }

    public static function my_all_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'group record id'),
                    'fullname' => new external_value(PARAM_TEXT, 'course name'),
                    'category' => new external_value(PARAM_INT, 'course category id'),
                )
            )
        );
    }

    public static function my_all_courses($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::my_all_courses_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->my_all_courses ($username);

        return $return;
    }

    /* multiple_unenrol_user */
    public static function multiple_unenrol_user_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                     'courses' => new external_multiple_structure(
                           new external_single_structure(
                              array(
                                 'id' => new external_value(PARAM_INT, 'course id'),
                              )
                           )
                        ),
                        )
        );
    }

    public static function multiple_unenrol_user_returns() {
        return new  external_value(PARAM_INT, 'user enroled');
    }

    public static function multiple_unenrol_user($username, $courses) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::multiple_unenrol_user_parameters(),
                array('username' => $username, 'courses' => $courses));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->multiple_unenrol_user ($username, $courses);

        return $id;
    }

    /* suspend_enrolment */
    public static function unenrol_user_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                            'id' => new external_value(PARAM_INT, 'course id'),
                        )
        );
    }

    public static function unenrol_user_returns() {
        return new  external_value(PARAM_INT, 'user created');
    }

    public static function unenrol_user($username, $id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::unenrol_user_parameters(), array('username' => $username, 'id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->unenrol_user ($username, $id);

        return $id;
    }


    /* get_children_grades */
    public static function get_children_grades_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function get_children_grades_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'username' => new external_value(PARAM_TEXT, 'username'),
                  'name' => new external_value(PARAM_TEXT, 'name'),
                  'grades' => new external_multiple_structure(
                           new external_single_structure(
                              array(
                                 'fullname' => new external_value(PARAM_TEXT, 'fullname'),
                                 'remoteid' => new external_value(PARAM_INT, 'course id'),
                                 'grades' => new external_multiple_structure(
                                          new external_single_structure(
                                             array (
                                                'itemname' => new external_value(PARAM_TEXT, 'item name'),
                                                'finalgrade' => new external_value(PARAM_TEXT, 'final grade'),
                                             )
                                          )
                                 ),

                              )
                           )
                        )
               )
            )
        );
    }

    public static function get_children_grades($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_children_grades_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_children_grades ($username);

        return $return;
    }


    /* get_children_grade_user_report */
    public static function get_children_grade_user_report_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function get_children_grade_user_report_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'username' => new external_value(PARAM_TEXT, 'username'),
                  'name' => new external_value(PARAM_TEXT, 'name'),
                  'grades' => new external_multiple_structure(
                           new external_single_structure(
                              array(
                                 'fullname' => new external_value(PARAM_TEXT, 'fullname'),
                                 'remoteid' => new external_value(PARAM_INT, 'course id'),
                                 'grades' => new external_single_structure(
                                    array(
                                       'config' => new external_single_structure(
                                             array(
                                                   'showlettergrade' => new external_value(PARAM_INT, 'showlettergrade'),
                                                )
                                             ),
                                       'data' => new external_multiple_structure(
                                             new external_single_structure(
                                                array(
                                                   'fullname' => new external_value(PARAM_TEXT, 'item name'),
                                                   'grademax' => new external_value(PARAM_FLOAT, 'grademax'),
                                                   'finalgrade' => new external_value(PARAM_FLOAT, 'final grade'),
                                                   'letter' => new external_value(PARAM_TEXT, 'grade letter'),
                                                   'items' => new external_multiple_structure(
                                                         new external_single_structure(
                                                            array(
                                                               'name' => new external_value(PARAM_TEXT, 'item name'),
                                                               'due' => new external_value(PARAM_INT, 'due date'),
                                                               'grademax' => new external_value(PARAM_FLOAT, 'grademax'),
                                                               'finalgrade' => new external_value(PARAM_FLOAT, 'final grade'),
                                                               'feedback' => new external_value(PARAM_RAW, 'feedback'),
                                                               'letter' => new external_value(PARAM_TEXT, 'grade letter'),
                                                            )
                                                         )
                                                      )
                                                )
                                             )
                                          )
                                    )
                                 )
                              )
                           )
                        )

               )
            )
        );
    }

    public static function get_children_grade_user_report($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_children_grade_user_report_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_children_grade_user_report ($username);

        return $return;
    }

    /* enrol_user_with_start_date */
    public static function enrol_user_with_start_date_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                            'id' => new external_value(PARAM_INT, 'course id'),
                            'roleid' => new external_value(PARAM_INT, 'role id'),
                            'start_date' => new external_value(PARAM_INT, 'start_date'),
                        )
        );
    }

    public static function enrol_user_with_start_date_returns() {
        return new  external_value(PARAM_INT, 'user created');
    }

    public static function enrol_user_with_start_date($username, $id, $roleid, $start_date) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::enrol_user_with_start_date_parameters(),
                array('username' => $username, 'id' => $id, 'roleid' => $roleid, 'start_date' => $start_date));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->enrol_user ($username, $id, $roleid, $start_date);

        return $id;
    }

    /* remove_user_role */
    public static function remove_user_role_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                            'id' => new external_value(PARAM_INT, 'course id'),
                            'roleid' => new external_value(PARAM_INT, 'role id'),
                        )
        );
    }

    public static function remove_user_role_returns() {
        return new  external_value(PARAM_INT, 'user created');
    }

    public static function remove_user_role($username, $id, $roleid) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::remove_user_role_parameters(),
                array('username' => $username, 'id' => $id, 'roleid' => $roleid));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->remove_user_role ($username, $id, $roleid);

        return $id;
    }

    /* get_themes */
    public static function get_themes_parameters() {
        return new external_function_parameters(
                        array(
                        )
        );
    }

    public static function get_themes_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'name' => new external_value(PARAM_TEXT, 'name'),
               )
            )
        );
    }

    public static function get_themes() {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_themes_parameters(), array());

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_themes ();

        return $return;
    }

    /* list_courses_scorm */
    public static function list_courses_scorm_parameters() {
        return new external_function_parameters(
                        array(
                            'enrollable_only' => new external_value(PARAM_INT, 'Return only enrollable courses'),
                            'sortby' => new external_value(PARAM_TEXT, 'Order field'),
                            'guest' => new external_value(PARAM_INT, 'Return only courses for guests'),
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function list_courses_scorm_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'remoteid' => new external_value(PARAM_INT, 'course id'),
                    'cat_id' => new external_value(PARAM_INT, 'category id'),
                    'cat_name' => new external_value(PARAM_TEXT, 'cartegory name'),
                    'cat_description' => new external_value(PARAM_RAW, 'category description'),
                    'sortorder' => new external_value(PARAM_TEXT, 'sortorder'),
                    'fullname' => new external_value(PARAM_TEXT, 'course name'),
                    'shortname' => new external_value(PARAM_TEXT, 'course shortname'),
                    'idnumber' => new external_value(PARAM_RAW, 'idnumber'),
                    'summary' => new external_value(PARAM_RAW, 'summary'),
                    'startdate' => new external_value(PARAM_INT, 'start date'),
                    'created' => new external_value(PARAM_INT, 'created'),
                    'modified' => new external_value(PARAM_INT, 'modified'),
                    'cost' => new external_value(PARAM_FLOAT, 'cost', VALUE_OPTIONAL),
                    'currency' => new external_value(PARAM_TEXT, 'currency', VALUE_OPTIONAL),
                    'self_enrolment' => new external_value(PARAM_INT, 'self enrollable'),
                    'enroled' => new external_value(PARAM_INT, 'user enroled'),
                    'in_enrol_date' => new external_value(PARAM_BOOL, 'in enrol date'),
                    'guest' => new external_value(PARAM_INT, 'guest access'),
                    'scorm_data' => new external_single_structure(
                        array(
                           'start_time' => new external_value(PARAM_INT, 'start time'),
                           'total_time' => new external_value(PARAM_TEXT, 'total time'),
                           'lesson_status' => new external_value(PARAM_TEXT, 'lesson status'),
                           'score' => new external_value(PARAM_FLOAT, 'raw score'),
                        )
                     )
                )
            )
        );
    }

    public static function list_courses_scorm($enrollable_only, $sortby, $guest, $username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::list_courses_scorm_parameters(),
                array('enrollable_only' => $enrollable_only, 'sortby' => $sortby, 'guest' => $guest, 'username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->list_courses_scorm ($enrollable_only, $sortby, $guest, $username);

        return $id;
    }

    /* create_moodle_only_user */
    public static function create_moodle_only_user_parameters() {
        return new external_function_parameters(
                        array(
                            'user_data' => new external_single_structure(
                                            array(
                                                'username' => new external_value(PARAM_TEXT, 'username'),
                                                'firstname' => new external_value(PARAM_TEXT, 'fistname'),
                                                'lastname' => new external_value(PARAM_TEXT, 'lastname'),
                                                'email' => new external_value(PARAM_RAW, 'email'),
                                                'password' => new external_value(PARAM_TEXT, 'password'),
                                                'city' => new external_value(PARAM_TEXT, 'city'),
                                                'country' => new external_value(PARAM_TEXT, 'country'),
                                            )
                                        )
                        )
        );
    }

    public static function create_moodle_only_user_returns() {
                return new  external_value(PARAM_INT, 'user id');
    }

    public static function create_moodle_only_user($user_data) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::create_moodle_only_user_parameters(), array('user_data' => $user_data));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->create_moodle_only_user ($user_data);

        return $return;
    }

    /* enrol_user_with_start_and_end_date */
    public static function enrol_user_with_start_and_end_date_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                            'id' => new external_value(PARAM_INT, 'course id'),
                            'roleid' => new external_value(PARAM_INT, 'role id'),
                            'start_date' => new external_value(PARAM_INT, 'start_date'),
                            'end_date' => new external_value(PARAM_INT, 'end_date'),
                        )
        );
    }

    public static function enrol_user_with_start_and_end_date_returns() {
        return new  external_value(PARAM_INT, 'user created');
    }

    public static function enrol_user_with_start_and_end_date($username, $id, $roleid, $start_date, $end_date) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::enrol_user_with_start_and_end_date_parameters(),
                array('username' => $username, 'id' => $id, 'roleid' => $roleid,
                    'start_date' => $start_date, 'end_date' => $end_date));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->enrol_user ($username, $id, $roleid, $start_date, $end_date);

        return $id;
    }

    /* update_course_enrolments_dates */
    public static function update_course_enrolments_dates_parameters() {
        return new external_function_parameters(
                        array(
                            'course_id' => new external_value(PARAM_INT, 'course id'),
                            'start_date' => new external_value(PARAM_INT, 'start_date'),
                            'end_date' => new external_value(PARAM_INT, 'end_date'),
                        )
        );
    }

    public static function update_course_enrolments_dates_returns() {
        return new  external_value(PARAM_INT, 'user created');
    }

    public static function update_course_enrolments_dates($course_id, $start_date, $end_date) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::update_course_enrolments_dates_parameters(),
                array('course_id' => $course_id, 'start_date' => $start_date, 'end_date' => $end_date));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->update_course_enrolments_dates ($course_id, $start_date, $end_date);

        return $id;
    }


    /* get_system_roles */
    public static function get_system_roles_parameters() {
        return new external_function_parameters(
                        array(
                        )
        );
    }

    public static function get_system_roles_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'id' => new external_value(PARAM_INT, 'role id'),
                  'name' => new external_value(PARAM_TEXT, 'role name'),
               )
            )
        );
    }


    public static function get_system_roles() {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_system_roles_parameters(), array());

        $auth = new  auth_plugin_joomdle ();
        $roles = $auth->get_system_roles ();

        return $roles;
    }

    /* add_system_role */
    public static function add_system_role_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                            'roleid' => new external_value(PARAM_INT, 'role id'),
                        )
        );
    }

    public static function add_system_role_returns() {
        return new  external_value(PARAM_INT, 'role assigned');
    }

    public static function add_system_role($username, $roleid) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::add_system_role_parameters(),
                array('username' => $username, 'roleid' => $roleid));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->add_system_role ($username, $roleid);

        return $id;
    }

    /* my_badges */
    public static function my_badges_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'Username'),
                            'max' => new external_value(PARAM_INT, 'max to return'),
                        )
        );
    }

    public static function my_badges_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'name' => new external_value(PARAM_TEXT, 'badge name'),
                    'hash' => new external_value(PARAM_TEXT, 'unique hash'),
                    'image_url' => new external_value(PARAM_TEXT, 'image url'),
                )
            )
        );
    }

    public static function my_badges($username, $max) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::my_badges_parameters(), array('username' => $username, 'max' => $max));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->my_badges ($username, $max);

        return $return;
    }

    /* get_course_grades */
    public static function get_course_grades_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                            'search' => new external_value(PARAM_TEXT, 'search text'),
                        )
        );
    }

    public static function get_course_grades_returns() {
         return new external_multiple_structure(
                new external_single_structure(
                    array(
                        'email' => new external_value(PARAM_TEXT, 'email'),
                        'firstname' => new external_value(PARAM_TEXT, 'fistname'),
                        'lastname' => new external_value(PARAM_TEXT, 'lastname'),
                        'grades' => new external_multiple_structure(
                                        new external_single_structure(
                                            array(
                                                'rawgrade' => new external_value(PARAM_FLOAT, 'raw grade'),
                                                'grademax' => new external_value(PARAM_FLOAT, 'grademax'),
                                            )
                                        )
                                    )
                    )
                )
            );
    }

    public static function get_course_grades($id, $search) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_course_grades_parameters(), array('id' => $id, 'search' => $search));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_course_grades ($id, $search);

        return $return;
    }

    /* get_course_grades_items */
    public static function get_course_grades_items_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                        )
        );
    }

    public static function get_course_grades_items_returns() {
         return new external_multiple_structure(
                new external_single_structure(
                    array(
                        'itemname' => new external_value(PARAM_TEXT, 'itemname'),
                    )
                )
            );
    }

    public static function get_course_grades_items($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_course_grades_items_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_course_grades_items ($id);

        return $return;
    }

    /* get_course_questionnaire_results */
    public static function get_course_questionnaire_results_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                        )
        );
    }

    public static function get_course_questionnaire_results_returns() {
         return new external_multiple_structure(
            new external_single_structure(
                array(
                    'name' => new external_value(PARAM_TEXT, 'name'),
                    'content' => new external_value(PARAM_RAW, 'content'),
                    'type' => new external_value(PARAM_INT, 'type'),
                    'options' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'n' => new external_value(PARAM_INT, 'number of responses'),
                              'content' => new external_value(PARAM_RAW, 'content'),
                                    )
                                )
                            )
                )
            )
        );
    }

    public static function get_course_questionnaire_results($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_course_questionnaire_results_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->get_course_questionnaire_results ($id);

        return $id;
    }

    /* get_all_courses */
    public static function get_all_courses_parameters() {
        return new external_function_parameters(
                        array(
                            'sortby' => new external_value(PARAM_TEXT, 'Order field'),
                        )
        );
    }

    public static function get_all_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'remoteid' => new external_value(PARAM_INT, 'course id'),
                    'cat_id' => new external_value(PARAM_INT, 'category id'),
                    'cat_name' => new external_value(PARAM_TEXT, 'cartegory name'),
                    'cat_description' => new external_value(PARAM_RAW, 'category description'),
                    'sortorder' => new external_value(PARAM_TEXT, 'sortorder'),
                    'fullname' => new external_value(PARAM_TEXT, 'course name'),
                    'shortname' => new external_value(PARAM_TEXT, 'course shortname'),
                    'idnumber' => new external_value(PARAM_RAW, 'idnumber'),
                    'summary' => new external_value(PARAM_RAW, 'summary'),
                    'startdate' => new external_value(PARAM_INT, 'start date'),
                    'created' => new external_value(PARAM_INT, 'created'),
                    'modified' => new external_value(PARAM_INT, 'modified'),
                    'cost' => new external_value(PARAM_FLOAT, 'cost', VALUE_OPTIONAL),
                    'currency' => new external_value(PARAM_TEXT, 'currency', VALUE_OPTIONAL),
                    'self_enrolment' => new external_value(PARAM_INT, 'self enrollable'),
                    'in_enrol_date' => new external_value(PARAM_BOOL, 'in enrol date'),
                    'guest' => new external_value(PARAM_INT, 'guest access'),
                    'summary_files' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'url' => new external_value(PARAM_TEXT, 'item url'),
                            )
                        )
                    )
                )
            )
        );
    }

    public static function get_all_courses($sortby) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_all_courses_parameters(), array('sortby' => $sortby));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->get_all_courses ($sortby);

        return $id;
    }

    /* get_events */
    public static function get_events_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'Username'),
                            'start_date' => new external_value(PARAM_INT, 'time start'),
                            'end_date' => new external_value(PARAM_INT, 'time end'),
                            'type' => new external_value(PARAM_TEXT, 'event type'),
                            'course_id' => new external_value(PARAM_INT, 'course_id'),
                        )
        );
    }

    public static function get_events_returns() {
         return new external_multiple_structure(
                new external_single_structure(
                    array(
                  'id' => new external_value(PARAM_INT, 'id'),
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        'description' => new external_value(PARAM_RAW, 'description'),
                  'timestart' => new external_value(PARAM_INT, 'timestart'),
                  'timeduration' => new external_value(PARAM_INT, 'timeduration'),
                    )
                )
            );
    }

    public static function get_events($username, $start_date, $end_date, $type, $course_id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_events_parameters(),
                array('username' => $username, 'start_date' => $start_date, 'end_date'=>$end_date,
                    'type' => $type, 'course_id' => $course_id));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_events ($username, $start_date, $end_date, $type, $course_id);

        return $return;
    }

    /* get_event */
    public static function get_event_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'id'),
                        )
        );
    }

    public static function get_event_returns() {
         return new external_single_structure(
                    array(
                  'id' => new external_value(PARAM_INT, 'id'),
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        'description' => new external_value(PARAM_RAW, 'description'),
                  'timestart' => new external_value(PARAM_INT, 'timestart'),
                  'timeduration' => new external_value(PARAM_INT, 'timeduration'),
                        'type' => new external_value(PARAM_TEXT, 'event type'),
                    )
            );
    }

    public static function get_event($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_event_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_event ($id);

        return $return;
    }

    /* get_certificates_credits */
    public static function get_certificates_credits_parameters() {
              return new external_function_parameters(
                        array(
                            'courses' => new external_multiple_structure(
                                        new external_single_structure(
                                            array(
                                                'id' => new external_value(PARAM_INT, 'course id'),
                                            )
                                        )
                        )
                )
        );
    }

    public static function get_certificates_credits_returns() {
         return new external_multiple_structure(
                new external_single_structure(
                    array(
                  'id' => new external_value(PARAM_INT, 'id'),
                        'course_id' => new external_value(PARAM_TEXT, 'course id'),
                        'credits' => new external_value(PARAM_TEXT, 'credits'),
                    )
                )
            );
    }

    public static function get_certificates_credits($courses) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_certificates_credits_parameters(), array('courses' => $courses));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_certificates_credits ($courses);

        return $return;
    }

    /* set_section_visible */
    public static function set_section_visible_parameters() {
        return new external_function_parameters(
                        array(
                            'course_id' => new external_value(PARAM_INT, 'course_id'),
                            'section' => new external_value(PARAM_INT, 'section'),
                            'active' => new external_value(PARAM_INT, 'active'),
                        )
        );
    }

    public static function set_section_visible_returns() {
        return new  external_value(PARAM_INT, 'course id');
    }

    public static function set_section_visible($course_id, $section, $active) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::set_section_visible_parameters(),
                array('course_id' => $course_id, 'section' => $section, 'active' => $active));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->set_section_visible ($course_id, $section, $active);

        return $return;
    }

    /* create_events */
    public static function create_events_parameters() {
              return new external_function_parameters(
                        array(
                            'events' => new external_multiple_structure(
                                        new external_single_structure(
                                            array(
                                                'name' => new external_value(PARAM_TEXT, 'name'),
                                                'description' => new external_value(PARAM_TEXT, 'description'),
                                                'courseid' => new external_value(PARAM_INT, 'course id'),
                                                'timestart' => new external_value(PARAM_INT, 'time start'),
                                                'timeend' => new external_value(PARAM_INT, 'time end'),
                                                'eventtype' => new external_value(PARAM_TEXT, 'event type'),
                                                'username' => new external_value(PARAM_TEXT, 'username'),
                                            )
                                        )
                        )
                )
        );
    }

    public static function create_events_returns() {
        return new  external_value(PARAM_INT, 'event number');
    }

    public static function create_events($events) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::create_events_parameters(), array('events' => $events));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->create_events ($events);

        return $return;
    }

    /* get_courses_not_editing_teachers */
    public static function get_courses_not_editing_teachers_parameters() {
                return new external_function_parameters(
                        array(
                            'courses' => new external_multiple_structure(
                                        new external_single_structure(
                                            array(
                                                'id' => new external_value(PARAM_INT, 'course id'),
                                            )
                                        )
                        )
                )
        );
    }

    public static function get_courses_not_editing_teachers_returns() {
         return new external_multiple_structure(
                new external_single_structure(
                    array(
                        'course_id' => new external_value(PARAM_TEXT, 'course id'),
                        'firstname' => new external_value(PARAM_TEXT, 'firstname'),
                        'lastname' => new external_value(PARAM_TEXT, 'lastname'),
                        'username' => new external_value(PARAM_TEXT, 'username'),
                    )
                )
            );
    }

    public static function get_courses_not_editing_teachers($courses) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_courses_not_editing_teachers_parameters(), array('courses' => $courses));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_courses_not_editing_teachers($courses);

        return $return;
    }

    /* get_course_progress */
    public static function get_course_progress_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'id'),
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function get_course_progress_returns() {
         return new external_multiple_structure(
                new external_single_structure(
                    array(
                  'id' => new external_value(PARAM_INT, 'id'),
                        'name' => new external_value(PARAM_TEXT, 'name'),
                        'type' => new external_value(PARAM_TEXT, 'type'),
                        'link' => new external_value(PARAM_RAW, 'link'),
                        'attempted' => new external_value(PARAM_TEXT, 'attempted'),
                        'available' => new external_value(PARAM_INT, 'available'),
                    )
                )
            );
    }

    public static function get_course_progress($id, $username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_course_progress_parameters(), array('id' => $id, 'username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_course_progress ($id, $username);

        return $return;
    }

    /* my_courses_progress */
    public static function my_courses_progress_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function my_courses_progress_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'group record id'),
                    'fullname' => new external_value(PARAM_TEXT, 'course name'),
                    'summary' => new external_value(PARAM_RAW, 'summary'),
                    'category' => new external_value(PARAM_INT, 'course category id'),
                    'cat_name' => new external_value(PARAM_TEXT, 'course category name'),
                    'can_unenrol' => new external_value(PARAM_INT, 'user can self unenrol'),
                    'summary_files' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'url' => new external_value(PARAM_TEXT, 'item url'),
                            )
                        )
                    ),
                    'progress' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'id'),
                                'name' => new external_value(PARAM_TEXT, 'name'),
                                'type' => new external_value(PARAM_TEXT, 'type'),
                                'link' => new external_value(PARAM_RAW, 'link'),
                                'attempted' => new external_value(PARAM_TEXT, 'attempted'),
                                'available' => new external_value(PARAM_INT, 'available'),
                            )
                        )
                    )
                )
            )
        );
    }

    public static function my_courses_progress($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::my_courses_progress_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->my_courses_progress ($username);

        return $return;
    }

    /* set_course_visible */
    public static function set_course_visible_parameters() {
        return new external_function_parameters(
                        array(
                            'course_id' => new external_value(PARAM_INT, 'course_id'),
                            'active' => new external_value(PARAM_INT, 'active'),
                        )
        );
    }

    public static function set_course_visible_returns() {
        return new  external_value(PARAM_INT, 'course id');
    }

    public static function set_course_visible($course_id, $active) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::set_course_visible_parameters(),
                array('course_id' => $course_id, 'active' => $active));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->set_course_visible ($course_id, $active);

        return $return;
    }

    /* my_completed_courses */
    public static function my_completed_courses_parameters() {
        return new external_function_parameters(
                        array(
                     'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function my_completed_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                   'remoteid' => new external_value(PARAM_INT, 'course id'),
                   'timecompleted' => new external_value(PARAM_INT, 'time completed'),
                   'fullname' => new external_value(PARAM_TEXT, 'course name'),
                )
            )
        );
    }

    public static function my_completed_courses ($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::my_completed_courses_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $courses = $auth->my_completed_courses ($username);

        return $courses;
    }

    /* users_completed_courses */
    public static function users_completed_courses_parameters() {
        return new external_function_parameters(
                    array(
                        'users' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'username' => new external_value(PARAM_TEXT, 'username'),
                                )
                            )
                        )
                    )
        );
    }

    public static function users_completed_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'username' => new external_value(PARAM_TEXT, 'username'),
                    'courses' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'remoteid' => new external_value(PARAM_INT, 'course id'),
                                'timecompleted' => new external_value(PARAM_INT, 'time completed'),
                                'fullname' => new external_value(PARAM_TEXT, 'course name'),
                            )
                        )
                    )
                )
            )
        );
    }

    public static function users_completed_courses ($users) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::users_completed_courses_parameters(), array('users' => $users));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->users_completed_courses ($users);

        return $id;
    }

    /* remove_parent_role */
    public static function remove_parent_role_parameters() {
        return new external_function_parameters(
                        array(
                            'child' => new external_value(PARAM_TEXT, 'child username'),
                            'parent' => new external_value(PARAM_TEXT, ' parent username'),
                        )
        );
    }

    public static function remove_parent_role_returns() {
        return new  external_value(PARAM_TEXT, 'role added');
    }

    public static function remove_parent_role($child, $parent) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::remove_parent_role_parameters(), array('child' => $child, 'parent' => $parent));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->remove_parent_role ($child, $parent);

        return $id;
    }

    /* get_completed_course_users */
    public static function get_completed_course_users_parameters() {
        return new external_function_parameters(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                        )
        );
    }

    public static function get_completed_course_users_returns() {
         return new external_multiple_structure(
                new external_single_structure(
                    array(
                        'firstname' => new external_value(PARAM_TEXT, 'firstname'),
                        'lastname' => new external_value(PARAM_TEXT, 'lastname'),
                        'username' => new external_value(PARAM_TEXT, 'username'),
                        'email' => new external_value(PARAM_TEXT, 'email'),
                        'timecompleted' => new external_value(PARAM_INT, 'time completed'),
                    )
                )
            );
    }

    public static function get_completed_course_users($id) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_completed_course_users_parameters(), array('id' => $id));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_completed_course_users ($id);

        return $return;
    }

    /* change_username */
    public static function change_username_parameters() {
        return new external_function_parameters(
                        array(
                            'old_username' => new external_value(PARAM_TEXT, 'old username'),
                            'new_username' => new external_value(PARAM_TEXT, 'new username')
                        )
        );
    }

    public static function change_username_returns() {
        return new  external_value(PARAM_BOOL, 'username changed');
    }

    public static function change_username ($old_username, $new_username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::change_username_parameters(), array('old_username' => $old_username,
               'new_username' => $new_username));

        $auth = new  auth_plugin_joomdle ();
        $id = $auth->change_username ($old_username, $new_username);

        return $id;
    }

    /* my_courses_and_groups */
    public static function my_courses_and_groups_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'Username')
                        )
        );
    }

    public static function my_courses_and_groups_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'group record id'),
                    'fullname' => new external_value(PARAM_TEXT, 'course name'),
                    'summary' => new external_value(PARAM_RAW, 'summary'),
                    'category' => new external_value(PARAM_INT, 'course category id'),
                    'cat_name' => new external_value(PARAM_TEXT, 'course category name'),
                    'can_unenrol' => new external_value(PARAM_INT, 'user can self unenrol'),
                    'summary_files' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'url' => new external_value(PARAM_TEXT, 'item url'),
                            )
                        )
                    ),
                    'groups' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'group id'),
                                'name' => new external_value(PARAM_TEXT, 'group name'),
                            )
                        )
                    )
                )
            )
        );
    }

    public static function my_courses_and_groups($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::my_courses_and_groups_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->my_courses_and_groups ($username);

        return $return;
    }

    /* create_groups */
    public static function create_groups_parameters() {
        return new external_function_parameters(
            array(
                'groups' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'courseid' => new external_value(PARAM_INT, 'id of course'),
                            'name' => new external_value(PARAM_TEXT, 'multilang compatible name, course unique'),
                            'description' => new external_value(PARAM_RAW, 'group description text'),
                            'descriptionformat' => new external_format_value('description', VALUE_DEFAULT),
                            'enrolmentkey' => new external_value(PARAM_RAW, 'group enrol secret phrase', VALUE_OPTIONAL),
                            'idnumber' => new external_value(PARAM_RAW, 'id number', VALUE_OPTIONAL)
                        )
                    ), 'List of group object. A group has a courseid, a name, a description and an enrolment key.'
                )
            )
        );
    }

    public static function create_groups_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'group record id'),
                    'courseid' => new external_value(PARAM_INT, 'id of course'),
                    'name' => new external_value(PARAM_TEXT, 'multilang compatible name, course unique'),
                    'description' => new external_value(PARAM_RAW, 'group description text'),
                    'descriptionformat' => new external_format_value('description'),
                    'enrolmentkey' => new external_value(PARAM_RAW, 'group enrol secret phrase'),
                    'idnumber' => new external_value(PARAM_RAW, 'id number')
                )
            ), 'List of group object. A group has an id, a courseid, a name, a description and an enrolment key.'
        );
    }

    public static function create_groups($groups) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::create_groups_parameters(), array('groups' => $groups));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->create_groups ($groups);

        return $return;
    }

    /* get_category_categories_and_courses */
    public static function get_category_categories_and_courses_parameters() {
        return new external_function_parameters(
                        array(
                            'category' => new external_value(PARAM_INT, 'category id'),
                        )
        );
    }

    public static function get_category_categories_and_courses_returns() {
        return new external_single_structure(
            array(
                'categories' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'category id'),
                            'name' => new external_value(PARAM_TEXT, 'category name'),
                            'description' => new external_value(PARAM_RAW, 'description'),
                            'courses' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'remoteid' => new external_value(PARAM_INT, 'course id'),
                                    'cat_id' => new external_value(PARAM_INT, 'category id'),
                                    'cat_name' => new external_value(PARAM_TEXT, 'category name'),
                                    'cat_description' => new external_value(PARAM_RAW, 'category description'),
                                    'fullname' => new external_value(PARAM_TEXT, 'course name'),
                                    'summary' => new external_value(PARAM_RAW, 'course summary'),
                                    'idnumber' => new external_value(PARAM_RAW, 'idnumber'),
                                    'startdate' => new external_value(PARAM_INT, 'start date'),
                                    'cost' => new external_value(PARAM_FLOAT, 'cost', VALUE_OPTIONAL),
                                    'currency' => new external_value(PARAM_TEXT, 'currency', VALUE_OPTIONAL),
                                    'self_enrolment' => new external_value(PARAM_INT, 'self enrollable'),
                                    'enroled' => new external_value(PARAM_INT, 'user enroled'),
                                    'in_enrol_date' => new external_value(PARAM_BOOL, 'in enrol date'),
                                    'guest' => new external_value(PARAM_INT, 'guest access'),
                                    'summary_files' => new external_multiple_structure(
                                        new external_single_structure(
                                            array(
                                                'url' => new external_value(PARAM_TEXT, 'item url'),
                                            )
                                        )
                                    )
                                )
                            )
                            )
                        )
                    )
                ),
                'courses' => new external_multiple_structure(
                      new external_single_structure(
                         array(
                            'remoteid' => new external_value(PARAM_INT, 'course id'),
                            'cat_id' => new external_value(PARAM_INT, 'category id'),
                            'cat_name' => new external_value(PARAM_TEXT, 'category name'),
                            'cat_description' => new external_value(PARAM_RAW, 'category description'),
                            'fullname' => new external_value(PARAM_TEXT, 'course name'),
                            'summary' => new external_value(PARAM_RAW, 'course summary'),
                            'idnumber' => new external_value(PARAM_RAW, 'idnumber'),
                            'startdate' => new external_value(PARAM_INT, 'start date'),
                            'cost' => new external_value(PARAM_FLOAT, 'cost', VALUE_OPTIONAL),
                            'currency' => new external_value(PARAM_TEXT, 'currency', VALUE_OPTIONAL),
                            'self_enrolment' => new external_value(PARAM_INT, 'self enrollable'),
                            'enroled' => new external_value(PARAM_INT, 'user enroled'),
                            'in_enrol_date' => new external_value(PARAM_BOOL, 'in enrol date'),
                            'guest' => new external_value(PARAM_INT, 'guest access'),
                            'summary_files' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'url' => new external_value(PARAM_TEXT, 'item url'),
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );
    }

    public static function get_category_categories_and_courses($category) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_category_categories_and_courses_parameters(),
                array('category' => $category));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_category_categories_and_courses ($category);

        return $return;
    }

    /* my_enrolments */
    public static function my_enrolments_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'Username')
                        )
        );
    }

    public static function my_enrolments_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'group record id'),
                    'fullname' => new external_value(PARAM_TEXT, 'course name'),
                    'summary' => new external_value(PARAM_RAW, 'summary'),
                    'startdate' => new external_value(PARAM_INT, 'course start date'),
                    'category' => new external_value(PARAM_INT, 'course category id'),
                    'cat_name' => new external_value(PARAM_TEXT, 'course category name'),
                    'can_unenrol' => new external_value(PARAM_INT, 'user can self unenrol'),
                    'summary_files' => new external_multiple_structure(
                        new external_single_structure(
                           array(
                              'url' => new external_value(PARAM_TEXT, 'item url'),
                           )
                        )
                     ),
                    'status' => new external_value(PARAM_INT, 'enrolment status'),
                    'timestart' => new external_value(PARAM_INT, 'enrolment start time'),
                    'timeend' => new external_value(PARAM_INT, 'enrolment end time'),
                    'groups' => new external_multiple_structure(
                        new external_single_structure(
                           array(
                              'id' => new external_value(PARAM_INT, 'group id'),
                              'name' => new external_value(PARAM_TEXT, 'group name'),
                           )
                        )
                     )
                )
            )
        );
    }

    public static function my_enrolments($username, $order_by_cat) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::my_enrolments_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->my_enrolments ($username);

        return $return;
    }

    public static function my_courses_completion_progress_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        )
        );
    }

    public static function my_courses_completion_progress_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'group record id'),
                    'fullname' => new external_value(PARAM_TEXT, 'course name'),
                    'summary' => new external_value(PARAM_RAW, 'summary'),
                    'category' => new external_value(PARAM_INT, 'course category id'),
                    'cat_name' => new external_value(PARAM_TEXT, 'course category name'),
                    'can_unenrol' => new external_value(PARAM_INT, 'user can self unenrol'),
                    'summary_files' => new external_multiple_structure(
                        new external_single_structure(
                           array(
                              'url' => new external_value(PARAM_TEXT, 'item url'),
                           )
                        )
                     ),
                    'progress' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                            'id' => new external_value(PARAM_INT, 'id'),
                            'name' => new external_value(PARAM_TEXT, 'name'),
                            'type' => new external_value(PARAM_TEXT, 'type'),
                            'link' => new external_value(PARAM_RAW, 'link'),
                            'completed' => new external_value(PARAM_TEXT, 'completed'),
                            'available' => new external_value(PARAM_INT, 'available'),
                            )
                        )
                    )
                )
            )
        );
    }

    public static function my_courses_completion_progress($username) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::my_courses_completion_progress_parameters(), array('username' => $username));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->my_courses_completion_progress ($username);

        return $return;
    }

    /* get_mentees_certificates */
    public static function get_mentees_certificates_parameters() {
        return new external_function_parameters(
                        array(
                            'username' => new external_value(PARAM_TEXT, 'username'),
                            'type' => new external_value(PARAM_TEXT, 'type')
                        )
        );
    }

    public static function get_mentees_certificates_returns() {
        return new external_multiple_structure(
            new external_single_structure(
               array(
                  'username' => new external_value(PARAM_TEXT, 'username'),
                  'name' => new external_value(PARAM_TEXT, 'name'),
                  'certificates' => new external_multiple_structure(
                           new external_single_structure(
                              array(
                                'name' => new external_value(PARAM_TEXT, 'name'),
                                'id' => new external_value(PARAM_INT, 'id')
                              )
                           )
                        )
               )
            )
        );
    }

    public static function get_mentees_certificates($username, $type) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_mentees_certificates_parameters(), array('username' => $username,
                    'type' => $type));

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_mentees_certificates ($username, $type);

        return $return;
    }

    /* get_moodle_version */
    public static function get_moodle_version_parameters() {
        return new external_function_parameters(
                        array(
                  )
            );
    }

    public static function get_moodle_version_returns() {
        return new  external_value(PARAM_INT, 'Moodle version');
    }

    public static function get_moodle_version($search) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::get_moodle_version_parameters(), array());

        $auth = new  auth_plugin_joomdle ();
        $return = $auth->get_moodle_version ();

        return $return;
    }

}
