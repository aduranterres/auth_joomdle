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

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/auth/joomdle/auth.php');

/**
 * External web service definitions for the Joomdle authentication plugin.
 *
 * @package auth_joomdle
 */
class joomdle_helpers_external extends external_api {
    /**
     * Resolves the requested manual enrolment role and verifies it is assignable.
     *
     * @param int $courseid Course id.
     * @param int $roleid Requested role id, or zero to use the manual enrolment default.
     * @param context_course $context Course context.
     * @return int The validated role id.
     */
    private static function resolve_assignable_enrol_role($courseid, $roleid, context_course $context) {
        if (!$roleid) {
            foreach (enrol_get_instances($courseid, true) as $instance) {
                if ($instance->enrol == 'manual') {
                    $roleid = $instance->roleid;
                    break;
                }
            }
        }

        $assignableroles = get_assignable_roles($context);
        if (!array_key_exists($roleid, $assignableroles)) {
            $errorparams = new stdClass();
            $errorparams->roleid = $roleid;
            $errorparams->courseid = $courseid;
            throw new moodle_exception('wsusercannotassign', 'enrol_manual', '', $errorparams);
        }

        return $roleid;
    }

    /**
     * Defines parameters for the certificate generation web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_certificate_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_USERNAME, 'Target Moodle username'),
                'type' => new external_value(PARAM_ALPHA, 'Certificate type'),
                'id' => new external_value(PARAM_INT, 'Course module id'),
            ]
        );
    }

    /**
     * Defines the certificate generation web service response.
     *
     * @return external_description The return structure.
     */
    public static function get_certificate_returns() {
        return new external_single_structure(
            [
                'filename' => new external_value(PARAM_FILE, 'Certificate filename'),
                'mimetype' => new external_value(PARAM_TEXT, 'Certificate MIME type'),
                'encoding' => new external_value(PARAM_ALPHANUM, 'Content encoding'),
                'content' => new external_value(PARAM_RAW, 'Encoded certificate content'),
            ]
        );
    }

    /**
     * Generates a certificate for a Moodle user.
     *
     * @param string $username Target Moodle username.
     * @param string $type Certificate type.
     * @param int $id Course module id.
     * @return array The encoded certificate and its metadata.
     */
    public static function get_certificate($username, $type, $id) {
        $params = self::validate_parameters(
            self::get_certificate_parameters(),
            ['username' => $username, 'type' => $type, 'id' => $id]
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/grade:viewall', $context);

        $manager = new \auth_joomdle\certificate\manager();
        return $manager->generate($params['username'], $params['type'], $params['id']);
    }

    /**
     * Defines parameters for the user id web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function user_id_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'multilang compatible name, course unique'),
            ]
        );
    }

    /**
     * Defines the return structure for the user id web service.
     *
     * @return external_description The return structure.
     */
    public static function user_id_returns() {
        return new  external_value(PARAM_INT, 'multilang compatible name, course unique');
    }

    /**
     * Executes the user id web service.
     *
     * @param mixed $username The username value.
     * @return mixed The web service result.
     */
    public static function user_id($username) {
        $params = self::validate_parameters(self::user_id_parameters(), ['username' => $username]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:viewdetails', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->user_id($params['username']);

        return $id;
    }

    /**
     * Defines parameters for the list courses web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function list_courses_parameters() {
        return new external_function_parameters(
            [
                'enrollable_only' => new external_value(PARAM_INT, 'Return only enrollable courses'),
                'sortby' => new external_value(PARAM_TEXT, 'Order field'),
                'guest' => new external_value(PARAM_INT, 'Return only courses for guests'),
                'username' => new external_value(PARAM_TEXT, 'username'),
                'include_hidden' => new external_value(PARAM_INT, 'Include hidden courses'),
            ]
        );
    }

    /**
     * Defines the return structure for the list courses web service.
     *
     * @return external_description The return structure.
     */
    public static function list_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
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
                            [
                                'url' => new external_value(PARAM_TEXT, 'item url'),
                            ]
                        )
                    ),
                ]
            )
        );
    }

    /**
     * Executes the list courses web service.
     *
     * @param mixed $enrollableonly The enrollable_only value.
     * @param mixed $sortby The sortby value.
     * @param mixed $guest The guest value.
     * @param mixed $username The username value.
     * @param mixed $includehidden The include_hidden value.
     * @return mixed The web service result.
     */
    public static function list_courses($enrollableonly, $sortby, $guest, $username, $includehidden = 0) {
        $params = self::validate_parameters(
            self::list_courses_parameters(),
            ['enrollable_only' => $enrollableonly, 'sortby' => $sortby, 'guest' => $guest,
                'username' => $username, 'include_hidden' => $includehidden]
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

    /**
     * Defines parameters for the my courses web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function my_courses_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'Username'),
                'order_by_cat' => new external_value(PARAM_INT, 'order by category'),
            ]
        );
    }

    /**
     * Defines the return structure for the my courses web service.
     *
     * @return external_description The return structure.
     */
    public static function my_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'id' => new external_value(PARAM_INT, 'group record id'),
                    'fullname' => new external_value(PARAM_TEXT, 'course name'),
                    'summary' => new external_value(PARAM_RAW, 'summary'),
                    'category' => new external_value(PARAM_INT, 'course category id'),
                    'cat_name' => new external_value(PARAM_TEXT, 'course category name'),
                    'can_unenrol' => new external_value(PARAM_INT, 'user can self unenrol'),
                    'summary_files' => new external_multiple_structure(
                        new external_single_structure(
                            [
                                'url' => new external_value(PARAM_TEXT, 'item url'),
                            ]
                        )
                    ),
                ]
            )
        );
    }

    /**
     * Executes the my courses web service.
     *
     * @param mixed $username The username value.
     * @param mixed $orderbycat The order_by_cat value.
     * @return mixed The web service result.
     */
    public static function my_courses($username, $orderbycat) {
        $params = self::validate_parameters(
            self::my_courses_parameters(),
            ['username' => $username, 'order_by_cat' => $orderbycat]
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/course:view', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->my_courses($username, $orderbycat);

        return $return;
    }


    /**
     * Defines parameters for the get course info web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_course_info_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'course id'),
                'username' => new external_value(PARAM_TEXT, 'username'),
            ]
        );
    }

    /**
     * Defines the return structure for the get course info web service.
     *
     * @return external_description The return structure.
     */
    public static function get_course_info_returns() {
        return new external_single_structure(
            [
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
                'visible' => new external_value(PARAM_INT, 'visible'),
                'guest' => new external_value(PARAM_INT, 'guest access'),
                'summary_files' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'url' => new external_value(PARAM_TEXT, 'item url'),
                        ]
                    )
                ),
            ]
        );
    }

    /**
     * Executes the get course info web service.
     *
     * @param mixed $id The id value.
     * @param mixed $username The username value.
     * @return mixed The web service result.
     */
    public static function get_course_info($id, $username) {
        $params = self::validate_parameters(self::get_course_info_parameters(), ['id' => $id, 'username' => $username]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/course:view', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_course_info($id, $username);

        return $return;
    }

    /**
     * Defines parameters for the get course contents web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_course_contents_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'course id'),
            ]
        );
    }

    /**
     * Defines the return structure for the get course contents web service.
     *
     * @return external_description The return structure.
     */
    public static function get_course_contents_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'section' => new external_value(PARAM_INT, 'section id'),
                    'name' => new external_value(PARAM_TEXT, 'section name'),
                    'summary' => new external_value(PARAM_RAW, 'summary'),
                ]
            )
        );
    }

    /**
     * Executes the get course contents web service.
     *
     * @param mixed $id The id value.
     * @return mixed The web service result.
     */
    public static function get_course_contents($id) {
        $params = self::validate_parameters(self::get_course_contents_parameters(), ['id' => $id]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/course:view', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_course_contents($id);

        return $return;
    }

    /**
     * Defines parameters for the courses by category web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function courses_by_category_parameters() {
        return new external_function_parameters(
            [
                'category' => new external_value(PARAM_INT, 'category id'),
                'enrollable_only' => new external_value(PARAM_INT, 'Return only enrollable courses'),
                'username' => new external_value(PARAM_TEXT, 'username'),
            ]
        );
    }

    /**
     * Defines the return structure for the courses by category web service.
     *
     * @return external_description The return structure.
     */
    public static function courses_by_category_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
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
                            [
                                'url' => new external_value(PARAM_TEXT, 'item url'),
                            ]
                        )
                    ),
                ]
            )
        );
    }

    /**
     * Executes the courses by category web service.
     *
     * @param mixed $category The category value.
     * @param mixed $enrollableonly The enrollable_only value.
     * @param mixed $username The username value.
     * @return mixed The web service result.
     */
    public static function courses_by_category($category, $enrollableonly, $username) {
        global $CFG, $DB;

        $params = self::validate_parameters(
            self::courses_by_category_parameters(),
            ['category' => $category, 'enrollable_only' => $enrollableonly, 'username' => $username]
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/course:view', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->courses_by_category($category, $enrollableonly, $username);

        return $return;
    }


    /**
     * Defines parameters for the get course categories web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_course_categories_parameters() {
        return new external_function_parameters(
            [
                'category' => new external_value(PARAM_INT, 'category id'),
            ]
        );
    }

    /**
     * Defines the return structure for the get course categories web service.
     *
     * @return external_description The return structure.
     */
    public static function get_course_categories_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'id' => new external_value(PARAM_INT, 'category id'),
                    'name' => new external_value(PARAM_TEXT, 'category name'),
                    'description' => new external_value(PARAM_RAW, 'description'),
                ]
            )
        );
    }

    /**
     * Executes the get course categories web service.
     *
     * @param mixed $category The category value.
     * @return mixed The web service result.
     */
    public static function get_course_categories($category) {
        $params = self::validate_parameters(self::get_course_categories_parameters(), ['category' => $category]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/category:viewcourselist', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_course_categories($params['category']);

        return $return;
    }

    /**
     * Defines parameters for the get course editing teachers web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_course_editing_teachers_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'course id'),
            ]
        );
    }

    /**
     * Defines the return structure for the get course editing teachers web service.
     *
     * @return external_description The return structure.
     */
    public static function get_course_editing_teachers_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'firstname' => new external_value(PARAM_TEXT, 'firstname'),
                    'lastname' => new external_value(PARAM_TEXT, 'lastname'),
                    'username' => new external_value(PARAM_TEXT, 'username'),
                ]
            )
        );
    }

    /**
     * Executes the get course editing teachers web service.
     *
     * @param mixed $id The id value.
     * @return mixed The web service result.
     */
    public static function get_course_editing_teachers($id) {
        global $DB;

        $params = self::validate_parameters(self::get_course_editing_teachers_parameters(), ['id' => $id]);

        $DB->get_record('course', ['id' => $params['id']], '*', MUST_EXIST);
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

    /**
     * Defines parameters for the get upcoming events web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_upcoming_events_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'course id'),
            ]
        );
    }

    /**
     * Defines the return structure for the get upcoming events web service.
     *
     * @return external_description The return structure.
     */
    public static function get_upcoming_events_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'name' => new external_value(PARAM_TEXT, 'event name'),
                    'timestart' => new external_value(PARAM_INT, 'start time'),
                    'courseid' => new external_value(PARAM_INT, 'course id'),
                ]
            )
        );
    }

    /**
     * Executes the get upcoming events web service.
     *
     * @param mixed $id The id value.
     * @return mixed The web service result.
     */
    public static function get_upcoming_events($id) {
        global $DB;

        $params = self::validate_parameters(self::get_upcoming_events_parameters(), ['id' => $id]);

        $DB->get_record('course', ['id' => $params['id']], '*', MUST_EXIST);
        if ($params['id'] == SITEID) {
            $context = context_system::instance();
        } else {
            $context = context_course::instance($params['id']);
        }
        self::validate_context($context);

        require_capability('moodle/course:view', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_upcoming_events($params['id']);

        return $return;
    }

    /**
     * Defines parameters for the get course grade categories web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_course_grade_categories_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'course id'),
            ]
        );
    }

    /**
     * Defines the return structure for the get course grade categories web service.
     *
     * @return external_description The return structure.
     */
    public static function get_course_grade_categories_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'fullname' => new external_value(PARAM_TEXT, 'item name'),
                    'grademax' => new external_value(PARAM_TEXT, 'final grade'),
                ]
            )
        );
    }

    /**
     * Executes the get course grade categories web service.
     *
     * @param mixed $id The id value.
     * @return mixed The web service result.
     */
    public static function get_course_grade_categories($id) {
        global $DB;

        $params = self::validate_parameters(self::get_course_grade_categories_parameters(), ['id' => $id]);

        $DB->get_record('course', ['id' => $params['id']], '*', MUST_EXIST);
        $context = context_course::instance($params['id']);
        self::validate_context($context);
        require_capability('moodle/grade:viewall', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_course_grade_categories($params['id']);

        return $return;
    }

    /**
     * Defines parameters for the search courses web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function search_courses_parameters() {
        return new external_function_parameters(
            [
                'text' => new external_value(PARAM_TEXT, 'text to search'),
                'phrase' => new external_value(PARAM_TEXT, 'search type'),
                'ordering' => new external_value(PARAM_TEXT, 'order'),
                'limit' => new external_value(PARAM_INT, 'limit'),
                'lang' => new external_value(PARAM_TEXT, 'lang'),
            ]
        );
    }

    /**
     * Defines the return structure for the search courses web service.
     *
     * @return external_description The return structure.
     */
    public static function search_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
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
                ]
            )
        );
    }

    /**
     * Executes the search courses web service.
     *
     * @param mixed $text The text value.
     * @param mixed $phrase The phrase value.
     * @param mixed $ordering The ordering value.
     * @param mixed $limit The limit value.
     * @param mixed $lang The lang value.
     * @return mixed The web service result.
     */
    public static function search_courses($text, $phrase, $ordering, $limit, $lang) {
        $params = self::validate_parameters(
            self::search_courses_parameters(),
            ['text' => $text, 'phrase' => $phrase, 'ordering' => $ordering, 'limit' => $limit, 'lang' => $lang]
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

    /**
     * Defines parameters for the search categories web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function search_categories_parameters() {
        return new external_function_parameters(
            [
                'text' => new external_value(PARAM_TEXT, 'text to search'),
                'phrase' => new external_value(PARAM_TEXT, 'search type'),
                'ordering' => new external_value(PARAM_TEXT, 'order'),
                'limit' => new external_value(PARAM_INT, 'limit'),
                'lang' => new external_value(PARAM_TEXT, 'lang'),
            ]
        );
    }

    /**
     * Defines the return structure for the search categories web service.
     *
     * @return external_description The return structure.
     */
    public static function search_categories_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'cat_id' => new external_value(PARAM_INT, 'category id'),
                    'cat_name' => new external_value(PARAM_TEXT, 'category name'),
                    'cat_description' => new external_value(PARAM_RAW, 'category description'),
                ]
            )
        );
    }
    /**
     * Executes the search categories web service.
     *
     * @param mixed $text The text value.
     * @param mixed $phrase The phrase value.
     * @param mixed $ordering The ordering value.
     * @param mixed $limit The limit value.
     * @param mixed $lang The lang value.
     * @return mixed The web service result.
     */
    public static function search_categories($text, $phrase, $ordering, $limit, $lang) {
        $params = self::validate_parameters(
            self::search_categories_parameters(),
            ['text' => $text, 'phrase' => $phrase, 'ordering' => $ordering, 'limit' => $limit, 'lang' => $lang]
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

    /**
     * Defines parameters for the search topics web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function search_topics_parameters() {
        return new external_function_parameters(
            [
                'text' => new external_value(PARAM_TEXT, 'text to search'),
                'phrase' => new external_value(PARAM_TEXT, 'search type'),
                'ordering' => new external_value(PARAM_TEXT, 'order'),
                'limit' => new external_value(PARAM_INT, 'limit'),
                'lang' => new external_value(PARAM_TEXT, 'lang'),
            ]
        );
    }

    /**
     * Defines the return structure for the search topics web service.
     *
     * @return external_description The return structure.
     */
    public static function search_topics_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'remoteid' => new external_value(PARAM_INT, 'course id'),
                    'fullname' => new external_value(PARAM_TEXT, 'course name'),
                    'course' => new external_value(PARAM_TEXT, 'course name'),
                    'section' => new external_value(PARAM_TEXT, 'course name'),
                    'summary' => new external_value(PARAM_RAW, 'summary'),
                    'cat_id' => new external_value(PARAM_INT, 'category id'),
                    'cat_name' => new external_value(PARAM_TEXT, 'category name'),
                    'sec_name' => new external_value(PARAM_TEXT, 'section name'),
                ]
            )
        );
    }

    /**
     * Executes the search topics web service.
     *
     * @param mixed $text The text value.
     * @param mixed $phrase The phrase value.
     * @param mixed $ordering The ordering value.
     * @param mixed $limit The limit value.
     * @param mixed $lang The lang value.
     * @return mixed The web service result.
     */
    public static function search_topics($text, $phrase, $ordering, $limit, $lang) {
        $params = self::validate_parameters(
            self::search_topics_parameters(),
            ['text' => $text, 'phrase' => $phrase, 'ordering' => $ordering, 'limit' => $limit, 'lang' => $lang]
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

    /**
     * Defines parameters for the get moodle only users web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_moodle_only_users_parameters() {
        return new external_function_parameters(
            [
                'users' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'username' => new external_value(PARAM_TEXT, 'username'),
                        ]
                    )
                ),
                'search' => new external_value(PARAM_TEXT, 'search text'),
            ]
        );
    }

    /**
     * Defines the return structure for the get moodle only users web service.
     *
     * @return external_description The return structure.
     */
    public static function get_moodle_only_users_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'id' => new external_value(PARAM_INT, 'user id'),
                    'username' => new external_value(PARAM_TEXT, 'username'),
                    'email' => new external_value(PARAM_TEXT, 'email'),
                    'name' => new external_value(PARAM_TEXT, 'name'),
                    'auth' => new external_value(PARAM_TEXT, 'auth plugin'),
                    'admin' => new external_value(PARAM_INT, 'admin user'),
                ]
            )
        );
    }

    /**
     * Executes the get moodle only users web service.
     *
     * @param mixed $users The users value.
     * @param mixed $search The search value.
     * @return mixed The web service result.
     */
    public static function get_moodle_only_users($users, $search) {
        $params = self::validate_parameters(
            self::get_moodle_only_users_parameters(),
            ['users' => $users, 'search' => $search]
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:viewalldetails', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_moodle_only_users($params['users'], $params['search']);

        return $return;
    }

    /**
     * Defines parameters for the get moodle users web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_moodle_users_parameters() {
        return new external_function_parameters(
            [
                'limitstart' => new external_value(PARAM_INT, 'limit start'),
                'limit' => new external_value(PARAM_INT, 'limit'),
                'order' => new external_value(PARAM_TEXT, 'order'),
                'order_dir' => new external_value(PARAM_TEXT, 'order dir'),
                'search' => new external_value(PARAM_TEXT, 'search text'),
            ]
        );
    }

    /**
     * Defines the return structure for the get moodle users web service.
     *
     * @return external_description The return structure.
     */
    public static function get_moodle_users_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'id' => new external_value(PARAM_INT, 'user id'),
                    'username' => new external_value(PARAM_TEXT, 'username'),
                    'email' => new external_value(PARAM_TEXT, 'email'),
                    'name' => new external_value(PARAM_TEXT, 'name'),
                    'auth' => new external_value(PARAM_TEXT, 'auth plugin'),
                    'admin' => new external_value(PARAM_INT, 'admin user'),
                ]
            )
        );
    }

    /**
     * Executes the get moodle users web service.
     *
     * @param mixed $limitstart The limitstart value.
     * @param mixed $limit The limit value.
     * @param mixed $order The order value.
     * @param mixed $orderdir The order_dir value.
     * @param mixed $search The search value.
     * @return mixed The web service result.
     */
    public static function get_moodle_users($limitstart, $limit, $order, $orderdir, $search) {
        $params = self::validate_parameters(
            self::get_moodle_users_parameters(),
            [
                'limitstart' => $limitstart,
                'limit' => $limit,
                'order' => $order,
                'order_dir' => $orderdir,
                'search' => $search,
            ]
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:viewalldetails', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_moodle_users($limitstart, $limit, $order, $orderdir, $search);

        return $return;
    }

    /**
     * Defines parameters for the get moodle users number web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_moodle_users_number_parameters() {
        return new external_function_parameters(
            [
                'search' => new external_value(PARAM_TEXT, 'sarch text'),
            ]
        );
    }

    /**
     * Defines the return structure for the get moodle users number web service.
     *
     * @return external_description The return structure.
     */
    public static function get_moodle_users_number_returns() {
        return new  external_value(PARAM_INT, 'user number');
    }

    /**
     * Executes the get moodle users number web service.
     *
     * @param mixed $search The search value.
     * @return mixed The web service result.
     */
    public static function get_moodle_users_number($search) {
        $params = self::validate_parameters(self::get_moodle_users_number_parameters(), ['search' => $search]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:viewalldetails', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_moodle_users_number($search);

        return $return;
    }

    /**
     * Defines parameters for the user exists web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function user_exists_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'multilang compatible name, course unique'),
            ]
        );
    }

    /**
     * Defines the return structure for the user exists web service.
     *
     * @return external_description The return structure.
     */
    public static function user_exists_returns() {
        return new  external_value(PARAM_INT, 'whether user exists');
    }

    /**
     * Executes the user exists web service.
     *
     * @param mixed $username The username value.
     * @return mixed The web service result.
     */
    public static function user_exists($username) {
        $params = self::validate_parameters(self::user_exists_parameters(), ['username' => $username]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:viewalldetails', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->user_exists($username);

        return $id;
    }

    /**
     * Defines parameters for the create joomdle user web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function create_joomdle_user_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
            ]
        );
    }

    /**
     * Defines the return structure for the create joomdle user web service.
     *
     * @return external_description The return structure.
     */
    public static function create_joomdle_user_returns() {
        return new  external_value(PARAM_INT, 'user created');
    }

    /**
     * Executes the create joomdle user web service.
     *
     * @param mixed $username The username value.
     * @return mixed The web service result.
     */
    public static function create_joomdle_user($username) {
        $params = self::validate_parameters(self::create_joomdle_user_parameters(), ['username' => $username]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:create', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->create_joomdle_user($params['username']);

        return $id;
    }

    /**
     * Defines parameters for the enrol user web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function enrol_user_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
                'id' => new external_value(PARAM_INT, 'course id'),
                'roleid' => new external_value(PARAM_INT, 'role id'),
            ]
        );
    }

    /**
     * Defines the return structure for the enrol user web service.
     *
     * @return external_description The return structure.
     */
    public static function enrol_user_returns() {
        return new  external_value(PARAM_INT, 'user created');
    }

    /**
     * Executes the enrol user web service.
     *
     * @param mixed $username The username value.
     * @param mixed $id The id value.
     * @param mixed $roleid The roleid value.
     * @return mixed The web service result.
     */
    public static function enrol_user($username, $id, $roleid) {
        global $DB;

        $params = self::validate_parameters(
            self::enrol_user_parameters(),
            ['username' => $username, 'id' => $id, 'roleid' => $roleid]
        );

        $DB->get_record('course', ['id' => $params['id']], '*', MUST_EXIST);
        $context = context_course::instance($params['id']);
        self::validate_context($context);
        require_capability('enrol/manual:enrol', $context);

        $roleid = self::resolve_assignable_enrol_role($params['id'], $params['roleid'], $context);

        $user = $DB->get_record('user', ['username' => core_text::strtolower($params['username'])]);
        if (!$user) {
            $systemcontext = context_system::instance();
            self::validate_context($systemcontext);
            require_capability('moodle/user:create', $systemcontext);
        }

        $auth = new  auth_plugin_joomdle();
        $id = $auth->enrol_user($params['username'], $params['id'], $roleid);

        return $id;
    }

    /**
     * Defines parameters for the multiple enrol web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function multiple_enrol_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
                'courses' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(PARAM_INT, 'course id'),
                        ]
                    )
                ),
                'roleid' => new external_value(PARAM_INT, 'role id'),
            ]
        );
    }

    /**
     * Defines the return structure for the multiple enrol web service.
     *
     * @return external_description The return structure.
     */
    public static function multiple_enrol_returns() {
        return new  external_value(PARAM_INT, 'user enroled');
    }

    /**
     * Executes the multiple enrol web service.
     *
     * @param mixed $username The username value.
     * @param mixed $courses The courses value.
     * @param mixed $roleid The roleid value.
     * @return mixed The web service result.
     */
    public static function multiple_enrol($username, $courses, $roleid) {
        global $DB;

        $params = self::validate_parameters(
            self::multiple_enrol_parameters(),
            ['username' => $username, 'courses' => $courses, 'roleid' => $roleid]
        );

        foreach ($params['courses'] as $course) {
            $DB->get_record('course', ['id' => $course['id']], '*', MUST_EXIST);
            $context = context_course::instance($course['id']);
            self::validate_context($context);
            require_capability('enrol/manual:enrol', $context);

            self::resolve_assignable_enrol_role($course['id'], $params['roleid'], $context);
        }

        $user = $DB->get_record('user', ['username' => core_text::strtolower($params['username'])]);
        if (!$user) {
            $systemcontext = context_system::instance();
            self::validate_context($systemcontext);
            require_capability('moodle/user:create', $systemcontext);
        }

        $auth = new  auth_plugin_joomdle();
        $id = $auth->multiple_enrol($params['username'], $params['courses'], $params['roleid']);

        return $id;
    }

    /**
     * Defines parameters for the user details web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function user_details_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
            ]
        );
    }

    /**
     * Defines the return structure for the user details web service.
     *
     * @return external_description The return structure.
     */
    public static function user_details_returns() {
        return    new external_single_structure(
            [
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
                        [
                            'id' => new external_value(PARAM_INT, 'field id'),
                            'data' => new external_value(PARAM_RAW, 'data'),
                        ]
                    )
                ),
            ]
        );
    }

    /**
     * Executes the user details web service.
     *
     * @param mixed $username The username value.
     * @return mixed The web service result.
     */
    public static function user_details($username) {
        $params = self::validate_parameters(self::user_details_parameters(), ['username' => $username]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:viewalldetails', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->user_details($params['username']);

        return $id;
    }

    /**
     * Defines parameters for the user details by id web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function user_details_by_id_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'user id'),
            ]
        );
    }

    /**
     * Defines the return structure for the user details by id web service.
     *
     * @return external_description The return structure.
     */
    public static function user_details_by_id_returns() {
        return    new external_single_structure(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
            ]
        );
    }

    /**
     * Executes the user details by id web service.
     *
     * @param mixed $id The id value.
     * @return mixed The web service result.
     */
    public static function user_details_by_id($id) {
        $params = self::validate_parameters(self::user_details_by_id_parameters(), ['id' => $id]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:viewalldetails', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->user_details_by_id($id);

        return $id;
    }

    /**
     * Defines parameters for the migrate to joomdle web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function migrate_to_joomdle_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
            ]
        );
    }

    /**
     * Defines the return structure for the migrate to joomdle web service.
     *
     * @return external_description The return structure.
     */
    public static function migrate_to_joomdle_returns() {
        return new  external_value(PARAM_BOOL, 'user migrated');
    }

    /**
     * Executes the migrate to joomdle web service.
     *
     * @param mixed $username The username value.
     * @return mixed The web service result.
     */
    public static function migrate_to_joomdle($username) {
        global $DB;

        $params = self::validate_parameters(self::migrate_to_joomdle_parameters(), ['username' => $username]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:update', $context);

        $user = $DB->get_record('user', [
            'username' => core_text::strtolower($params['username']),
            'deleted' => 0,
        ]);

        if (!$user) {
            return false;
        }
        if (is_siteadmin($user)) {
            return false;
        }

        $auth = new  auth_plugin_joomdle();
        $id = $auth->migrate_to_joomdle($params['username']);

        return $id;
    }

    /**
     * Defines parameters for the my events web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function my_events_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
                'courses' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(PARAM_INT, 'course id'),
                        ]
                    )
                ),
            ]
        );
    }

    /**
     * Defines the return structure for the my events web service.
     *
     * @return external_description The return structure.
     */
    public static function my_events_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'name' => new external_value(PARAM_TEXT, 'event name'),
                    'timestart' => new external_value(PARAM_INT, 'start time'),
                    'courseid' => new external_value(PARAM_INT, 'course id'),
                ]
            )
        );
    }
    /**
     * Executes the my events web service.
     *
     * @param mixed $username The username value.
     * @param mixed $courses The courses value.
     * @return mixed The web service result.
     */
    public static function my_events($username, $courses) {
        $params = self::validate_parameters(
            self::my_events_parameters(),
            ['username' => $username, 'courses' => $courses]
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/calendar:manageentries', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->my_events($params['username'], $params['courses']);

        return $id;
    }

    /**
     * Defines parameters for the delete user web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function delete_user_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
            ]
        );
    }

    /**
     * Defines the return structure for the delete user web service.
     *
     * @return external_description The return structure.
     */
    public static function delete_user_returns() {
        return new  external_value(PARAM_BOOL, 'user deleted');
    }

    /**
     * Executes the delete user web service.
     *
     * @param mixed $username The username value.
     * @return mixed The web service result.
     */
    public static function delete_user($username) {
        global $DB, $USER;

        $params = self::validate_parameters(self::delete_user_parameters(), ['username' => $username]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:delete', $context);

        $user = $DB->get_record('user', [
            'username' => core_text::strtolower($params['username']),
            'deleted' => 0,
        ]);

        if (!$user) {
            return false;
        }

        // Only deal with joomdle users.
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

    /**
     * Defines parameters for the system check web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function system_check_parameters() {
        return new external_function_parameters(
            []
        );
    }

    /**
     * Defines the return structure for the system check web service.
     *
     * @return external_description The return structure.
     */
    public static function system_check_returns() {
        return new external_single_structure(
            [
                'joomdle_auth' => new external_value(PARAM_INT, 'joomdle plugin enabled'),
                'joomdle_configured' => new external_value(PARAM_INT, 'joomdle configured'),
                'test_data' => new external_value(PARAM_RAW, 'test data', VALUE_OPTIONAL),
                'release' => new external_value(PARAM_TEXT, 'Joomdle release'),
                'curl_blocked' => new external_value(PARAM_INT, 'curl blocked'),
            ]
        );
    }

    /**
     * Executes the system check web service.
     *
     * @return mixed The web service result.
     */
    public static function system_check() {
        $params = self::validate_parameters(self::system_check_parameters(), []);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:configview', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->system_check();

        return $return;
    }

    /**
     * Defines parameters for the update session web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function update_session_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
            ]
        );
    }

    /**
     * Defines the return structure for the update session web service.
     *
     * @return external_description The return structure.
     */
    public static function update_session_returns() {
        return new  external_value(PARAM_BOOL, 'session updated');
    }

    /**
     * Executes the update session web service.
     *
     * @param mixed $username The username value.
     * @return mixed The web service result.
     */
    public static function update_session($username) {
        $params = self::validate_parameters(self::update_session_parameters(), ['username' => $username]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:update', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->update_session($params['username']);

        return $id;
    }

    /**
     * Defines parameters for the get cat name web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_cat_name_parameters() {
        return new external_function_parameters(
            [
                'cat_id' => new external_value(PARAM_INT, 'category id'),
            ]
        );
    }

    /**
     * Defines the return structure for the get cat name web service.
     *
     * @return external_description The return structure.
     */
    public static function get_cat_name_returns() {
        return new  external_value(PARAM_TEXT, 'category name');
    }

    /**
     * Executes the get cat name web service.
     *
     * @param mixed $catid The cat_id value.
     * @return mixed The web service result.
     */
    public static function get_cat_name($catid) {
        global $DB;

        $params = self::validate_parameters(self::get_cat_name_parameters(), ['cat_id' => $catid]);

        $category = $DB->get_record('course_categories', ['id' => $params['cat_id']], '*', MUST_EXIST);
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

    /**
     * Defines parameters for the courses abc web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function courses_abc_parameters() {
        return new external_function_parameters(
            [
                'start_chars' => new external_value(PARAM_TEXT, 'Start chars'),
                'username' => new external_value(PARAM_TEXT, 'username'),
            ]
        );
    }

    /**
     * Defines the return structure for the courses abc web service.
     *
     * @return external_description The return structure.
     */
    public static function courses_abc_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
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
                            [
                                'url' => new external_value(PARAM_TEXT, 'item url'),
                            ]
                        )
                    ),
                ]
            )
        );
    }

    /**
     * Executes the courses abc web service.
     *
     * @param mixed $startchars The start_chars value.
     * @param mixed $username The username value.
     * @return mixed The web service result.
     */
    public static function courses_abc($startchars, $username) {
        $params = self::validate_parameters(
            self::courses_abc_parameters(),
            ['start_chars' => $startchars, 'username' => $username]
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/category:viewcourselist', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->courses_abc($params['start_chars'], $params['username']);

        return $id;
    }

    /**
     * Defines parameters for the teachers abc web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function teachers_abc_parameters() {
        return new external_function_parameters(
            [
                'start_chars' => new external_value(PARAM_TEXT, 'Start chars'),
            ]
        );
    }

    /**
     * Defines the return structure for the teachers abc web service.
     *
     * @return external_description The return structure.
     */
    public static function teachers_abc_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'firstname' => new external_value(PARAM_TEXT, 'firstname'),
                    'lastname' => new external_value(PARAM_TEXT, 'lastname'),
                    'username' => new external_value(PARAM_TEXT, 'username'),
                ]
            )
        );
    }

    /**
     * Executes the teachers abc web service.
     *
     * @param mixed $startchars The start_chars value.
     * @return mixed The web service result.
     */
    public static function teachers_abc($startchars) {
        $params = self::validate_parameters(self::teachers_abc_parameters(), ['start_chars' => $startchars]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:viewdetails', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->teachers_abc($params['start_chars']);

        return $id;
    }

    /**
     * Defines parameters for the teacher courses web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function teacher_courses_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'Teacher username'),
            ]
        );
    }

    /**
     * Defines the return structure for the teacher courses web service.
     *
     * @return external_description The return structure.
     */
    public static function teacher_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'remoteid' => new external_value(PARAM_INT, 'course id'),
                    'fullname' => new external_value(PARAM_TEXT, 'course name'),
                    'cat_id' => new external_value(PARAM_INT, 'category id'),
                    'cat_name' => new external_value(PARAM_TEXT, 'category name'),
                ]
            )
        );
    }

    /**
     * Executes the teacher courses web service.
     *
     * @param mixed $username The username value.
     * @return mixed The web service result.
     */
    public static function teacher_courses($username) {
        $params = self::validate_parameters(self::teacher_courses_parameters(), ['username' => $username]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/role:review', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->teacher_courses($params['username']);

        return $id;
    }

    /**
     * Defines parameters for the user custom fields web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function user_custom_fields_parameters() {
        return new external_function_parameters(
            []
        );
    }

    /**
     * Defines the return structure for the user custom fields web service.
     *
     * @return external_description The return structure.
     */
    public static function user_custom_fields_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'id' => new external_value(PARAM_INT, 'field id'),
                    'name' => new external_value(PARAM_TEXT, 'field name'),
                    'shortname' => new external_value(PARAM_TEXT, 'field short name'),
                ]
            )
        );
    }

    /**
     * Executes the user custom fields web service.
     *
     * @return mixed The web service result.
     */
    public static function user_custom_fields() {
        $params = self::validate_parameters(self::user_custom_fields_parameters(), []);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:configview', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->user_custom_fields();

        return $id;
    }

    /**
     * Defines parameters for the course enrol methods web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function course_enrol_methods_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'course id'),
            ]
        );
    }

    /**
     * Defines the return structure for the course enrol methods web service.
     *
     * @return external_description The return structure.
     */
    public static function course_enrol_methods_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'id' => new external_value(PARAM_INT, 'enrol method id'),
                    'enrol' => new external_value(PARAM_TEXT, 'enrol method name'),
                    'enrolstartdate' => new external_value(PARAM_INT, 'enrol start date', VALUE_OPTIONAL),
                    'enrolenddate' => new external_value(PARAM_INT, 'enrol end date', VALUE_OPTIONAL),
                ]
            )
        );
    }

    /**
     * Executes the course enrol methods web service.
     *
     * @param mixed $id The id value.
     * @return mixed The web service result.
     */
    public static function course_enrol_methods($id) {
        global $DB;

        $params = self::validate_parameters(self::course_enrol_methods_parameters(), ['id' => $id]);

        self::validate_context(context_system::instance());
        $course = $DB->get_record('course', ['id' => $params['id']], '*', MUST_EXIST);
        if (!core_course_category::can_view_course_info($course) && !can_access_course($course)) {
            throw new moodle_exception('coursehidden');
        }

        require_capability('moodle/course:enrolreview', context_system::instance());

        $auth = new  auth_plugin_joomdle();
        $methods = $auth->course_enrol_methods($params['id']);

        return $methods;
    }

    /**
     * Defines parameters for the get course students web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_course_students_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'course id'),
                'active' => new external_value(PARAM_INT, 'active'),
            ]
        );
    }

    /**
     * Defines the return structure for the get course students web service.
     *
     * @return external_description The return structure.
     */
    public static function get_course_students_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'firstname' => new external_value(PARAM_TEXT, 'firstname'),
                    'lastname' => new external_value(PARAM_TEXT, 'lastname'),
                    'username' => new external_value(PARAM_TEXT, 'username'),
                    'email' => new external_value(PARAM_TEXT, 'email'),
                    'id' => new external_value(PARAM_INT, 'user id'),
                ]
            )
        );
    }

    /**
     * Executes the get course students web service.
     *
     * @param mixed $id The id value.
     * @param mixed $active The active value.
     * @return mixed The web service result.
     */
    public static function get_course_students($id, $active) {
        global $DB;

        $params = self::validate_parameters(
            self::get_course_students_parameters(),
            ['id' => $id, 'active' => $active]
        );

        $DB->get_record('course', ['id' => $params['id']], '*', MUST_EXIST);
        $context = context_course::instance($params['id']);
        self::validate_context($context);
        course_require_view_participants($context);
        require_capability('moodle/course:enrolreview', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_course_students($params['id'], "", $params['active']);

        return $return;
    }

    /**
     * Defines parameters for the multiple suspend enrolment web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function multiple_suspend_enrolment_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
                'courses' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(PARAM_INT, 'course id'),
                        ]
                    )
                ),
            ]
        );
    }

    /**
     * Defines the return structure for the multiple suspend enrolment web service.
     *
     * @return external_description The return structure.
     */
    public static function multiple_suspend_enrolment_returns() {
        return new  external_value(PARAM_INT, 'user enroled');
    }

    /**
     * Executes the multiple suspend enrolment web service.
     *
     * @param mixed $username The username value.
     * @param mixed $courses The courses value.
     * @return mixed The web service result.
     */
    public static function multiple_suspend_enrolment($username, $courses) {
        global $DB;

        $params = self::validate_parameters(
            self::multiple_suspend_enrolment_parameters(),
            ['username' => $username, 'courses' => $courses]
        );

        foreach ($params['courses'] as $course) {
            $DB->get_record('course', ['id' => $course['id']], '*', MUST_EXIST);
            $context = context_course::instance($course['id']);
            self::validate_context($context);
            require_capability('enrol/manual:manage', $context);
        }

        $auth = new  auth_plugin_joomdle();
        $id = $auth->multiple_suspend_enrolment($params['username'], $params['courses']);

        return $id;
    }

    /**
     * Defines parameters for the suspend enrolment web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function suspend_enrolment_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
                'id' => new external_value(PARAM_INT, 'course id'),
            ]
        );
    }

    /**
     * Defines the return structure for the suspend enrolment web service.
     *
     * @return external_description The return structure.
     */
    public static function suspend_enrolment_returns() {
        return new  external_value(PARAM_INT, 'user created');
    }

    /**
     * Executes the suspend enrolment web service.
     *
     * @param mixed $username The username value.
     * @param mixed $id The id value.
     * @return mixed The web service result.
     */
    public static function suspend_enrolment($username, $id) {
        global $DB;

        $params = self::validate_parameters(
            self::suspend_enrolment_parameters(),
            ['username' => $username, 'id' => $id]
        );

        $DB->get_record('course', ['id' => $params['id']], '*', MUST_EXIST);
        $context = context_course::instance($params['id']);
        self::validate_context($context);
        require_capability('enrol/manual:manage', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->suspend_enrolment($params['username'], $params['id']);

        return $id;
    }

    /**
     * Defines parameters for the my certificates web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function my_certificates_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
                'type' => new external_value(PARAM_TEXT, 'type'),
            ]
        );
    }

    /**
     * Defines the return structure for the my certificates web service.
     *
     * @return external_description The return structure.
     */
    public static function my_certificates_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'name' => new external_value(PARAM_TEXT, 'name'),
                    'id' => new external_value(PARAM_INT, 'id'),
                    'code' => new external_value(PARAM_TEXT, 'code', VALUE_OPTIONAL),
                ]
            )
        );
    }

    /**
     * Executes the my certificates web service.
     *
     * @param mixed $username The username value.
     * @param mixed $type The type value.
     * @return mixed The web service result.
     */
    public static function my_certificates($username, $type) {
        $params = self::validate_parameters(
            self::my_certificates_parameters(),
            ['username' => $username, 'type' => $type]
        );

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/grade:viewall', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->my_certificates($params['username'], $params['type']);

        return $return;
    }

    /**
     * Defines parameters for the get my grades web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_my_grades_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
            ]
        );
    }

    /**
     * Defines the return structure for the get my grades web service.
     *
     * @return external_description The return structure.
     */
    public static function get_my_grades_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'fullname' => new external_value(PARAM_TEXT, 'fullname'),
                    'remoteid' => new external_value(PARAM_INT, 'course id'),
                    'grades' => new external_multiple_structure(
                        new external_single_structure(
                            [
                                'itemname' => new external_value(PARAM_TEXT, 'item name'),
                                'finalgrade' => new external_value(PARAM_TEXT, 'final grade'),
                            ]
                        )
                    ),

                ]
            )
        );
    }

    /**
     * Executes the get my grades web service.
     *
     * @param mixed $username The username value.
     * @return mixed The web service result.
     */
    public static function get_my_grades($username) {
        $params = self::validate_parameters(self::get_my_grades_parameters(), ['username' => $username]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/grade:viewall', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_my_grades($params['username']);

        return $return;
    }

    /**
     * Defines parameters for the get cohorts web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_cohorts_parameters() {
        return new external_function_parameters(
            []
        );
    }

    /**
     * Defines the return structure for the get cohorts web service.
     *
     * @return external_description The return structure.
     */
    public static function get_cohorts_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'id' => new external_value(PARAM_INT, 'cohort id'),
                    'name' => new external_value(PARAM_TEXT, 'name'),
                ]
            )
        );
    }

    /**
     * Executes the get cohorts web service.
     *
     * @return mixed The web service result.
     */
    public static function get_cohorts() {
        $params = self::validate_parameters(self::get_cohorts_parameters(), []);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/cohort:manage', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_cohorts();

        return $return;
    }

    /**
     * Defines parameters for the add cohort member web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function add_cohort_member_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
                'cohort_id' => new external_value(PARAM_INT, 'cohort id'),
            ]
        );
    }

    /**
     * Defines the return structure for the add cohort member web service.
     *
     * @return external_description The return structure.
     */
    public static function add_cohort_member_returns() {
        return new  external_value(PARAM_INT, 'user added');
    }

    /**
     * Executes the add cohort member web service.
     *
     * @param mixed $username The username value.
     * @param mixed $cohortid The cohort_id value.
     * @return mixed The web service result.
     */
    public static function add_cohort_member($username, $cohortid) {
        global $DB;

        $params = self::validate_parameters(
            self::add_cohort_member_parameters(),
            ['username' => $username, 'cohort_id' => $cohortid]
        );

        $cohort = $DB->get_record('cohort', ['id' => $params['cohort_id']], '*', MUST_EXIST);
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

    /**
     * Defines parameters for the get rubrics web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_rubrics_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'id'),
            ]
        );
    }

    /**
     * Defines the return structure for the get rubrics web service.
     *
     * @return external_description The return structure.
     */
    public static function get_rubrics_returns() {
        return
            new external_single_structure(
                [
                    'assign_name' => new external_value(PARAM_TEXT, 'definition name'),
                    'definitions' => new external_multiple_structure(
                        new external_single_structure(
                            [
                                'definition' => new external_value(PARAM_TEXT, 'definition name'),
                                'criteria' => new external_multiple_structure(
                                    new external_single_structure(
                                        [
                                            'description' => new external_value(PARAM_TEXT, 'criterion description'),
                                            'levels' => new external_multiple_structure(
                                                new external_single_structure(
                                                    [
                                                        'definition' => new external_value(
                                                            PARAM_RAW,
                                                            'level definition'
                                                        ),
                                                        'score' => new external_value(
                                                            PARAM_FLOAT,
                                                            'grademax'
                                                        ),
                                                    ]
                                                )
                                            ),
                                        ]
                                    )
                                ),
                            ]
                        )
                    ),
                ]
            );
    }

    /**
     * Executes the get rubrics web service.
     *
     * @param mixed $id The id value.
     * @return mixed The web service result.
     */
    public static function get_rubrics($id) {
        global $DB;

        $params = self::validate_parameters(self::get_rubrics_parameters(), ['id' => $id]);

        $gradeitem = $DB->get_record('grade_items', ['id' => $params['id']], '*', MUST_EXIST);
        $context = context_course::instance($gradeitem->courseid);
        self::validate_context($context);
        require_capability('moodle/grade:managegradingforms', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_rubrics($params['id']);

        return $return;
    }

    /**
     * Defines parameters for the get grade user report web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_grade_user_report_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'course id'),
                'username' => new external_value(PARAM_TEXT, 'username'),
            ]
        );
    }

    /**
     * Defines the return structure for the get grade user report web service.
     *
     * @return external_description The return structure.
     */
    public static function get_grade_user_report_returns() {
        return
            new external_single_structure(
                [
                    'config' => new external_single_structure(
                        [
                            'showlettergrade' => new external_value(PARAM_INT, 'showlettergrade'),
                        ]
                    ),
                    'data' => new external_multiple_structure(
                        new external_single_structure(
                            [
                                'fullname' => new external_value(PARAM_TEXT, 'item name'),
                                'grademin' => new external_value(PARAM_FLOAT, 'grademin', VALUE_OPTIONAL),
                                'grademax' => new external_value(PARAM_FLOAT, 'grademax'),
                                'finalgrade' => new external_value(PARAM_FLOAT, 'final grade'),
                                'letter' => new external_value(PARAM_TEXT, 'grade letter'),
                                'items' => new external_multiple_structure(
                                    new external_single_structure(
                                        [
                                            'name' => new external_value(PARAM_TEXT, 'item name'),
                                            'due' => new external_value(PARAM_INT, 'due date'),
                                            'grademax' => new external_value(PARAM_FLOAT, 'grademax'),
                                            'finalgrade' => new external_value(PARAM_TEXT, 'final grade'),
                                            'feedback' => new external_value(PARAM_RAW, 'feedback'),
                                            'letter' => new external_value(PARAM_TEXT, 'grade letter'),
                                            'module' => new external_value(PARAM_TEXT, 'module'),
                                            'iteminstance' => new external_value(PARAM_INT, 'item instance'),
                                            'course_module_id' => new external_value(PARAM_INT, 'course module id'),
                                        ]
                                    )
                                ),
                            ]
                        )
                    ),
                ]
            );
    }

    /**
     * Executes the get grade user report web service.
     *
     * @param mixed $id The id value.
     * @param mixed $username The username value.
     * @return mixed The web service result.
     */
    public static function get_grade_user_report($id, $username) {
        global $DB;

        $params = self::validate_parameters(
            self::get_grade_user_report_parameters(),
            ['id' => $id, 'username' => $username]
        );

        $DB->get_record('course', ['id' => $params['id']], '*', MUST_EXIST);
        $context = context_course::instance($params['id']);
        self::validate_context($context);
        require_capability('moodle/grade:viewall', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_grade_user_report($params['id'], $params['username']);

        return $return;
    }


    /**
     * Defines parameters for the get my grade user report web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_my_grade_user_report_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
            ]
        );
    }

    /**
     * Defines the return structure for the get my grade user report web service.
     *
     * @return external_description The return structure.
     */
    public static function get_my_grade_user_report_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'fullname' => new external_value(PARAM_TEXT, 'fullname'),
                    'remoteid' => new external_value(PARAM_INT, 'course id'),
                    'grades' => new external_single_structure(
                        [
                            'config' => new external_single_structure(
                                [
                                    'showlettergrade' => new external_value(PARAM_INT, 'showlettergrade'),
                                ]
                            ),
                            'data' => new external_multiple_structure(
                                new external_single_structure(
                                    [
                                        'fullname' => new external_value(PARAM_TEXT, 'item name'),
                                        'grademax' => new external_value(PARAM_FLOAT, 'grademax'),
                                        'finalgrade' => new external_value(PARAM_FLOAT, 'final grade'),
                                        'letter' => new external_value(PARAM_TEXT, 'grade letter'),
                                        'items' => new external_multiple_structure(
                                            new external_single_structure(
                                                [
                                                    'name' => new external_value(PARAM_TEXT, 'item name'),
                                                    'due' => new external_value(PARAM_INT, 'due date'),
                                                    'grademax' => new external_value(PARAM_FLOAT, 'grademax'),
                                                    'finalgrade' => new external_value(PARAM_TEXT, 'final grade'),
                                                    'feedback' => new external_value(PARAM_RAW, 'feedback'),
                                                    'letter' => new external_value(PARAM_TEXT, 'grade letter'),
                                                    'module' => new external_value(PARAM_TEXT, 'module'),
                                                    'iteminstance' => new external_value(PARAM_INT, 'item instance'),
                                                    'course_module_id' => new external_value(PARAM_INT, 'course module id'),
                                                ]
                                            )
                                        ),
                                    ]
                                )
                            ),
                        ]
                    ),
                ]
            )
        );
    }

    /**
     * Executes the get my grade user report web service.
     *
     * @param mixed $username The username value.
     * @return mixed The web service result.
     */
    public static function get_my_grade_user_report($username) {
        $params = self::validate_parameters(self::get_my_grade_user_report_parameters(), ['username' => $username]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/grade:viewall', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_my_grade_user_report($params['username']);

        return $return;
    }

    /**
     * Defines parameters for the get group members web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_group_members_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'group id'),
                'search' => new external_value(PARAM_TEXT, 'search'),
            ]
        );
    }

    /**
     * Defines the return structure for the get group members web service.
     *
     * @return external_description The return structure.
     */
    public static function get_group_members_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'id' => new external_value(PARAM_INT, 'record id'),
                    'firstname' => new external_value(PARAM_TEXT, 'first name'),
                    'lastname' => new external_value(PARAM_TEXT, 'last name'),
                    'username' => new external_value(PARAM_TEXT, 'username'),
                ]
            )
        );
    }

    /**
     * Executes the get group members web service.
     *
     * @param mixed $id The id value.
     * @param mixed $search The search value.
     * @return mixed The web service result.
     */
    public static function get_group_members($id, $search) {
        global $DB;

        $params = self::validate_parameters(self::get_group_members_parameters(), ['id' => $id, 'search' => $search]);

        $group = $DB->get_record('groups', ['id' => $params['id']], '*', MUST_EXIST);
        $context = context_course::instance($group->courseid);
        self::validate_context($context);
        require_capability('moodle/course:managegroups', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_group_members($params['id'], $params['search']);

        return $return;
    }

    /**
     * Defines parameters for the get course groups web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_course_groups_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'course id'),
            ]
        );
    }

    /**
     * Defines the return structure for the get course groups web service.
     *
     * @return external_description The return structure.
     */
    public static function get_course_groups_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'id' => new external_value(PARAM_INT, 'group record id'),
                    'name' => new external_value(PARAM_TEXT, 'group name'),
                    'description' => new external_value(PARAM_RAW, 'description'),
                ]
            )
        );
    }

    /**
     * Executes the get course groups web service.
     *
     * @param mixed $id The id value.
     * @return mixed The web service result.
     */
    public static function get_course_groups($id) {
        global $DB;

        $params = self::validate_parameters(self::get_course_groups_parameters(), ['id' => $id]);

        $DB->get_record('course', ['id' => $params['id']], '*', MUST_EXIST);
        $context = context_course::instance($params['id']);
        self::validate_context($context);
        require_capability('moodle/course:managegroups', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_course_groups($params['id']);

        return $return;
    }

    /**
     * Defines parameters for the remove cohort member web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function remove_cohort_member_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
                'cohort_id' => new external_value(PARAM_INT, 'cohort id'),
            ]
        );
    }

    /**
     * Defines the return structure for the remove cohort member web service.
     *
     * @return external_description The return structure.
     */
    public static function remove_cohort_member_returns() {
        return new  external_value(PARAM_INT, 'user added');
    }

    /**
     * Executes the remove cohort member web service.
     *
     * @param mixed $username The username value.
     * @param mixed $cohortid The cohort_id value.
     * @return mixed The web service result.
     */
    public static function remove_cohort_member($username, $cohortid) {
        global $DB;

        $params = self::validate_parameters(
            self::remove_cohort_member_parameters(),
            ['username' => $username, 'cohort_id' => $cohortid]
        );

        $DB->get_record('user', ['username' => strtolower($params['username'])], '*', MUST_EXIST);
        $cohort = $DB->get_record('cohort', ['id' => $params['cohort_id']], '*', MUST_EXIST);
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

    /**
     * Defines parameters for the multiple add cohort member web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function multiple_add_cohort_member_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
                'cohorts' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(PARAM_INT, 'cohort id'),
                        ]
                    )
                ),
            ]
        );
    }

    /**
     * Defines the return structure for the multiple add cohort member web service.
     *
     * @return external_description The return structure.
     */
    public static function multiple_add_cohort_member_returns() {
        return new  external_value(PARAM_INT, 'user enroled');
    }

    /**
     * Executes the multiple add cohort member web service.
     *
     * @param mixed $username The username value.
     * @param mixed $cohorts The cohorts value.
     * @return mixed The web service result.
     */
    public static function multiple_add_cohort_member($username, $cohorts) {
        global $DB;

        $params = self::validate_parameters(
            self::multiple_add_cohort_member_parameters(),
            ['username' => $username, 'cohorts' => $cohorts]
        );

        $DB->get_record('user', ['username' => strtolower($params['username'])], '*', MUST_EXIST);
        foreach ($params['cohorts'] as $cohortdata) {
            $cohort = $DB->get_record('cohort', ['id' => $cohortdata['id']], '*', MUST_EXIST);
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

    /**
     * Defines parameters for the multiple remove cohort member web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function multiple_remove_cohort_member_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
                'cohorts' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(PARAM_INT, 'cohort id'),
                        ]
                    )
                ),
            ]
        );
    }

    /**
     * Defines the return structure for the multiple remove cohort member web service.
     *
     * @return external_description The return structure.
     */
    public static function multiple_remove_cohort_member_returns() {
        return new  external_value(PARAM_INT, 'user enroled');
    }

    /**
     * Executes the multiple remove cohort member web service.
     *
     * @param mixed $username The username value.
     * @param mixed $cohorts The cohorts value.
     * @return mixed The web service result.
     */
    public static function multiple_remove_cohort_member($username, $cohorts) {
        global $DB;

        $params = self::validate_parameters(
            self::multiple_remove_cohort_member_parameters(),
            ['username' => $username, 'cohorts' => $cohorts]
        );

        $DB->get_record('user', ['username' => strtolower($params['username'])], '*', MUST_EXIST);
        foreach ($params['cohorts'] as $cohortdata) {
            $cohort = $DB->get_record('cohort', ['id' => $cohortdata['id']], '*', MUST_EXIST);
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

    /**
     * Defines parameters for the get courses and groups web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_courses_and_groups_parameters() {
        return new external_function_parameters(
            []
        );
    }

    /**
     * Defines the return structure for the get courses and groups web service.
     *
     * @return external_description The return structure.
     */
    public static function get_courses_and_groups_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'remoteid' => new external_value(PARAM_INT, 'course id'),
                    'fullname' => new external_value(PARAM_TEXT, 'course name'),
                    'groups' => new external_multiple_structure(
                        new external_single_structure(
                            [
                                'id' => new external_value(PARAM_INT, 'group record id'),
                                'name' => new external_value(PARAM_TEXT, 'group name'),
                                'description' => new external_value(PARAM_RAW, 'description'),
                            ]
                        )
                    ),
                ]
            )
        );
    }

    /**
     * Executes the get courses and groups web service.
     *
     * @return mixed The web service result.
     */
    public static function get_courses_and_groups() {
        $params = self::validate_parameters(self::get_courses_and_groups_parameters(), []);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/category:viewcourselist', $context);
        require_capability('moodle/course:managegroups', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->get_courses_and_groups();

        return $id;
    }

    /**
     * Defines parameters for the multiple enrol to course and group web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function multiple_enrol_to_course_and_group_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
                'courses' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(PARAM_INT, 'course id'),
                            'group_id' => new external_value(PARAM_INT, 'group id'),
                        ]
                    )
                ),
                'roleid' => new external_value(PARAM_INT, 'role id'),
            ]
        );
    }

    /**
     * Defines the return structure for the multiple enrol to course and group web service.
     *
     * @return external_description The return structure.
     */
    public static function multiple_enrol_to_course_and_group_returns() {
        return new  external_value(PARAM_INT, 'user enroled');
    }

    /**
     * Executes the multiple enrol to course and group web service.
     *
     * @param mixed $username The username value.
     * @param mixed $courses The courses value.
     * @param mixed $roleid The roleid value.
     * @return mixed The web service result.
     */
    public static function multiple_enrol_to_course_and_group($username, $courses, $roleid) {
        global $DB;

        $params = self::validate_parameters(
            self::multiple_enrol_to_course_and_group_parameters(),
            ['username' => $username, 'courses' => $courses, 'roleid' => $roleid]
        );

        $DB->get_record('user', ['username' => core_text::strtolower($params['username'])], '*', MUST_EXIST);
        foreach ($params['courses'] as $course) {
            $DB->get_record('course', ['id' => $course['id']], '*', MUST_EXIST);
            $DB->get_record('groups', ['id' => $course['group_id'], 'courseid' => $course['id']], '*', MUST_EXIST);
            $context = context_course::instance($course['id']);
            self::validate_context($context);
            require_capability('enrol/manual:enrol', $context);
            require_capability('moodle/course:managegroups', $context);

            self::resolve_assignable_enrol_role($course['id'], $params['roleid'], $context);
        }

        $auth = new  auth_plugin_joomdle();
        $id = $auth->multiple_enrol_to_course_and_group(
            $params['username'],
            $params['courses'],
            $params['roleid']
        );

        return $id;
    }

    /**
     * Defines parameters for the multiple remove from group web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function multiple_remove_from_group_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
                'courses' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(PARAM_INT, 'course id'),
                            'group_id' => new external_value(PARAM_INT, 'group id'),
                        ]
                    )
                ),
            ]
        );
    }

    /**
     * Defines the return structure for the multiple remove from group web service.
     *
     * @return external_description The return structure.
     */
    public static function multiple_remove_from_group_returns() {
        return new  external_value(PARAM_INT, 'user enroled');
    }

    /**
     * Executes the multiple remove from group web service.
     *
     * @param mixed $username The username value.
     * @param mixed $courses The courses value.
     * @return mixed The web service result.
     */
    public static function multiple_remove_from_group($username, $courses) {
        global $DB;

        $params = self::validate_parameters(
            self::multiple_remove_from_group_parameters(),
            ['username' => $username, 'courses' => $courses]
        );

        $DB->get_record('user', ['username' => core_text::strtolower($params['username'])], '*', MUST_EXIST);
        foreach ($params['courses'] as $course) {
            $DB->get_record('course', ['id' => $course['id']], '*', MUST_EXIST);
            $DB->get_record('groups', ['id' => $course['group_id'], 'courseid' => $course['id']], '*', MUST_EXIST);
            $context = context_course::instance($course['id']);
            self::validate_context($context);
            require_capability('moodle/course:managegroups', $context);
        }

        $auth = new  auth_plugin_joomdle();
        $id = $auth->multiple_remove_from_group($params['username'], $params['courses']);

        return $id;
    }

    /**
     * Defines parameters for the multiple unenrol user web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function multiple_unenrol_user_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
                'courses' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(PARAM_INT, 'course id'),
                        ]
                    )
                ),
            ]
        );
    }

    /**
     * Defines the return structure for the multiple unenrol user web service.
     *
     * @return external_description The return structure.
     */
    public static function multiple_unenrol_user_returns() {
        return new  external_value(PARAM_INT, 'user enroled');
    }

    /**
     * Executes the multiple unenrol user web service.
     *
     * @param mixed $username The username value.
     * @param mixed $courses The courses value.
     * @return mixed The web service result.
     */
    public static function multiple_unenrol_user($username, $courses) {
        global $DB;

        $params = self::validate_parameters(
            self::multiple_unenrol_user_parameters(),
            ['username' => $username, 'courses' => $courses]
        );

        $DB->get_record('user', ['username' => core_text::strtolower($params['username'])], '*', MUST_EXIST);
        foreach ($params['courses'] as $course) {
            $DB->get_record('course', ['id' => $course['id']], '*', MUST_EXIST);
            $context = context_course::instance($course['id']);
            self::validate_context($context);
            require_capability('enrol/manual:unenrol', $context);
        }

        $auth = new  auth_plugin_joomdle();
        $id = $auth->multiple_unenrol_user($params['username'], $params['courses']);

        return $id;
    }

    /**
     * Defines parameters for the unenrol user web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function unenrol_user_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
                'id' => new external_value(PARAM_INT, 'course id'),
            ]
        );
    }

    /**
     * Defines the return structure for the unenrol user web service.
     *
     * @return external_description The return structure.
     */
    public static function unenrol_user_returns() {
        return new  external_value(PARAM_INT, 'user created');
    }

    /**
     * Executes the unenrol user web service.
     *
     * @param mixed $username The username value.
     * @param mixed $id The id value.
     * @return mixed The web service result.
     */
    public static function unenrol_user($username, $id) {
        global $DB;

        $params = self::validate_parameters(self::unenrol_user_parameters(), ['username' => $username, 'id' => $id]);

        $DB->get_record('user', ['username' => core_text::strtolower($params['username'])], '*', MUST_EXIST);
        $DB->get_record('course', ['id' => $params['id']], '*', MUST_EXIST);
        $context = context_course::instance($params['id']);
        self::validate_context($context);
        require_capability('enrol/manual:unenrol', $context);

        $auth = new  auth_plugin_joomdle();
        $id = $auth->unenrol_user($params['username'], $params['id']);

        return $id;
    }

    /**
     * Defines parameters for the get themes web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_themes_parameters() {
        return new external_function_parameters(
            []
        );
    }

    /**
     * Defines the return structure for the get themes web service.
     *
     * @return external_description The return structure.
     */
    public static function get_themes_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'name' => new external_value(PARAM_TEXT, 'name'),
                ]
            )
        );
    }

    /**
     * Executes the get themes web service.
     *
     * @return mixed The web service result.
     */
    public static function get_themes() {
        $params = self::validate_parameters(self::get_themes_parameters(), []);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:configview', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_themes();

        return $return;
    }

    /**
     * Defines parameters for the enrol user with start and end date web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function enrol_user_with_start_and_end_date_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
                'id' => new external_value(PARAM_INT, 'course id'),
                'roleid' => new external_value(PARAM_INT, 'role id'),
                'start_date' => new external_value(PARAM_INT, 'start_date'),
                'end_date' => new external_value(PARAM_INT, 'end_date'),
            ]
        );
    }

    /**
     * Defines the return structure for the enrol user with start and end date web service.
     *
     * @return external_description The return structure.
     */
    public static function enrol_user_with_start_and_end_date_returns() {
        return new  external_value(PARAM_INT, 'user created');
    }

    /**
     * Executes the enrol user with start and end date web service.
     *
     * @param mixed $username The username value.
     * @param mixed $id The id value.
     * @param mixed $roleid The roleid value.
     * @param mixed $startdate The start_date value.
     * @param mixed $enddate The end_date value.
     * @return mixed The web service result.
     */
    public static function enrol_user_with_start_and_end_date($username, $id, $roleid, $startdate, $enddate) {
        global $DB;

        $params = self::validate_parameters(
            self::enrol_user_with_start_and_end_date_parameters(),
            [
                'username' => $username,
                'id' => $id,
                'roleid' => $roleid,
                'start_date' => $startdate,
                'end_date' => $enddate,
            ]
        );

        $DB->get_record('course', ['id' => $params['id']], '*', MUST_EXIST);
        $context = context_course::instance($params['id']);
        self::validate_context($context);
        require_capability('enrol/manual:enrol', $context);

        $roleid = self::resolve_assignable_enrol_role($params['id'], $params['roleid'], $context);

        $user = $DB->get_record('user', ['username' => core_text::strtolower($params['username'])]);
        if (!$user) {
            $systemcontext = context_system::instance();
            self::validate_context($systemcontext);
            require_capability('moodle/user:create', $systemcontext);
        }

        $auth = new  auth_plugin_joomdle();
        $id = $auth->enrol_user(
            $params['username'],
            $params['id'],
            $roleid,
            $params['start_date'],
            $params['end_date']
        );

        return $id;
    }

    /**
     * Defines parameters for the my badges web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function my_badges_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'Username'),
                'max' => new external_value(PARAM_INT, 'max to return'),
            ]
        );
    }

    /**
     * Defines the return structure for the my badges web service.
     *
     * @return external_description The return structure.
     */
    public static function my_badges_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'name' => new external_value(PARAM_TEXT, 'badge name'),
                    'hash' => new external_value(PARAM_TEXT, 'unique hash'),
                    'image_url' => new external_value(PARAM_TEXT, 'image url'),
                ]
            )
        );
    }

    /**
     * Executes the my badges web service.
     *
     * @param mixed $username The username value.
     * @param mixed $max The max value.
     * @return mixed The web service result.
     */
    public static function my_badges($username, $max) {
        global $DB, $USER;

        $params = self::validate_parameters(self::my_badges_parameters(), ['username' => $username, 'max' => $max]);

        $user = $DB->get_record(
            'user',
            ['username' => core_text::strtolower($params['username'])],
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

    /**
     * Defines parameters for the get events web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_events_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'Username'),
                'start_date' => new external_value(PARAM_INT, 'time start'),
                'end_date' => new external_value(PARAM_INT, 'time end'),
                'type' => new external_value(PARAM_TEXT, 'event type'),
                'course_id' => new external_value(PARAM_INT, 'course_id'),
            ]
        );
    }

    /**
     * Defines the return structure for the get events web service.
     *
     * @return external_description The return structure.
     */
    public static function get_events_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'id' => new external_value(PARAM_INT, 'id'),
                    'name' => new external_value(PARAM_TEXT, 'name'),
                    'description' => new external_value(PARAM_RAW, 'description'),
                    'timestart' => new external_value(PARAM_INT, 'timestart'),
                    'timeduration' => new external_value(PARAM_INT, 'timeduration'),
                ]
            )
        );
    }

    /**
     * Executes the get events web service.
     *
     * @param mixed $username The username value.
     * @param mixed $startdate The start_date value.
     * @param mixed $enddate The end_date value.
     * @param mixed $type The type value.
     * @param mixed $courseid The course_id value.
     * @return mixed The web service result.
     */
    public static function get_events($username, $startdate, $enddate, $type, $courseid) {
        global $DB;

        $params = self::validate_parameters(
            self::get_events_parameters(),
            [
                'username' => $username,
                'start_date' => $startdate,
                'end_date' => $enddate,
                'type' => $type,
                'course_id' => $courseid,
            ]
        );

        $DB->get_record('user', ['username' => core_text::strtolower($params['username'])], '*', MUST_EXIST);
        if ($params['course_id']) {
            $DB->get_record('course', ['id' => $params['course_id']], '*', MUST_EXIST);
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

    /**
     * Defines parameters for the get event web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_event_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'id'),
            ]
        );
    }

    /**
     * Defines the return structure for the get event web service.
     *
     * @return external_description The return structure.
     */
    public static function get_event_returns() {
        return new external_single_structure(
            [
                'id' => new external_value(PARAM_INT, 'id'),
                'name' => new external_value(PARAM_TEXT, 'name'),
                'description' => new external_value(PARAM_RAW, 'description'),
                'timestart' => new external_value(PARAM_INT, 'timestart'),
                'timeduration' => new external_value(PARAM_INT, 'timeduration'),
                'type' => new external_value(PARAM_TEXT, 'event type'),
            ]
        );
    }

    /**
     * Executes the get event web service.
     *
     * @param mixed $id The id value.
     * @return mixed The web service result.
     */
    public static function get_event($id) {
        global $DB;

        $params = self::validate_parameters(self::get_event_parameters(), ['id' => $id]);

        $DB->get_record('event', ['id' => $params['id']], '*', MUST_EXIST);
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/calendar:manageentries', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_event($params['id']);

        return $return;
    }

    /**
     * Defines parameters for the my completed courses web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function my_completed_courses_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
                'order_by_cat' => new external_value(PARAM_INT, 'order by category'),
            ]
        );
    }

    /**
     * Defines the return structure for the my completed courses web service.
     *
     * @return external_description The return structure.
     */
    public static function my_completed_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'id' => new external_value(PARAM_INT, 'course id'),
                    'timecompleted' => new external_value(PARAM_INT, 'time completed'),
                    'fullname' => new external_value(PARAM_TEXT, 'course name'),
                    'summary' => new external_value(PARAM_RAW, 'summary'),
                    'category' => new external_value(PARAM_INT, 'course category id'),
                    'cat_name' => new external_value(PARAM_TEXT, 'course category name'),
                    'can_unenrol' => new external_value(PARAM_INT, 'user can self unenrol'),
                    'summary_files' => new external_multiple_structure(
                        new external_single_structure(
                            [
                                'url' => new external_value(PARAM_TEXT, 'item url'),
                            ]
                        )
                    ),
                ]
            )
        );
    }

    /**
     * Executes the my completed courses web service.
     *
     * @param mixed $username The username value.
     * @param mixed $orderbycat The order_by_cat value.
     * @return mixed The web service result.
     */
    public static function my_completed_courses($username, $orderbycat) {
        global $DB;

        $params = self::validate_parameters(self::my_completed_courses_parameters(), [
            'username' => $username,
            'order_by_cat' => $orderbycat,
        ]);

        $DB->get_record('user', ['username' => core_text::strtolower($params['username'])], '*', MUST_EXIST);
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('report/completion:view', $context);

        $auth = new  auth_plugin_joomdle();
        $courses = $auth->my_completed_courses($params['username'], $params['order_by_cat']);

        return $courses;
    }

    /**
     * Defines parameters for the get completed course users web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_completed_course_users_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'course id'),
            ]
        );
    }

    /**
     * Defines the return structure for the get completed course users web service.
     *
     * @return external_description The return structure.
     */
    public static function get_completed_course_users_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'firstname' => new external_value(PARAM_TEXT, 'firstname'),
                    'lastname' => new external_value(PARAM_TEXT, 'lastname'),
                    'username' => new external_value(PARAM_TEXT, 'username'),
                    'email' => new external_value(PARAM_TEXT, 'email'),
                    'timecompleted' => new external_value(PARAM_INT, 'time completed'),
                ]
            )
        );
    }

    /**
     * Executes the get completed course users web service.
     *
     * @param mixed $id The id value.
     * @return mixed The web service result.
     */
    public static function get_completed_course_users($id) {
        $params = self::validate_parameters(self::get_completed_course_users_parameters(), ['id' => $id]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('report/completion:view', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_completed_course_users($id);

        return $return;
    }

    /**
     * Defines parameters for the change username web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function change_username_parameters() {
        return new external_function_parameters(
            [
                'old_username' => new external_value(PARAM_TEXT, 'old username'),
                'new_username' => new external_value(PARAM_TEXT, 'new username'),
            ]
        );
    }

    /**
     * Defines the return structure for the change username web service.
     *
     * @return external_description The return structure.
     */
    public static function change_username_returns() {
        return new  external_value(PARAM_BOOL, 'username changed');
    }

    /**
     * Executes the change username web service.
     *
     * @param mixed $oldusername The old_username value.
     * @param mixed $newusername The new_username value.
     * @return mixed The web service result.
     */
    public static function change_username($oldusername, $newusername) {
        global $DB;

        $params = self::validate_parameters(self::change_username_parameters(), [
            'old_username' => $oldusername,
            'new_username' => $newusername,
        ]);

        $user = $DB->get_record('user', ['username' => core_text::strtolower($params['old_username'])], '*', MUST_EXIST);
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:update', $context);

        if (!$user) {
            return false;
        }
        if (is_siteadmin($user)) {
            return false;
        }

        $auth = new  auth_plugin_joomdle();
        $id = $auth->change_username($params['old_username'], $params['new_username']);

        return $id;
    }

    /**
     * Defines parameters for the get moodle version web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_moodle_version_parameters() {
        return new external_function_parameters(
            []
        );
    }

    /**
     * Defines the return structure for the get moodle version web service.
     *
     * @return external_description The return structure.
     */
    public static function get_moodle_version_returns() {
        return new  external_value(PARAM_INT, 'Moodle version');
    }

    /**
     * Executes the get moodle version web service.
     *
     * @return mixed The web service result.
     */
    public static function get_moodle_version() {
        $params = self::validate_parameters(self::get_moodle_version_parameters(), []);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:configview', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_moodle_version();

        return $return;
    }

    /**
     * Defines parameters for the enable user web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function enable_user_parameters() {
        return new external_function_parameters(
            [
                'username' => new external_value(PARAM_TEXT, 'username'),
                'suspended' => new external_value(PARAM_INT, 'suspend user'),
            ]
        );
    }

    /**
     * Defines the return structure for the enable user web service.
     *
     * @return external_description The return structure.
     */
    public static function enable_user_returns() {
        return new  external_value(PARAM_INT, 'multilang compatible name, course unique');
    }

    /**
     * Executes the enable user web service.
     *
     * @param mixed $username The username value.
     * @param mixed $suspended The suspended value.
     * @return mixed The web service result.
     */
    public static function enable_user($username, $suspended) {
        global $DB;

        $params = self::validate_parameters(self::enable_user_parameters(), ['username' => $username, 'suspended' => $suspended]);

        $user = $DB->get_record('user', ['username' => core_text::strtolower($params['username'])], '*', MUST_EXIST);
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:update', $context);

        if (!$user) {
            return false;
        }
        if (is_siteadmin($user)) {
            return false;
        }

        $auth = new  auth_plugin_joomdle();
        $auth->enable_user($params['username'], $params['suspended']);

        return $params['suspended'];
    }

    /**
     * Defines parameters for the get course users web service.
     *
     * @return external_function_parameters The parameter definition.
     */
    public static function get_course_users_parameters() {
        return new external_function_parameters(
            [
                'course_id' => new external_value(PARAM_INT, 'course id'),
            ]
        );
    }

    /**
     * Defines the return structure for the get course users web service.
     *
     * @return external_description The return structure.
     */
    public static function get_course_users_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'username' => new external_value(PARAM_TEXT, 'username'),
                    'roles' => new external_multiple_structure(
                        new external_single_structure(
                            [
                                'id' => new external_value(PARAM_INT, 'role id'),
                                'name' => new external_value(PARAM_TEXT, 'role name'),
                            ]
                        )
                    ),
                ]
            )
        );
    }

    /**
     * Executes the get course users web service.
     *
     * @param mixed $courseid The course_id value.
     * @return mixed The web service result.
     */
    public static function get_course_users($courseid) {
        global $DB;

        $params = self::validate_parameters(
            self::get_course_users_parameters(),
            ['course_id' => $courseid]
        );

        $DB->get_record('course', ['id' => $params['course_id']], '*', MUST_EXIST);
        $context = context_course::instance($params['course_id']);
        self::validate_context($context);
        course_require_view_participants($context);
        require_capability('moodle/role:review', $context);

        $auth = new  auth_plugin_joomdle();
        $return = $auth->get_course_users($params['course_id']);

        return $return;
    }
}
