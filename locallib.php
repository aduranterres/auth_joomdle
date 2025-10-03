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

require_once($CFG->dirroot . '/auth/joomdle/auth.php');

/**
 * Event handler for joomdle auth plugin.
 */
class auth_joomdle_handler {

    public static function user_created (\core\event\user_created $event) {
        global $CFG, $DB;

        $sync_to_joomla = get_config('auth_joomdle', 'sync_to_joomla');
        $forward_events = get_config('auth_joomdle', 'forward_events');

        if (!$sync_to_joomla) {
            return true;
        }

        $user = $event->get_record_snapshot('user', $event->objectid);

        // Added so that we don't try to sync incomplete users.
        if (!$user->email) {
            return true;
        }

        if ($user->auth != 'joomdle') {
            return true;
        }

        $auth_joomdle = new auth_plugin_joomdle ();

        /* Create user in Joomla */
        $userinfo = array ();
        $userinfo['username'] = $user->username;
        // We can't sync password, because we get in hashed, and hash algo is different in Joomla.
//        $userinfo['password'] = $user->password;
//        $userinfo['password2'] = $user->password;
        $userinfo['name'] = $user->firstname. " " . $user->lastname;
        $userinfo['email'] = $user->email;
        $userinfo['firstname'] = $user->firstname;
        $userinfo['lastname'] = $user->lastname;
        $userinfo['city'] = $user->city;
        $userinfo['country'] = $user->country;
        $userinfo['lang'] = $user->lang;
        $userinfo['timezone'] = $user->timezone;
        $userinfo['phone1'] = $user->phone1;
        $userinfo['phone2'] = $user->phone2;
        $userinfo['address'] = $user->address;
        $userinfo['description'] = $user->description;
        $userinfo['institution'] = $user->institution;
        $userinfo['idnumber'] = $user->idnumber;
        $userinfo['department'] = $user->department;
        $userinfo['picture'] = $user->picture;
        $userinfo['lastnamephonetic'] = $user->lastnamephonetic;
        $userinfo['firstnamephonetic'] = $user->firstnamephonetic;
        $userinfo['middlename'] = $user->middlename;
        $userinfo['alternatename'] = $user->alternatename;
        $userinfo['id'] = $user->id;

        $id = $user->id;
        $usercontext = context_user::instance($id);
        $context_id = $usercontext->id;

        if ($user->picture) {
            $userinfo['pic_url'] = $CFG->wwwroot."/pluginfile.php/$context_id/user/icon/f1";
        }

        $userinfo['block'] = 0;
        $userinfo['confirmed'] = $user->confirmed;

        /* Custom fields */
        $query = "SELECT f.id, d.data
                    FROM {$CFG->prefix}user_info_field as f, {$CFG->prefix}user_info_data d
                    WHERE f.id=d.fieldid and userid = ?";

        $params = array ($id);
        $records = $DB->get_records_sql($query, $params);

        $i = 0;
        $userinfo['custom_fields'] = array ();
        foreach ($records as $field) {
            $userinfo['custom_fields'][$i]['id'] = $field->id;
            $userinfo['custom_fields'][$i]['data'] = $field->data;
            $i++;
        }

        $auth_joomdle->call_method ("createUser", $userinfo);

        // Forward event to Joomla.
        if ($forward_events) {
            $auth_joomdle->call_method ('moodleEvent', 'UserCreated',  $userinfo);
        }

        return true;
    }


    public static function user_updated (\core\event\user_updated $event) {
        global $CFG, $DB;

        $sync_to_joomla = get_config('auth_joomdle', 'sync_to_joomla');
        $forward_events = get_config('auth_joomdle', 'forward_events');

        $auth_joomdle = new auth_plugin_joomdle ();

        // Forward event to Joomla.
        if ($forward_events) {
            $auth_joomdle->call_method ('moodleEvent', 'UserUpdated',  $userinfo);
        }

        if (!$sync_to_joomla) {
            return true;
        }

        $user = $event->get_record_snapshot('user', $event->objectid);

        if ($user->auth != 'joomdle') {
                return true;
        }

        /* Update user info in Joomla */
        $userinfo = array ();
        $userinfo['username'] = $user->username;
        $userinfo['name'] = $user->firstname. " " . $user->lastname;
        $userinfo['email'] = $user->email;
        $userinfo['firstname'] = $user->firstname;
        $userinfo['lastname'] = $user->lastname;
        $userinfo['city'] = $user->city;
        $userinfo['country'] = $user->country;
        $userinfo['lang'] = $user->lang;
        $userinfo['timezone'] = $user->timezone;
        $userinfo['phone1'] = $user->phone1;
        $userinfo['phone2'] = $user->phone2;
        $userinfo['address'] = $user->address;
        $userinfo['description'] = $user->description;
        $userinfo['institution'] = $user->institution;
        $userinfo['idnumber'] = $user->idnumber;
        $userinfo['department'] = $user->department;
        $userinfo['picture'] = $user->picture;
        $userinfo['lastnamephonetic'] = $user->lastnamephonetic;
        $userinfo['firstnamephonetic'] = $user->firstnamephonetic;
        $userinfo['middlename'] = $user->middlename;
        $userinfo['alternatename'] = $user->alternatename;
        $userinfo['id'] = $user->id;

        $id = $user->id;
        $usercontext = context_user::instance($id);
        $context_id = $usercontext->id;

        if ($user->picture) {
            $userinfo['pic_url'] = $CFG->wwwroot."/pluginfile.php/$context_id/user/icon/f1";
        }

        $userinfo['block'] = $user->suspended;
        $userinfo['confirmed'] = $user->confirmed;

        /* Custom fields */
        $query = "SELECT f.id, d.data
                    FROM {$CFG->prefix}user_info_field as f, {$CFG->prefix}user_info_data d
                    WHERE f.id=d.fieldid and userid = ?";

        $params = array ($id);
        $records = $DB->get_records_sql($query, $params);

        $i = 0;
        $userinfo['custom_fields'] = array ();
        foreach ($records as $field) {
            $userinfo['custom_fields'][$i]['id'] = $field->id;
            $userinfo['custom_fields'][$i]['data'] = $field->data;
            $i++;
        }

        $auth_joomdle->call_method ("updateUser", $userinfo);

        return true;
    }

    public static function user_deleted (\core\event\user_deleted $event) {
        global $CFG, $DB;

        $sync_to_joomla = get_config('auth_joomdle', 'sync_to_joomla');
        $forward_events = get_config('auth_joomdle', 'forward_events');

        if (!$sync_to_joomla) {
            return true;
        }

        $user = $event->get_record_snapshot('user', $event->objectid);

        if ($user->auth != 'joomdle') {
            return true;
        }

        $auth_joomdle = new auth_plugin_joomdle ();

        $auth_joomdle->call_method ("deleteUser", $user->username);

        // Forward event to Joomla.
        if ($forward_events) {
            $data = array ();
            $data['username'] = $user->username;
            $auth_joomdle->call_method ('moodleEvent', 'UserDeleted',  $data);
        }

        return true;
    }

    public static function course_created (\core\event\course_created $event) {
        self::new_course_event ($event);
    }

    public static function course_restored (\core\event\course_restored $event) {
        self::new_course_event ($event);
    }

    private static function new_course_event ($event) {
        global $CFG, $DB;

        $forward_events = get_config('auth_joomdle', 'forward_events');

        $course = $event->get_record_snapshot('course', $event->objectid);

        $activities = get_config('auth_joomdle', 'jomsocial_activities');
        $groups = get_config('auth_joomdle', 'jomsocial_groups');
        $joomla_user_groups = get_config('auth_joomdle', 'joomla_user_groups');
        $use_kunena_forums = get_config('auth_joomdle', 'use_kunena_forums');

        $auth_joomdle = new auth_plugin_joomdle ();

        /* kludge for the call_method fn to work */
        if (!$course->summary) {
            $course->summary = ' ';
        }

        $conditions = array ('id' => $course->category);
        $cat = $DB->get_record('course_categories', $conditions);

        $context = context_course::instance($course->id);
        $course->summary = file_rewrite_pluginfile_urls ($course->summary, 'pluginfile.php', $context->id, 'course', 'summary',
                null);
        $course->summary = str_replace ('pluginfile.php', '/auth/joomdle/pluginfile_joomdle.php', $course->summary);
        if ($activities) {
            $auth_joomdle->call_method ('addActivityCourse', (int) $course->id, $course->fullname,  $course->summary,
                    (int) $course->category, $cat->name);
        }

        if ($groups) {
            $auth_joomdle->call_method ('addSocialGroup', $course->fullname,  get_string('auth_joomla_group_for_course',
                        'auth_joomdle') . ' ' . $course->fullname,  (int) $course->id);
        }

        if ($joomla_user_groups) {
            $auth_joomdle->call_method ('addUserGroups', (int) $course->id, $course->fullname);
        }

        if ($use_kunena_forums) {
            // Create section.
            $auth_joomdle->call_method ('addForum', (int) $course->id, (int) -2, $course->fullname);
        }

        // Forward event to Joomla.
        if ($forward_events) {
            $data = array ();
            $data['course_id'] = $course->id;
            $data['course_name'] = $course->fullname;
            $data['summary'] = $course->summary;
            $data['category'] = $course->category;
            $data['category_name'] = $cat->name;
            $data['course_shortname'] = $course->shortname;
            $data['idnumber'] = $course->idnumber;
            $data['startdate'] = $course->startdate;
            $data['enddate'] = $course->enddate;
            $auth_joomdle->call_method ('moodleEvent', 'CourseCreated',  $data);
        }

        return true;
    }

    public static function course_deleted (\core\event\course_deleted $event) {
        $forward_events = get_config('auth_joomdle', 'forward_events');

        $course = $event->get_record_snapshot('course', $event->objectid);

        $groups_delete = get_config('auth_joomdle', 'jomsocial_groups_delete');
        $use_kunena_forums = get_config('auth_joomdle', 'use_kunena_forums');
        $joomla_user_groups = get_config('auth_joomdle', 'joomla_user_groups');

        $auth_joomdle = new auth_plugin_joomdle ();

        if ($groups_delete) {
            $auth_joomdle->call_method ('deleteSocialGroup', $course->id);
        }

        if ($joomla_user_groups) {
            $auth_joomdle->call_method ("removeUserGroups", (int) $course->id);
        }

        if ($use_kunena_forums) {
            $auth_joomdle->call_method ("removeCourseForums", (int) $course->id);
        }

        // Forward event to Joomla.
        if ($forward_events) {
            $data = array ();
            $data['course_name'] = $course->fullname;
            $auth_joomdle->call_method ('moodleEvent', 'CourseDeleted',  $data);
        }

        return true;
    }

    public static function course_updated (\core\event\course_updated $event) {
        global $CFG, $DB;

        $forward_events = get_config('auth_joomdle', 'forward_events');

        $course = $event->get_record_snapshot('course', $event->objectid);

        $groups = get_config('auth_joomdle', 'jomsocial_groups');
        $joomla_user_groups = get_config('auth_joomdle', 'joomla_user_groups');

        $auth_joomdle = new auth_plugin_joomdle ();

        if ($groups) {
            $auth_joomdle->call_method ('updateSocialGroup', $course->fullname,
                    get_string('auth_joomla_group_for_course', 'auth_joomdle') . ' ' .$course->fullname,  (int) $course->id);
        }

        if ($joomla_user_groups) {
            $auth_joomdle->call_method ('updateUserGroups', (int) $course->id, $course->fullname);
        }

        // Forward event to Joomla.
        if ($forward_events) {
            $data = array ();
            $data['course_id'] = $course->id;
            $data['course_name'] = $course->fullname;
            $data['summary'] = $course->summary;
            $data['category'] = $course->category;

            $conditions = array ('id' => $course->category);
            $cat = $DB->get_record('course_categories', $conditions);

            $data['category_name'] = $cat->name;
            $data['course_shortname'] = $course->shortname;
            $data['idnumber'] = $course->idnumber;
            $data['startdate'] = $course->startdate;
            $data['enddate'] = $course->enddate;
            $auth_joomdle->call_method ('moodleEvent', 'CourseUpdated',  $data);
        }

        return true;
    }

    public static function role_assigned (\core\event\role_assigned $event) {
        global $CFG, $DB;

        $activities = get_config('auth_joomdle', 'jomsocial_activities');
        $groups = get_config('auth_joomdle', 'jomsocial_groups');
        $enrol_parents = get_config('auth_joomdle', 'enrol_parents');
        $parent_role_id = get_config('auth_joomdle', 'parent_role_id');
        $points = get_config('auth_joomdle', 'give_points');
        $auto_mailing_lists = get_config('auth_joomdle', 'auto_mailing_lists');
        $use_kunena_forums = get_config('auth_joomdle', 'use_kunena_forums');
        $joomla_user_groups = get_config('auth_joomdle', 'joomla_user_groups');
        $forward_events = get_config('auth_joomdle', 'forward_events');

        $auth_joomdle = new auth_plugin_joomdle ();

        $context = context::instance_by_id($event->contextid, MUST_EXIST);

        /* If a course enrolment, publish */
        if ($context->contextlevel == CONTEXT_COURSE) {
            $courseid = $context->instanceid;
            $conditions = array ('id' => $courseid);
            $course = $DB->get_record('course', $conditions);
            $conditions = array ('id' => $course->category);
            $cat = $DB->get_record('course_categories', $conditions);
            $userid = $event->relateduserid;
            $conditions = array ('id' => $userid);
            $user = $DB->get_record('user', $conditions);

            // Jomsocial activity.
            if ($activities) {
                $auth_joomdle->call_method ('addActivityCourseEnrolment', $user->username, (int) $courseid, $course->fullname,
                        (int) $course->category, $cat->name);
            }

            $roleid = $event->objectid;
            // Join social group.
            if ($groups) {
                /* Join teachers as group admins, and students as regular members */
                if ($roleid == 3) {
                    $auth_joomdle->call_method ('addSocialGroupMember', $user->username, 1, (int) $courseid);
                } else {
                    $auth_joomdle->call_method ('addSocialGroupMember', $user->username, -1, (int) $courseid);
                }
            }

            // Enrol parents.
            if (($enrol_parents) && ($parent_role_id)) {
                if ($roleid == 5) {
                    /* Get mentors for the student */
                    $usercontext = context_user::instance($userid);
                    $usercontextid = $usercontext->id;

                    $query =
                        "SELECT userid
                        FROM
                        {$CFG->prefix}role_assignments
                        WHERE
                        roleid = ? and contextid = ?
                        ";

                    $params = array ($parent_role_id, $usercontextid);
                    $mentors = $DB->get_records_sql($query, $params);
                    foreach ($mentors as $mentor) {
                        /* Enrol as parent into course*/
                        $conditions = array ('id' => $mentor->userid);
                        $parent_user = $DB->get_record('user', $conditions);
                        $auth_joomdle->enrol_user ($parent_user->username, $courseid, $parent_role_id);
                    }
                }
            }

            if ($points) {
                $auth_joomdle->call_method ('addPoints', 'joomdle.enrol', $user->username,   (int) $courseid, $course->fullname);
            }

            if ($auto_mailing_lists) {
                $type = '';
                if ($roleid == 3) {
                    $type = 'course_teachers';
                } else if ($roleid == 5) {
                    $type = 'course_students';

                    /* Get mentors for the student */
                    $usercontext = context_user::instance($userid);
                    $usercontextid = $usercontext->id;

                    $query =
                        "SELECT userid
                        FROM
                        {$CFG->prefix}role_assignments
                        WHERE
                        roleid = ? and contextid = ?
                        ";

                    $params = array ($parent_role_id, $usercontextid);
                    $mentors = $DB->get_records_sql($query, $params);
                    foreach ($mentors as $mentor) {
                        $conditions = array ('id' => $mentor->userid);
                        $parent_user = $DB->get_record('user', $conditions);

                        $auth_joomdle->call_method ('addMailingSub',  $parent_user->username,   (int) $courseid, 'course_parents');
                    }
                }

                if ($type) {
                    $auth_joomdle->call_method ('addMailingSub',  $user->username,   (int) $courseid, $type);
                }
            }

            if ($joomla_user_groups) {
                $type = '';
                if ($roleid == 3) {
                    $type = 'teachers';
                } else if ($roleid == 5) {
                    $type = 'students';
                }

                if ($type) {
                    $auth_joomdle->call_method ('addGroupMember',  (int) $courseid, $user->username, $type);
                }
            }

            if ($use_kunena_forums) {
                if ($roleid == 3) {
                    $auth_joomdle->call_method ('addForumsModerator',  (int) $courseid, $user->username);
                }
            }

            // Forward event to Joomla.
            if ($forward_events) {
                $data = array ();
                $data['course_id'] = $courseid;
                $data['username'] = $user->username;
                $data['course_name'] = $course->fullname;
                $data['roleid'] = $roleid;
                $auth_joomdle->call_method ('moodleEvent', 'RoleAssigned',  $data);
            }
        }

        return true;
    }

    public static function role_unassigned (\core\event\role_unassigned $event) {
        global $DB, $CFG;

        $groups = get_config('auth_joomdle', 'jomsocial_groups');
        $auto_mailing_lists = get_config('auth_joomdle', 'auto_mailing_lists');
        $use_kunena_forums = get_config('auth_joomdle', 'use_kunena_forums');
        $joomla_user_groups = get_config('auth_joomdle', 'joomla_user_groups');
        $parent_role_id = get_config('auth_joomdle', 'parent_role_id');
        $forward_events = get_config('auth_joomdle', 'forward_events');

        $auth_joomdle = new auth_plugin_joomdle ();

        $context = context::instance_by_id($event->contextid, MUST_EXIST);
        /* If a course unenrolment, remove from group */
        if ($context->contextlevel == CONTEXT_COURSE) {
            $courseid = $context->instanceid;
            $conditions = array ('id' => $courseid);
            $course = $DB->get_record('course', $conditions);
            $conditions = array ('id' => $course->category);
            $cat = $DB->get_record('course_categories', $conditions);
            $userid = $event->relateduserid;
            $conditions = array ('id' => $userid);
            $user = $DB->get_record('user', $conditions);

            if ($groups) {
                $auth_joomdle->call_method ('removeSocialGroupMember', $user->username, $courseid);
            }

            $roleid = $event->objectid;
            $type = '';
            if ($auto_mailing_lists) {
                if ($roleid == 3) {
                    $type = 'course_teachers';
                } else if ($roleid == 5) {
                    $type = 'course_students';

                    /* Get mentors for the student */
                    $usercontext = context_user::instance($userid);
                    $usercontextid = $usercontext->id;

                    $query =
                        "SELECT userid
                        FROM
                        {$CFG->prefix}role_assignments
                        WHERE
                        roleid = ? and contextid = ?
                        ";

                    $params = array ($parent_role_id, $usercontextid);
                    $mentors = $DB->get_records_sql($query, $params);
                    foreach ($mentors as $mentor) {
                        $conditions = array ('id' => $mentor->userid);
                        $parent_user = $DB->get_record('user', $conditions);

                        $auth_joomdle->call_method ('removeMailingSub',  $parent_user->username, (int) $courseid, 'course_parents');
                    }
                }

                $auth_joomdle->call_method ('removeMailingSub',  $user->username,   (int) $courseid, $type);
            }

            if ($joomla_user_groups) {
                $type = '';
                if ($roleid == 3) {
                    $type = 'teachers';
                } else if ($roleid == 5) {
                    $type = 'students';
                }

                if ($type)
                    $auth_joomdle->call_method ('removeGroupMember',  (int) $courseid, $user->username, $type);
            }

            if ($use_kunena_forums) {
                if ($roleid == 3) {
                    $auth_joomdle->call_method ('removeForumsModerator',  (int) $courseid, $user->username);
                }
            }

            // Forward event to Joomla.
            if ($forward_events) {
                $data = array ();
                $data['course_id'] = $courseid;
                $data['username'] = $user->username;
                $data['course_name'] = $course->fullname;
                $auth_joomdle->call_method ('moodleEvent', 'RoleUnassigned',  $data);
            }
        }

        return true;
    }

    public static function attempt_submitted (\mod_quiz\event\attempt_submitted $event) {
        global $DB , $CFG;

        $activities = get_config('auth_joomdle', 'jomsocial_activities');
        $points = get_config('auth_joomdle', 'give_points');
        $forward_events = get_config('auth_joomdle', 'forward_events');

        $auth_joomdle = new auth_plugin_joomdle ();

        $course  = $DB->get_record('course', array('id' => $event->courseid));
        $quiz    = $DB->get_record('quiz', array('id' => $event->other['quizid']));
        $user    = $DB->get_record('user', array('id' => $event->other['submitterid']));

        if ($activities) {
            $auth_joomdle->call_method ('addActivityQuizAttempt', $user->username, (int) $event->courseid, $course->fullname,
                    $quiz->name);
        }

        if ($points) {
            $auth_joomdle->call_method ('addPoints', 'joomdle.quiz_attempt', $user->username, (int) $event->courseid,
                    $course->fullname);
        }

        // Forward event to Joomla.
        if ($forward_events) {
            $data = array ();
            $data['course_id'] = $event->courseid;
            $data['course_name'] = $course->fullname;
            $data['quiz_name'] = $quiz->name;
            $data['username'] = $user->username;
            $auth_joomdle->call_method ('moodleEvent', 'QuizAttemptSubmitted',  $data);
        }

        return true;
    }

    public static function course_module_created (\core\event\course_module_created $event) {
        $use_kunena_forums = get_config('auth_joomdle', 'use_kunena_forums');
        $forward_events = get_config('auth_joomdle', 'forward_events');

        $auth_joomdle = new auth_plugin_joomdle ();

        if ($use_kunena_forums) {
            if ($event->other['modulename'] == 'forum') {
                $auth_joomdle->call_method ('addForum', (int) $event->courseid, $event->objectid, $event->other['name']);
            }
        }

        // Forward event to Joomla.
        if ($forward_events) {
            $data = array ();
            $data['course_id'] = $event->courseid;
            $data['objectid'] = $event->objectid;
            $data['name'] = $event->other['name'];
            $data['module'] = $event->other['modulename'];
            $auth_joomdle->call_method ('moodleEvent', 'CourseModuleCreated',  $data);
        }

        return true;
    }

    public static function course_module_deleted (\core\event\course_module_deleted $event) {
        $use_kunena_forums = get_config('auth_joomdle', 'use_kunena_forums');
        $forward_events = get_config('auth_joomdle', 'forward_events');

        $auth_joomdle = new auth_plugin_joomdle ();

        if ($use_kunena_forums) {
            if ($event->other['modulename'] == 'forum') {
                $auth_joomdle->call_method ("removeForum", (int) $event->courseid, $event->objectid);
            }
        }

        // Forward event to Joomla.
        if ($forward_events) {
            $data = array ();
            $data['course_id'] = $event->courseid;
            $data['objectid'] = $event->objectid;
            $data['module'] = $event->other['modulename'];
            $auth_joomdle->call_method ('moodleEvent', 'CourseModuleDeleted',  $data);
        }

        return true;
    }

    public static function course_module_updated (\core\event\course_module_updated $event) {
        $use_kunena_forums = get_config('auth_joomdle', 'use_kunena_forums');
        $forward_events = get_config('auth_joomdle', 'forward_events');

        $auth_joomdle = new auth_plugin_joomdle ();

        if ($use_kunena_forums) {
            if ($event->other['modulename'] == 'forum') {
                $auth_joomdle->call_method ("updateForum", (int) $event->courseid, $event->objectid, $event->other['name']);
            }
        }

        // Forward event to Joomla.
        if ($forward_events) {
            $data = array ();
            $data['course_id'] = $event->courseid;
            $data['objectid'] = $event->objectid;
            $data['name'] = $event->other['name'];
            $data['module'] = $event->other['modulename'];
            $auth_joomdle->call_method ('moodleEvent', 'CourseModuleUpdated',  $data);
        }

        return true;
    }

    public static function course_completed (\core\event\course_completed $event) {
        global $DB , $CFG;

        $activities = get_config('auth_joomdle', 'jomsocial_activities');
        $points = get_config('auth_joomdle', 'give_points');
        $forward_events = get_config('auth_joomdle', 'forward_events');

        $auth_joomdle = new auth_plugin_joomdle ();

        $course  = $DB->get_record('course', array('id' => $event->courseid));
        $user    = $DB->get_record('user', array('id' => $event->relateduserid));

        if ($activities) {
            $auth_joomdle->call_method ('addActivityCourseCompleted', $user->username, (int) $event->courseid, $course->fullname);
        }

        if ($points) {
            $auth_joomdle->call_method ('addPoints', 'joomdle.course_completed', $user->username, (int) $event->courseid,
                    $course->fullname);
        }

        // Forward event to Joomla.
        if ($forward_events) {
            $data = array ();
            $data['course_id'] = $event->courseid;
            $data['course_name'] = $course->fullname;
            $data['username'] = $user->username;
            $auth_joomdle->call_method ('moodleEvent', 'CourseCompleted',  $data);
        }

        return true;
    }

    // Note. This does not sync password to Joomla anymore, because hash algo is now different in Joomla and Moodle.
    // We also don't need it: work is done by user_update_password in auth.php for password changes / admin user edits.
    // We have not found a way to make it work for users created in Moodle directly.
    public static function user_password_updated (\core\event\user_password_updated $event) {

        $sync_to_joomla = get_config('auth_joomdle', 'sync_to_joomla');
        $forward_events = get_config('auth_joomdle', 'forward_events');

        $user = $event->get_record_snapshot('user', $event->contextinstanceid);

        if ($forward_events) {
            $data = array ();
            $data['username'] = $user->username;
            $data['password'] = $user->password;
            $auth_joomdle->call_method ('moodleEvent', 'UserPasswordUpdated',  $data);
        }

        // To be removed: hash algo is now different in Joomla and Moodle.
        /*
        if (!$sync_to_joomla) {
            return true;
        }

        if ($user->auth != 'joomdle') {
            return true;
        }

        $auth_joomdle = new auth_plugin_joomdle ();
        $auth_joomdle->call_method ('changePassword', $user->username, $user->password);
        */
    }
}
