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
 * @copyright  2009 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/auth/joomdle/auth.php');

/**
 * Event handler for joomdle auth plugin.
 */
class auth_joomdle_handler {
    /**
     * Handles user creation events.
     *
     * @param \core\event\user_created $event The user creation event.
     * @return bool True when the event has been handled.
     */
    public static function user_created(\core\event\user_created $event) {
        global $CFG, $DB;

        $synctojoomla = get_config('auth_joomdle', 'sync_to_joomla');
        $forwardevents = get_config('auth_joomdle', 'forward_events');

        if (!$synctojoomla) {
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

        $authjoomdle = new auth_plugin_joomdle();

        // Create the user in Joomla.
        $userinfo = [];
        $userinfo['username'] = $user->username;
        // We can't sync password, because we get in hashed, and hash algo is different in Joomla.
        // $userinfo['password'] = $user->password;
        // $userinfo['password2'] = $user->password;
        $userinfo['name'] = $user->firstname . " " . $user->lastname;
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
        $contextid = $usercontext->id;

        if ($user->picture) {
            $userinfo['pic_url'] = $CFG->wwwroot . "/pluginfile.php/$contextid/user/icon/f1";
        }

        $userinfo['block'] = 0;
        $userinfo['confirmed'] = $user->confirmed;

        /* Custom fields */
        $query = "SELECT f.id, d.data
                    FROM {$CFG->prefix}user_info_field as f, {$CFG->prefix}user_info_data d
                    WHERE f.id=d.fieldid and userid = ?";

        $params = [$id];
        $records = $DB->get_records_sql($query, $params);

        $i = 0;
        $userinfo['custom_fields'] = [];
        foreach ($records as $field) {
            $userinfo['custom_fields'][$i]['id'] = $field->id;
            $userinfo['custom_fields'][$i]['data'] = $field->data;
            $i++;
        }

        $authjoomdle->call_method("createUser", $userinfo);

        // Forward event to Joomla.
        if ($forwardevents) {
            $authjoomdle->call_method('moodleEvent', 'UserCreated', $userinfo);
        }

        return true;
    }


    /**
     * Handles user update events.
     *
     * @param \core\event\user_updated $event The user update event.
     * @return bool True when the event has been handled.
     */
    public static function user_updated(\core\event\user_updated $event) {
        global $CFG, $DB;

        $synctojoomla = get_config('auth_joomdle', 'sync_to_joomla');
        $forwardevents = get_config('auth_joomdle', 'forward_events');

        $authjoomdle = new auth_plugin_joomdle();

        // Forward event to Joomla.
        if ($forwardevents) {
            $authjoomdle->call_method('moodleEvent', 'UserUpdated', $userinfo);
        }

        if (!$synctojoomla) {
            return true;
        }

        $user = $event->get_record_snapshot('user', $event->objectid);

        if ($user->auth != 'joomdle') {
            return true;
        }

        /* Update user info in Joomla */
        $userinfo = [];
        $userinfo['username'] = $user->username;
        $userinfo['name'] = $user->firstname . " " . $user->lastname;
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
        $contextid = $usercontext->id;

        if ($user->picture) {
            $userinfo['pic_url'] = $CFG->wwwroot . "/pluginfile.php/$contextid/user/icon/f1";
        }

        $userinfo['block'] = $user->suspended;
        $userinfo['confirmed'] = $user->confirmed;

        /* Custom fields */
        $query = "SELECT f.id, d.data
                    FROM {$CFG->prefix}user_info_field as f, {$CFG->prefix}user_info_data d
                    WHERE f.id=d.fieldid and userid = ?";

        $params = [$id];
        $records = $DB->get_records_sql($query, $params);

        $i = 0;
        $userinfo['custom_fields'] = [];
        foreach ($records as $field) {
            $userinfo['custom_fields'][$i]['id'] = $field->id;
            $userinfo['custom_fields'][$i]['data'] = $field->data;
            $i++;
        }

        $authjoomdle->call_method("updateUser", $userinfo);

        return true;
    }

    /**
     * Handles user deletion events.
     *
     * @param \core\event\user_deleted $event The user deletion event.
     * @return bool True when the event has been handled.
     */
    public static function user_deleted(\core\event\user_deleted $event) {
        $synctojoomla = get_config('auth_joomdle', 'sync_to_joomla');
        $forwardevents = get_config('auth_joomdle', 'forward_events');

        if (!$synctojoomla) {
            return true;
        }

        $user = $event->get_record_snapshot('user', $event->objectid);

        if ($user->auth != 'joomdle') {
            return true;
        }

        $authjoomdle = new auth_plugin_joomdle();

        $authjoomdle->call_method("deleteUser", $user->username);

        // Forward event to Joomla.
        if ($forwardevents) {
            $data = [];
            $data['username'] = $user->username;
            $authjoomdle->call_method('moodleEvent', 'UserDeleted', $data);
        }

        return true;
    }

    /**
     * Handles course creation events.
     *
     * @param \core\event\course_created $event The course creation event.
     * @return void
     */
    public static function course_created(\core\event\course_created $event) {
        self::new_course_event($event);
    }

    /**
     * Handles course restoration events.
     *
     * @param \core\event\course_restored $event The course restoration event.
     * @return void
     */
    public static function course_restored(\core\event\course_restored $event) {
        self::new_course_event($event);
    }

    /**
     * Performs the common handling for new and restored courses.
     *
     * @param \core\event\base $event The course event.
     * @return bool True when the event has been handled.
     */
    private static function new_course_event($event) {
        global $DB;

        $forwardevents = get_config('auth_joomdle', 'forward_events');

        $course = $event->get_record_snapshot('course', $event->objectid);

        $joomlausergroups = get_config('auth_joomdle', 'joomla_user_groups');

        $authjoomdle = new auth_plugin_joomdle();

        /* kludge for the call_method fn to work */
        if (!$course->summary) {
            $course->summary = ' ';
        }

        $conditions = ['id' => $course->category];
        $cat = $DB->get_record('course_categories', $conditions);

        $context = context_course::instance($course->id);
        $course->summary = file_rewrite_pluginfile_urls(
            $course->summary,
            'pluginfile.php',
            $context->id,
            'course',
            'summary',
            null
        );
        $course->summary = str_replace('pluginfile.php', '/auth/joomdle/pluginfile_joomdle.php', $course->summary);

        if ($joomlausergroups) {
            $authjoomdle->call_method('addUserGroups', (int) $course->id, $course->fullname);
        }

        // Forward event to Joomla.
        if ($forwardevents) {
            $data = [];
            $data['course_id'] = $course->id;
            $data['course_name'] = $course->fullname;
            $data['summary'] = $course->summary;
            $data['category'] = $course->category;
            $data['category_name'] = $cat->name;
            $data['course_shortname'] = $course->shortname;
            $data['idnumber'] = $course->idnumber;
            $data['startdate'] = $course->startdate;
            $data['enddate'] = $course->enddate;
            $authjoomdle->call_method('moodleEvent', 'CourseCreated', $data);
        }

        return true;
    }

    /**
     * Handles course deletion events.
     *
     * @param \core\event\course_deleted $event The course deletion event.
     * @return bool True when the event has been handled.
     */
    public static function course_deleted(\core\event\course_deleted $event) {
        $forwardevents = get_config('auth_joomdle', 'forward_events');

        $course = $event->get_record_snapshot('course', $event->objectid);

        $joomlausergroups = get_config('auth_joomdle', 'joomla_user_groups');

        $authjoomdle = new auth_plugin_joomdle();

        if ($joomlausergroups) {
            $authjoomdle->call_method("removeUserGroups", (int) $course->id);
        }

        // Forward event to Joomla.
        if ($forwardevents) {
            $data = [];
            $data['course_name'] = $course->fullname;
            $authjoomdle->call_method('moodleEvent', 'CourseDeleted', $data);
        }

        return true;
    }

    /**
     * Handles course update events.
     *
     * @param \core\event\course_updated $event The course update event.
     * @return bool True when the event has been handled.
     */
    public static function course_updated(\core\event\course_updated $event) {
        global $DB;

        $forwardevents = get_config('auth_joomdle', 'forward_events');

        $course = $event->get_record_snapshot('course', $event->objectid);

        $joomlausergroups = get_config('auth_joomdle', 'joomla_user_groups');

        $authjoomdle = new auth_plugin_joomdle();

        if ($joomlausergroups) {
            $authjoomdle->call_method('updateUserGroups', (int) $course->id, $course->fullname);
        }

        // Forward event to Joomla.
        if ($forwardevents) {
            $data = [];
            $data['course_id'] = $course->id;
            $data['course_name'] = $course->fullname;
            $data['summary'] = $course->summary;
            $data['category'] = $course->category;

            $conditions = ['id' => $course->category];
            $cat = $DB->get_record('course_categories', $conditions);

            $data['category_name'] = $cat->name;
            $data['course_shortname'] = $course->shortname;
            $data['idnumber'] = $course->idnumber;
            $data['startdate'] = $course->startdate;
            $data['enddate'] = $course->enddate;
            $authjoomdle->call_method('moodleEvent', 'CourseUpdated', $data);
        }

        return true;
    }

    /**
     * Handles role assignment events.
     *
     * @param \core\event\role_assigned $event The role assignment event.
     * @return bool True when the event has been handled.
     */
    public static function role_assigned(\core\event\role_assigned $event) {
        global $DB;

        $automailinglists = get_config('auth_joomdle', 'auto_mailing_lists');
        $joomlausergroups = get_config('auth_joomdle', 'joomla_user_groups');
        $forwardevents = get_config('auth_joomdle', 'forward_events');

        $authjoomdle = new auth_plugin_joomdle();

        $context = context::instance_by_id($event->contextid, MUST_EXIST);

        /* If a course enrolment, publish */
        if ($context->contextlevel == CONTEXT_COURSE) {
            $courseid = $context->instanceid;
            $conditions = ['id' => $courseid];
            $course = $DB->get_record('course', $conditions);
            $conditions = ['id' => $course->category];
            $cat = $DB->get_record('course_categories', $conditions);
            $userid = $event->relateduserid;
            $conditions = ['id' => $userid];
            $user = $DB->get_record('user', $conditions);

            $roleid = $event->objectid;

            if ($automailinglists) {
                $type = '';
                if ($roleid == 3) {
                    $type = 'course_teachers';
                } else if ($roleid == 5) {
                    $type = 'course_students';
                }

                if ($type) {
                    $authjoomdle->call_method('addMailingSub', $user->username, (int) $courseid, $type);
                }
            }

            if ($joomlausergroups) {
                $type = '';
                if ($roleid == 3) {
                    $type = 'teachers';
                } else if ($roleid == 5) {
                    $type = 'students';
                }

                if ($type) {
                    $authjoomdle->call_method('addGroupMember', (int) $courseid, $user->username, $type);
                }
            }

            // Forward event to Joomla.
            if ($forwardevents) {
                $data = [];
                $data['course_id'] = $courseid;
                $data['username'] = $user->username;
                $data['course_name'] = $course->fullname;
                $data['roleid'] = $roleid;
                $authjoomdle->call_method('moodleEvent', 'RoleAssigned', $data);
            }
        }

        return true;
    }

    /**
     * Handles role unassignment events.
     *
     * @param \core\event\role_unassigned $event The role unassignment event.
     * @return bool True when the event has been handled.
     */
    public static function role_unassigned(\core\event\role_unassigned $event) {
        global $DB, $CFG;

        $automailinglists = get_config('auth_joomdle', 'auto_mailing_lists');
        $joomlausergroups = get_config('auth_joomdle', 'joomla_user_groups');
        $forwardevents = get_config('auth_joomdle', 'forward_events');

        $authjoomdle = new auth_plugin_joomdle();

        $context = context::instance_by_id($event->contextid, MUST_EXIST);
        /* If a course unenrolment, remove from group */
        if ($context->contextlevel == CONTEXT_COURSE) {
            $courseid = $context->instanceid;
            $conditions = ['id' => $courseid];
            $course = $DB->get_record('course', $conditions);
            $conditions = ['id' => $course->category];
            $cat = $DB->get_record('course_categories', $conditions);
            $userid = $event->relateduserid;
            $conditions = ['id' => $userid];
            $user = $DB->get_record('user', $conditions);

            $roleid = $event->objectid;
            $type = '';
            if ($automailinglists) {
                if ($roleid == 3) {
                    $type = 'course_teachers';
                } else if ($roleid == 5) {
                    $type = 'course_students';
                }

                $authjoomdle->call_method('removeMailingSub', $user->username, (int) $courseid, $type);
            }

            if ($joomlausergroups) {
                $type = '';
                if ($roleid == 3) {
                    $type = 'teachers';
                } else if ($roleid == 5) {
                    $type = 'students';
                }

                if ($type) {
                    $authjoomdle->call_method('removeGroupMember', (int) $courseid, $user->username, $type);
                }
            }

            // Forward event to Joomla.
            if ($forwardevents) {
                $data = [];
                $data['course_id'] = $courseid;
                $data['username'] = $user->username;
                $data['course_name'] = $course->fullname;
                $authjoomdle->call_method('moodleEvent', 'RoleUnassigned', $data);
            }
        }

        return true;
    }

    /**
     * Handles submitted quiz attempt events.
     *
     * @param \mod_quiz\event\attempt_submitted $event The submitted attempt event.
     * @return bool True when the event has been handled.
     */
    public static function attempt_submitted(\mod_quiz\event\attempt_submitted $event) {
        global $DB, $CFG;

        $forwardevents = get_config('auth_joomdle', 'forward_events');

        $authjoomdle = new auth_plugin_joomdle();

        $course  = $DB->get_record('course', ['id' => $event->courseid]);
        $quiz    = $DB->get_record('quiz', ['id' => $event->other['quizid']]);
        $user    = $DB->get_record('user', ['id' => $event->other['submitterid']]);

        // Forward event to Joomla.
        if ($forwardevents) {
            $data = [];
            $data['course_id'] = $event->courseid;
            $data['course_name'] = $course->fullname;
            $data['quiz_name'] = $quiz->name;
            $data['username'] = $user->username;
            $authjoomdle->call_method('moodleEvent', 'QuizAttemptSubmitted', $data);
        }

        return true;
    }

    /**
     * Handles course module creation events.
     *
     * @param \core\event\course_module_created $event The module creation event.
     * @return bool True when the event has been handled.
     */
    public static function course_module_created(\core\event\course_module_created $event) {
        $forwardevents = get_config('auth_joomdle', 'forward_events');

        $authjoomdle = new auth_plugin_joomdle();

        // Forward event to Joomla.
        if ($forwardevents) {
            $data = [];
            $data['course_id'] = $event->courseid;
            $data['objectid'] = $event->objectid;
            $data['name'] = $event->other['name'];
            $data['module'] = $event->other['modulename'];
            $authjoomdle->call_method('moodleEvent', 'CourseModuleCreated', $data);
        }

        return true;
    }

    /**
     * Handles course module deletion events.
     *
     * @param \core\event\course_module_deleted $event The module deletion event.
     * @return bool True when the event has been handled.
     */
    public static function course_module_deleted(\core\event\course_module_deleted $event) {
        $forwardevents = get_config('auth_joomdle', 'forward_events');

        $authjoomdle = new auth_plugin_joomdle();

        // Forward event to Joomla.
        if ($forwardevents) {
            $data = [];
            $data['course_id'] = $event->courseid;
            $data['objectid'] = $event->objectid;
            $data['module'] = $event->other['modulename'];
            $authjoomdle->call_method('moodleEvent', 'CourseModuleDeleted', $data);
        }

        return true;
    }

    /**
     * Handles course module update events.
     *
     * @param \core\event\course_module_updated $event The module update event.
     * @return bool True when the event has been handled.
     */
    public static function course_module_updated(\core\event\course_module_updated $event) {
        $forwardevents = get_config('auth_joomdle', 'forward_events');

        $authjoomdle = new auth_plugin_joomdle();

        // Forward event to Joomla.
        if ($forwardevents) {
            $data = [];
            $data['course_id'] = $event->courseid;
            $data['objectid'] = $event->objectid;
            $data['name'] = $event->other['name'];
            $data['module'] = $event->other['modulename'];
            $authjoomdle->call_method('moodleEvent', 'CourseModuleUpdated', $data);
        }

        return true;
    }

    /**
     * Handles course completion events.
     *
     * @param \core\event\course_completed $event The course completion event.
     * @return bool True when the event has been handled.
     */
    public static function course_completed(\core\event\course_completed $event) {
        global $DB;

        $forwardevents = get_config('auth_joomdle', 'forward_events');

        $authjoomdle = new auth_plugin_joomdle();

        $course  = $DB->get_record('course', ['id' => $event->courseid]);
        $user    = $DB->get_record('user', ['id' => $event->relateduserid]);

        // Forward event to Joomla.
        if ($forwardevents) {
            $data = [];
            $data['course_id'] = $event->courseid;
            $data['course_name'] = $course->fullname;
            $data['username'] = $user->username;
            $authjoomdle->call_method('moodleEvent', 'CourseCompleted', $data);
        }

        return true;
    }

    // Note. This does not sync password to Joomla anymore, because hash algo is now different in Joomla and Moodle.
    // We also don't need it: work is done by user_update_password in auth.php for password changes / admin user edits.
    // We have not found a way to make it work for users created in Moodle directly.
    /**
     * Handles user password update events.
     *
     * @param \core\event\user_password_updated $event The password update event.
     * @return void
     */
    public static function user_password_updated(\core\event\user_password_updated $event) {
        $forwardevents = get_config('auth_joomdle', 'forward_events');

        $user = $event->get_record_snapshot('user', $event->contextinstanceid);

        $authjoomdle = new auth_plugin_joomdle();

        if ($forwardevents) {
            $data = [];
            $data['username'] = $user->username;
            $data['password'] = $user->password;
            $authjoomdle->call_method('moodleEvent', 'UserPasswordUpdated', $data);
        }
    }
}
