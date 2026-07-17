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
 * @copyright  2009 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/auth/joomdle/auth.php');

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
        $params = self::validate_parameters(self::user_id_parameters(), array('username' => $username));

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:viewdetails', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->user_id($params['username']);

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
                'include_hidden' => new external_value(PARAM_INT, 'Include hidden courses', VALUE_OPTIONAL),
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

    public static function list_courses($enrollable_only, $sortby, $guest, $username, $include_hidden = 0) {
        $params = self::validate_parameters(
            self::list_courses_parameters(),
            array('enrollable_only' => $enrollable_only, 'sortby' => $sortby, 'guest' => $guest, 'username' => $username, 'include_hidden' => $include_hidden)
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/course:view', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->list_courses(
            $params['enrollable_only'],
            $params['sortby'],
            $params['guest'],
            $params['username'],
            $params['include_hidden']
        );

        return $id;
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
        $params = self::validate_parameters(
            self::my_courses_parameters(),
            array('username' => $username, 'order_by_cat' => $order_by_cat)
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/course:view', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->my_courses($username, $order_by_cat);

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
        $params = self::validate_parameters(self::get_course_info_parameters(), array('id' => $id, 'username' => $username));

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/course:view', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_course_info($id, $username);

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
        $params = self::validate_parameters(self::get_course_contents_parameters(), array('id' => $id));

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/course:view', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_course_contents($id);

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

        $params = self::validate_parameters(
            self::courses_by_category_parameters(),
            array('category' => $category, 'enrollable_only' => $enrollable_only, 'username' => $username)
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/course:view', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->courses_by_category($category, $enrollable_only, $username);

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
        $params = self::validate_parameters(self::get_course_categories_parameters(), array('category' => $category));

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/category:viewcourselist', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_course_categories($params['category']);

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
        global $DB;

        $params = self::validate_parameters(self::get_course_editing_teachers_parameters(), array('id' => $id));

        $DB->get_record('course', array('id' => $params['id']), '*', MUST_EXIST);
        if ($params['id'] == SITEID) {
            $context = context_system::instance();
        } else {
            $context = context_course::instance($params['id']);
        }
        self::validate_context($context);
        course_require_view_participants($context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_course_editing_teachers($params['id']);

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
        global $DB;

        $params = self::validate_parameters(self::get_upcoming_events_parameters(), array('id' => $id));

        $DB->get_record('course', array('id' => $params['id']), '*', MUST_EXIST);
        if ($params['id'] == SITEID) {
            $context = context_system::instance();
        } else {
            $context = context_course::instance($params['id']);
        }
        self::validate_context($context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_upcoming_events($params['id']);

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
        global $DB;

        $params = self::validate_parameters(self::get_course_grade_categories_parameters(), array('id' => $id));

        $DB->get_record('course', array('id' => $params['id']), '*', MUST_EXIST);
        $context = context_course::instance($params['id']);
        self::validate_context($context);
        require_capability('moodle/grade:viewall', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_course_grade_categories($params['id']);

        return $return;
    }

    /* search_courses */
    public static function search_courses_parameters() {
        return new external_function_parameters(
            array(
                'text' => new external_value(PARAM_TEXT, 'text to search'),
                'phrase' => new external_value(PARAM_TEXT, 'search type'),
                'ordering' => new external_value(PARAM_TEXT, 'order'),
                'limit' => new external_value(PARAM_INT, 'limit'),
                'lang' => new external_value(PARAM_TEXT, 'lang'),
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

    public static function search_courses($text, $phrase, $ordering, $limit, $lang) {
        $params = self::validate_parameters(
            self::search_courses_parameters(),
            array('text' => $text, 'phrase' => $phrase, 'ordering' => $ordering, 'limit' => $limit, 'lang' => $lang)
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/course:view', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->search_courses(
            $params['text'],
            $params['phrase'],
            $params['ordering'],
            $params['limit'],
            $params['lang']
        );

        return $return;
    }

    /* search_categories */
    public static function search_categories_parameters() {
        return new external_function_parameters(
            array(
                'text' => new external_value(PARAM_TEXT, 'text to search'),
                'phrase' => new external_value(PARAM_TEXT, 'search type'),
                'ordering' => new external_value(PARAM_TEXT, 'order'),
                'limit' => new external_value(PARAM_INT, 'limit'),
                'lang' => new external_value(PARAM_TEXT, 'lang'),
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
    public static function search_categories($text, $phrase, $ordering, $limit, $lang) {
        $params = self::validate_parameters(
            self::search_categories_parameters(),
            array('text' => $text, 'phrase' => $phrase, 'ordering' => $ordering, 'limit' => $limit, 'lang' => $lang)
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/category:viewcourselist', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->search_categories(
            $params['text'],
            $params['phrase'],
            $params['ordering'],
            $params['limit'],
            $params['lang']
        );

        return $return;
    }

    /* search_topics */
    public static function search_topics_parameters() {
        return new external_function_parameters(
            array(
                'text' => new external_value(PARAM_TEXT, 'text to search'),
                'phrase' => new external_value(PARAM_TEXT, 'search type'),
                'ordering' => new external_value(PARAM_TEXT, 'order'),
                'limit' => new external_value(PARAM_INT, 'limit'),
                'lang' => new external_value(PARAM_TEXT, 'lang'),
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
                    'sec_name' => new external_value(PARAM_TEXT, 'section name'),
                )
            )
        );
    }

    public static function search_topics($text, $phrase, $ordering, $limit, $lang) {
        $params = self::validate_parameters(
            self::search_topics_parameters(),
            array('text' => $text, 'phrase' => $phrase, 'ordering' => $ordering, 'limit' => $limit, 'lang' => $lang)
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/course:view', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->search_topics(
            $params['text'],
            $params['phrase'],
            $params['ordering'],
            $params['limit'],
            $params['lang']
        );

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
                'search' => new external_value(PARAM_TEXT, 'search text'),
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
        $params = self::validate_parameters(
            self::get_moodle_only_users_parameters(),
            array('users' => $users, 'search' => $search)
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:viewalldetails', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_moodle_only_users($params['users'], $params['search']);

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
        $params = self::validate_parameters(
            self::get_moodle_users_parameters(),
            array(
                'limitstart' => $limitstart,
                'limit' => $limit,
                'order' => $order,
                'order_dir' => $order_dir,
                'search' => $search
            )
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:viewalldetails', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_moodle_users($limitstart, $limit, $order, $order_dir, $search);

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
        $params = self::validate_parameters(self::get_moodle_users_number_parameters(), array('search' => $search));

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:viewalldetails', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_moodle_users_number($search);

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
        $params = self::validate_parameters(self::user_exists_parameters(), array('username' => $username));

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:viewalldetails', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->user_exists($username);

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
        $params = self::validate_parameters(self::create_joomdle_user_parameters(), array('username' => $username));

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:create', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->create_joomdle_user($params['username']);

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
        global $DB;

        $params = self::validate_parameters(
            self::enrol_user_parameters(),
            array('username' => $username, 'id' => $id, 'roleid' => $roleid)
        );

        $DB->get_record('course', array('id' => $params['id']), '*', MUST_EXIST);
        $context = context_course::instance($params['id']);
        self::validate_context($context);
        require_capability('enrol/manual:enrol', $context);

        $roleid = $params['roleid'];
        if (!$roleid) {
            foreach (enrol_get_instances($params['id'], true) as $instance) {
                if ($instance->enrol == 'manual') {
                    $roleid = $instance->roleid;
                    break;
                }
            }
        }

        $user = $DB->get_record('user', array('username' => core_text::strtolower($params['username'])));
        if (!$user) {
            $systemcontext = context_system::instance();
            self::validate_context($systemcontext);
            require_capability('moodle/user:create', $systemcontext);
        }

        $auth = new  auth_plugin_joomdle();
        $id = $auth->enrol_user($params['username'], $params['id'], $roleid);

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
        global $DB;

        $params = self::validate_parameters(
            self::multiple_enrol_parameters(),
            array('username' => $username, 'courses' => $courses, 'roleid' => $roleid)
        );

        foreach ($params['courses'] as $course) {
            $DB->get_record('course', array('id' => $course['id']), '*', MUST_EXIST);
            $context = context_course::instance($course['id']);
            self::validate_context($context);
            require_capability('enrol/manual:enrol', $context);

            $assignroleid = $params['roleid'];
            if (!$assignroleid) {
                foreach (enrol_get_instances($course['id'], true) as $instance) {
                    if ($instance->enrol == 'manual') {
                        $assignroleid = $instance->roleid;
                        break;
                    }
                }
            }
        }

        $user = $DB->get_record('user', array('username' => core_text::strtolower($params['username'])));
        if (!$user) {
            $systemcontext = context_system::instance();
            self::validate_context($systemcontext);
            require_capability('moodle/user:create', $systemcontext);
        }

        $auth = new  auth_plugin_joomdle();
        $id = $auth->multiple_enrol($params['username'], $params['courses'], $params['roleid']);

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
                'idnumber' => new external_value(PARAM_TEXT, 'idnumber', VALUE_OPTIONAL),
                'department' => new external_value(PARAM_TEXT, 'department', VALUE_OPTIONAL),
                'picture' => new external_value(PARAM_TEXT, 'picture', VALUE_OPTIONAL),
                'pic_url' => new external_value(PARAM_TEXT, 'pic url', VALUE_OPTIONAL),
                'lastnamephonetic' => new external_value(PARAM_TEXT, 'lastnamephonetic', VALUE_OPTIONAL),
                'firstnamephonetic' => new external_value(PARAM_TEXT, 'firstnamephonetic', VALUE_OPTIONAL),
                'middlename' => new external_value(PARAM_TEXT, 'middlename', VALUE_OPTIONAL),
                'alternatename' => new external_value(PARAM_TEXT, 'alternatename', VALUE_OPTIONAL),
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
        $params = self::validate_parameters(self::user_details_parameters(), array('username' => $username));

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:viewalldetails', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->user_details($params['username']);

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
        $params = self::validate_parameters(self::user_details_by_id_parameters(), array('id' => $id));

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:viewalldetails', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->user_details_by_id($id);

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
        $params = self::validate_parameters(self::migrate_to_joomdle_parameters(), array('username' => $username));

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:update', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->migrate_to_joomdle($params['username']);

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
        $params = self::validate_parameters(
            self::my_events_parameters(),
            array('username' => $username, 'courses' => $courses)
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/calendar:manageentries', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->my_events($params['username'], $params['courses']);

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
        global $DB, $USER;

        $params = self::validate_parameters(self::delete_user_parameters(), array('username' => $username));

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:delete', $context);

        $user = $DB->get_record('user', array(
            'username' => core_text::strtolower($params['username']),
            'deleted' => 0,
        ));

        if (!$user) {
            return false;
        }

        // Only deal with joomdle users
        if ($user->auth != 'joomdle') {
            return false;
        }

        if (is_siteadmin($user)) {
            return false;
        }
        if ($user->id == $USER->id) {
            return false;
        }

        $auth = new  auth_plugin_joomdle();
        $id = $auth->delete_user($params['username']);

        return $id;
    }

    /* system_check */
    public static function system_check_parameters() {
        return new external_function_parameters(
            array()
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
        $params = self::validate_parameters(self::system_check_parameters(), array());

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:configview', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->system_check();

        return $return;
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
        $params = self::validate_parameters(self::update_session_parameters(), array('username' => $username));

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:update', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->update_session($params['username']);

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
        global $DB;

        $params = self::validate_parameters(self::get_cat_name_parameters(), array('cat_id' => $cat_id));

        $category = $DB->get_record('course_categories', array('id' => $params['cat_id']), '*', MUST_EXIST);
        $context = context_coursecat::instance($params['cat_id']);
        self::validate_context($context);
        require_capability('moodle/category:viewcourselist', $context);
        if (!$category->visible) {
            require_capability('moodle/category:viewhiddencategories', $context);
        }

        $auth = new  auth_plugin_joomdle();
        $id = $auth->get_cat_name($params['cat_id']);

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
        $params = self::validate_parameters(
            self::courses_abc_parameters(),
            array('start_chars' => $start_chars, 'username' => $username)
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/category:viewcourselist', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->courses_abc($params['start_chars'], $params['username']);

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
        $params = self::validate_parameters(self::teachers_abc_parameters(), array('start_chars' => $start_chars));

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:viewdetails', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->teachers_abc($params['start_chars']);

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
        $params = self::validate_parameters(self::teacher_courses_parameters(), array('username' => $username));

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/role:review', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->teacher_courses($params['username']);

        return $id;
    }

    /* user_custom_fields */
    public static function user_custom_fields_parameters() {
        return new external_function_parameters(
            array()
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
        $params = self::validate_parameters(self::user_custom_fields_parameters(), array());

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:configview', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->user_custom_fields();

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
        global $DB;

        $params = self::validate_parameters(self::course_enrol_methods_parameters(), array('id' => $id));

        self::validate_context(context_system::instance());
        $course = $DB->get_record('course', array('id' => $params['id']), '*', MUST_EXIST);
        if (!core_course_category::can_view_course_info($course) && !can_access_course($course)) {
            throw new moodle_exception('coursehidden');
        }

        require_capability('moodle/course:enrolreview', context_system::instance());

        $auth = new  auth_plugin_joomdle();
        $methods = $auth->course_enrol_methods($params['id']);

        return $methods;
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
        global $DB;

        $params = self::validate_parameters(
            self::get_course_students_parameters(),
            array('id' => $id, 'active' => $active)
        );

        $DB->get_record('course', array('id' => $params['id']), '*', MUST_EXIST);
        $context = context_course::instance($params['id']);
        self::validate_context($context);
        course_require_view_participants($context);
        require_capability('moodle/course:enrolreview', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_course_students($params['id'], "", $params['active']);

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
        global $DB;

        $params = self::validate_parameters(
            self::multiple_suspend_enrolment_parameters(),
            array('username' => $username, 'courses' => $courses)
        );

        foreach ($params['courses'] as $course) {
            $DB->get_record('course', array('id' => $course['id']), '*', MUST_EXIST);
            $context = context_course::instance($course['id']);
            self::validate_context($context);
            require_capability('enrol/manual:manage', $context);
        }

        $auth = new  auth_plugin_joomdle();
        $id = $auth->multiple_suspend_enrolment($params['username'], $params['courses']);

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
        global $DB;

        $params = self::validate_parameters(
            self::suspend_enrolment_parameters(),
            array('username' => $username, 'id' => $id)
        );

        $DB->get_record('course', array('id' => $params['id']), '*', MUST_EXIST);
        $context = context_course::instance($params['id']);
        self::validate_context($context);
        require_capability('enrol/manual:manage', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->suspend_enrolment($params['username'], $params['id']);

        return $id;
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
                    'code' => new external_value(PARAM_TEXT, 'code', VALUE_OPTIONAL),
                )
            )
        );
    }

    public static function my_certificates($username, $type) {
        $params = self::validate_parameters(
            self::my_certificates_parameters(),
            array('username' => $username, 'type' => $type)
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/grade:viewall', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->my_certificates($params['username'], $params['type']);

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
                            array(
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
        $params = self::validate_parameters(self::get_my_grades_parameters(), array('username' => $username));

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/grade:viewall', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_my_grades($params['username']);

        return $return;
    }

    /* get_cohorts */
    public static function get_cohorts_parameters() {
        return new external_function_parameters(
            array()
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
        $params = self::validate_parameters(self::get_cohorts_parameters(), array());

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/cohort:manage', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_cohorts();

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
        global $DB;

        $params = self::validate_parameters(
            self::add_cohort_member_parameters(),
            array('username' => $username, 'cohort_id' => $cohort_id)
        );

        $cohort = $DB->get_record('cohort', array('id' => $params['cohort_id']), '*', MUST_EXIST);
        $context = context::instance_by_id($cohort->contextid, MUST_EXIST);
        if ($context->contextlevel != CONTEXT_COURSECAT && $context->contextlevel != CONTEXT_SYSTEM) {
            throw new invalid_parameter_exception('Invalid context');
        }
        self::validate_context($context);
        require_capability('moodle/cohort:manage', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->add_cohort_member($params['username'], $params['cohort_id']);

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
                                                        'definition' => new external_value(
                                                            PARAM_RAW,
                                                            'level definition'
                                                        ),
                                                        'score' => new external_value(
                                                            PARAM_FLOAT,
                                                            'grademax'
                                                        ),
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
        global $DB;

        $params = self::validate_parameters(self::get_rubrics_parameters(), array('id' => $id));

        $gradeitem = $DB->get_record('grade_items', array('id' => $params['id']), '*', MUST_EXIST);
        $context = context_course::instance($gradeitem->courseid);
        self::validate_context($context);
        require_capability('moodle/grade:managegradingforms', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_rubrics($params['id']);

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
                                'grademin' => new external_value(PARAM_FLOAT, 'grademin', VALUE_OPTIONAL),
                                'grademax' => new external_value(PARAM_FLOAT, 'grademax'),
                                'finalgrade' => new external_value(PARAM_FLOAT, 'final grade'),
                                'letter' => new external_value(PARAM_TEXT, 'grade letter'),
                                'items' => new external_multiple_structure(
                                    new external_single_structure(
                                        array(
                                            'name' => new external_value(PARAM_TEXT, 'item name'),
                                            'due' => new external_value(PARAM_INT, 'due date'),
                                            'grademax' => new external_value(PARAM_FLOAT, 'grademax'),
                                            'finalgrade' => new external_value(PARAM_TEXT, 'final grade'),
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
        global $DB;

        $params = self::validate_parameters(
            self::get_grade_user_report_parameters(),
            array('id' => $id, 'username' => $username)
        );

        $DB->get_record('course', array('id' => $params['id']), '*', MUST_EXIST);
        $context = context_course::instance($params['id']);
        self::validate_context($context);
        require_capability('moodle/grade:viewall', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_grade_user_report($params['id'], $params['username']);

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
                                                    'finalgrade' => new external_value(PARAM_TEXT, 'final grade'),
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
        $params = self::validate_parameters(self::get_my_grade_user_report_parameters(), array('username' => $username));

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/grade:viewall', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_my_grade_user_report($params['username']);

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
        global $DB;

        $params = self::validate_parameters(self::get_group_members_parameters(), array('id' => $id, 'search' => $search));

        $group = $DB->get_record('groups', array('id' => $params['id']), '*', MUST_EXIST);
        $context = context_course::instance($group->courseid);
        self::validate_context($context);
        require_capability('moodle/course:managegroups', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_group_members($params['id'], $params['search']);

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
        global $DB;

        $params = self::validate_parameters(self::get_course_groups_parameters(), array('id' => $id));

        $DB->get_record('course', array('id' => $params['id']), '*', MUST_EXIST);
        $context = context_course::instance($params['id']);
        self::validate_context($context);
        require_capability('moodle/course:managegroups', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_course_groups($params['id']);

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
        global $DB;

        $params = self::validate_parameters(
            self::remove_cohort_member_parameters(),
            array('username' => $username, 'cohort_id' => $cohort_id)
        );

        $DB->get_record('user', array('username' => strtolower($params['username'])), '*', MUST_EXIST);
        $cohort = $DB->get_record('cohort', array('id' => $params['cohort_id']), '*', MUST_EXIST);
        $context = context::instance_by_id($cohort->contextid, MUST_EXIST);
        if ($context->contextlevel != CONTEXT_COURSECAT && $context->contextlevel != CONTEXT_SYSTEM) {
            throw new invalid_parameter_exception('Invalid context');
        }
        self::validate_context($context);
        require_capability('moodle/cohort:manage', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->remove_cohort_member($params['username'], $params['cohort_id']);

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
        global $DB;

        $params = self::validate_parameters(
            self::multiple_add_cohort_member_parameters(),
            array('username' => $username, 'cohorts' => $cohorts)
        );

        $DB->get_record('user', array('username' => strtolower($params['username'])), '*', MUST_EXIST);
        foreach ($params['cohorts'] as $cohortdata) {
            $cohort = $DB->get_record('cohort', array('id' => $cohortdata['id']), '*', MUST_EXIST);
            $context = context::instance_by_id($cohort->contextid, MUST_EXIST);
            if ($context->contextlevel != CONTEXT_COURSECAT && $context->contextlevel != CONTEXT_SYSTEM) {
                throw new invalid_parameter_exception('Invalid context');
            }
            self::validate_context($context);
            require_capability('moodle/cohort:manage', $context);
        }

        $auth = new  auth_plugin_joomdle();
        $id = $auth->multiple_add_cohort_member($params['username'], $params['cohorts']);

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
        global $DB;

        $params = self::validate_parameters(
            self::multiple_remove_cohort_member_parameters(),
            array('username' => $username, 'cohorts' => $cohorts)
        );

        $DB->get_record('user', array('username' => strtolower($params['username'])), '*', MUST_EXIST);
        foreach ($params['cohorts'] as $cohortdata) {
            $cohort = $DB->get_record('cohort', array('id' => $cohortdata['id']), '*', MUST_EXIST);
            $context = context::instance_by_id($cohort->contextid, MUST_EXIST);
            if ($context->contextlevel != CONTEXT_COURSECAT && $context->contextlevel != CONTEXT_SYSTEM) {
                throw new invalid_parameter_exception('Invalid context');
            }
            self::validate_context($context);
            require_capability('moodle/cohort:manage', $context);
        }

        $auth = new  auth_plugin_joomdle();
        $id = $auth->multiple_remove_cohort_member($params['username'], $params['cohorts']);

        return $id;
    }

    /* get_courses_and_groups */
    public static function get_courses_and_groups_parameters() {
        return new external_function_parameters(
            array()
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
        $params = self::validate_parameters(self::get_courses_and_groups_parameters(), array());

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/category:viewcourselist', $context);
        require_capability('moodle/course:managegroups', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->get_courses_and_groups();

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
        global $DB;

        $params = self::validate_parameters(
            self::multiple_enrol_to_course_and_group_parameters(),
            array('username' => $username, 'courses' => $courses, 'roleid' => $roleid)
        );

        $DB->get_record('user', array('username' => core_text::strtolower($params['username'])), '*', MUST_EXIST);
        foreach ($params['courses'] as $course) {
            $DB->get_record('course', array('id' => $course['id']), '*', MUST_EXIST);
            $DB->get_record('groups', array('id' => $course['group_id'], 'courseid' => $course['id']), '*', MUST_EXIST);
            $context = context_course::instance($course['id']);
            self::validate_context($context);
            require_capability('enrol/manual:enrol', $context);
            require_capability('moodle/course:managegroups', $context);

            $assignroleid = $params['roleid'];
            if (!$assignroleid) {
                foreach (enrol_get_instances($course['id'], true) as $instance) {
                    if ($instance->enrol == 'manual') {
                        $assignroleid = $instance->roleid;
                        break;
                    }
                }
            }
        }

        $auth = new  auth_plugin_joomdle();
        $id = $auth->multiple_enrol_to_course_and_group(
            $params['username'],
            $params['courses'],
            $params['roleid']
        );

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
        global $DB;

        $params = self::validate_parameters(
            self::multiple_remove_from_group_parameters(),
            array('username' => $username, 'courses' => $courses)
        );

        $DB->get_record('user', array('username' => core_text::strtolower($params['username'])), '*', MUST_EXIST);
        foreach ($params['courses'] as $course) {
            $DB->get_record('course', array('id' => $course['id']), '*', MUST_EXIST);
            $DB->get_record('groups', array('id' => $course['group_id'], 'courseid' => $course['id']), '*', MUST_EXIST);
            $context = context_course::instance($course['id']);
            self::validate_context($context);
            require_capability('moodle/course:managegroups', $context);
        }

        $auth = new  auth_plugin_joomdle();
        $id = $auth->multiple_remove_from_group($params['username'], $params['courses']);

        return $id;
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
        global $DB;

        $params = self::validate_parameters(
            self::multiple_unenrol_user_parameters(),
            array('username' => $username, 'courses' => $courses)
        );

        $DB->get_record('user', array('username' => core_text::strtolower($params['username'])), '*', MUST_EXIST);
        foreach ($params['courses'] as $course) {
            $DB->get_record('course', array('id' => $course['id']), '*', MUST_EXIST);
            $context = context_course::instance($course['id']);
            self::validate_context($context);
            require_capability('enrol/manual:unenrol', $context);
        }

        $auth = new  auth_plugin_joomdle();
        $id = $auth->multiple_unenrol_user($params['username'], $params['courses']);

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
        global $DB;

        $params = self::validate_parameters(self::unenrol_user_parameters(), array('username' => $username, 'id' => $id));

        $DB->get_record('user', array('username' => core_text::strtolower($params['username'])), '*', MUST_EXIST);
        $DB->get_record('course', array('id' => $params['id']), '*', MUST_EXIST);
        $context = context_course::instance($params['id']);
        self::validate_context($context);
        require_capability('enrol/manual:unenrol', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->unenrol_user($params['username'], $params['id']);

        return $id;
    }

    /* get_themes */
    public static function get_themes_parameters() {
        return new external_function_parameters(
            array()
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
        $params = self::validate_parameters(self::get_themes_parameters(), array());

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:configview', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_themes();

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
        global $DB;

        $params = self::validate_parameters(
            self::enrol_user_with_start_and_end_date_parameters(),
            array(
                'username' => $username,
                'id' => $id,
                'roleid' => $roleid,
                'start_date' => $start_date,
                'end_date' => $end_date
            )
        );

        $DB->get_record('course', array('id' => $params['id']), '*', MUST_EXIST);
        $context = context_course::instance($params['id']);
        self::validate_context($context);
        require_capability('enrol/manual:enrol', $context);

        $roleid = $params['roleid'];
        if (!$roleid) {
            foreach (enrol_get_instances($params['id'], true) as $instance) {
                if ($instance->enrol == 'manual') {
                    $roleid = $instance->roleid;
                    break;
                }
            }
        }

        $user = $DB->get_record('user', array('username' => core_text::strtolower($params['username'])));
        if (!$user) {
            $systemcontext = context_system::instance();
            self::validate_context($systemcontext);
            require_capability('moodle/user:create', $systemcontext);
        }

        $auth = new  auth_plugin_joomdle();
        $id = $auth->enrol_user(
            $params['username'],
            $params['id'],
            $params['roleid'],
            $params['start_date'],
            $params['end_date']
        );

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
        global $DB, $USER;

        $params = self::validate_parameters(self::my_badges_parameters(), array('username' => $username, 'max' => $max));

        $user = $DB->get_record(
            'user',
            array('username' => core_text::strtolower($params['username'])),
            '*',
            MUST_EXIST
        );
        $context = context_user::instance($user->id);
        self::validate_context($context);
        if ($USER->id != $user->id) {
            require_capability('moodle/badges:viewotherbadges', $context);
        }

        $auth = new  auth_plugin_joomdle();
        $return = $auth->my_badges($params['username'], $params['max']);

        return $return;
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
        global $DB;

        $params = self::validate_parameters(
            self::get_events_parameters(),
            array(
                'username' => $username,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'type' => $type,
                'course_id' => $course_id
            )
        );

        $DB->get_record('user', array('username' => core_text::strtolower($params['username'])), '*', MUST_EXIST);
        if ($params['course_id']) {
            $DB->get_record('course', array('id' => $params['course_id']), '*', MUST_EXIST);
        }
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/calendar:manageentries', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_events(
            $params['username'],
            $params['start_date'],
            $params['end_date'],
            $params['type'],
            $params['course_id']
        );

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
        global $DB;

        $params = self::validate_parameters(self::get_event_parameters(), array('id' => $id));

        $DB->get_record('event', array('id' => $params['id']), '*', MUST_EXIST);
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/calendar:manageentries', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_event($params['id']);

        return $return;
    }

    /* my_completed_courses */
    public static function my_completed_courses_parameters() {
        return new external_function_parameters(
            array(
                'username' => new external_value(PARAM_TEXT, 'username'),
                'order_by_cat' => new external_value(PARAM_INT, 'order by category'),
            )
        );
    }

    public static function my_completed_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'course id'),
                    'timecompleted' => new external_value(PARAM_INT, 'time completed'),
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

    public static function my_completed_courses($username, $order_by_cat) {
        global $DB;

        $params = self::validate_parameters(self::my_completed_courses_parameters(), array(
            'username' => $username,
            'order_by_cat' => $order_by_cat
        ));

        $DB->get_record('user', array('username' => core_text::strtolower($params['username'])), '*', MUST_EXIST);
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('report/completion:view', $context);

        $auth = new  auth_plugin_joomdle();
        $courses = $auth->my_completed_courses($params['username'], $params['order_by_cat']);

        return $courses;
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
        $params = self::validate_parameters(self::get_completed_course_users_parameters(), array('id' => $id));

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('report/completion:view', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_completed_course_users($id);

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

    public static function change_username($old_username, $new_username) {
        global $DB;

        $params = self::validate_parameters(self::change_username_parameters(), array(
            'old_username' => $old_username,
            'new_username' => $new_username
        ));

        $DB->get_record('user', array('username' => core_text::strtolower($params['old_username'])), '*', MUST_EXIST);
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:update', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->change_username($params['old_username'], $params['new_username']);

        return $id;
    }

    /* get_moodle_version */
    public static function get_moodle_version_parameters() {
        return new external_function_parameters(
            array()
        );
    }

    public static function get_moodle_version_returns() {
        return new  external_value(PARAM_INT, 'Moodle version');
    }

    public static function get_moodle_version() {
        $params = self::validate_parameters(self::get_moodle_version_parameters(), array());

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:configview', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_moodle_version();

        return $return;
    }

    /* enable_user */
    public static function enable_user_parameters() {
        return new external_function_parameters(
            array(
                'username' => new external_value(PARAM_TEXT, 'username'),
                'suspended' => new external_value(PARAM_INT, 'suspend user'),
            )
        );
    }

    public static function enable_user_returns() {
        return new  external_value(PARAM_INT, 'multilang compatible name, course unique');
    }

    public static function enable_user($username, $suspended) {
        global $DB;

        $params = self::validate_parameters(self::enable_user_parameters(), array('username' => $username, 'suspended' => $suspended));

        $DB->get_record('user', array('username' => core_text::strtolower($params['username'])), '*', MUST_EXIST);
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:update', $context);

        $auth = new  auth_plugin_joomdle();
        $auth->enable_user($params['username'], $params['suspended']);

        return $params['suspended'];
    }

    /* get_course_users */
    public static function get_course_users_parameters() {
        return new external_function_parameters(
            array(
                'course_id' => new external_value(PARAM_INT, 'course id'),
            )
        );
    }

    public static function get_course_users_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'username' => new external_value(PARAM_TEXT, 'username'),
                    'roles' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'role id'),
                                'name' => new external_value(PARAM_TEXT, 'role name'),
                            )
                        )
                    )
                )
            )
        );
    }

    public static function get_course_users($course_id) {
        global $DB;

        $params = self::validate_parameters(
            self::get_course_users_parameters(),
            array('course_id' => $course_id)
        );

        $DB->get_record('course', array('id' => $params['course_id']), '*', MUST_EXIST);
        $context = context_course::instance($params['course_id']);
        self::validate_context($context);
        course_require_view_participants($context);
        require_capability('moodle/role:review', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_course_users($params['course_id']);

        return $return;
    }
}
