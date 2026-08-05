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
 * Installation support for the Joomdle authentication plugin.
 *
 * @package    auth_joomdle
 * @copyright  2009 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_external\util;

class_alias(\core_external\util::class, 'external_util');

/**
 * Performs the Joomdle authentication plugin installation.
 *
 * @return void
 */
function xmldb_auth_joomdle_install() {
}

// Setup is called from admin_setting_configtext_initial_config.
/**
 * Configures the Moodle resources required by Joomdle.
 */
class joomdle_moodle_config {
    /**
     * Enables web services in Moodle.
     *
     * @return void
     */
    public function enable_web_services() {
        set_config('enablewebservices', 1);
    }

    /**
     * Enables the REST web service protocol.
     *
     * @return void
     */
    public function enable_rest() {
        global $CFG;

        $activewebservices = empty($CFG->webserviceprotocols) ? [] : explode(',', $CFG->webserviceprotocols);

        $webservice = 'rest';
        if (!in_array($webservice, $activewebservices)) {
            $activewebservices[] = $webservice;
            $activewebservices = array_unique($activewebservices);

            set_config('webserviceprotocols', implode(',', $activewebservices));
        }
    }

    /**
     * Creates the Joomdle connector user when it does not exist.
     *
     * @return void
     */
    public function create_user() {
        global $CFG;

        require_once($CFG->dirroot . '/lib/moodlelib.php');
        require_once($CFG->dirroot . '/user/lib.php');

        // First check that the user does not already exist.
        $user = get_complete_user_data('username', 'joomdle_connector');
        if ($user) {
            return;
        }

        // Create user.
        $username = 'joomdle_connector';
        $password = random_string(20);

        $newuser = new stdClass();
        $newuser->username = $username;
        $newuser->email = "joomdle@donotdeletemeplease.com";
        $newuser->confirmed = 1;
        $newuser->mnethostid = 1;
        $newuser->firstname = 'Joomdle';
        $newuser->lastname = 'Connector';

        $newuser->id = user_create_user($newuser, false, false);

        $user = get_complete_user_data('id', $newuser->id);
        update_internal_user_password($user, $password);
    }

    /**
     * Creates the Joomdle role and assigns its required capabilities.
     *
     * @return void
     */
    public function add_user_capability() {
        global $CFG, $DB;

        // Create new role.
        $role = $DB->get_record('role', ['shortname' => 'joomdlews']);
        if (!$role) {
            $roleid = create_role(
                'Joomdle Web Services',
                'joomdlews',
                'Role to give required capabilities to the Joomdle Connector user'
            );
            set_role_contextlevels($roleid, [CONTEXT_SYSTEM]);
        } else {
            $roleid = $role->id;
        }

        // Enable REST capability for role.
        $context = context_system::instance();
        assign_capability('webservice/rest:use', CAP_ALLOW, $roleid, $context->id, true);

        // Enable required capabilities for Joomdle work.
        assign_capability('mod/forum:viewdiscussion', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/calendar:manageentries', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/user:create', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/user:delete', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/user:viewdetails', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/user:viewalldetails', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/course:view', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/course:viewhiddencourses', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/category:viewcourselist', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/course:viewparticipants', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/site:viewparticipants', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/grade:viewall', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/site:configview', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/category:viewhiddencategories', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/role:assign', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/role:review', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/course:enrolreview', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('enrol/manual:enrol', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('enrol/manual:unenrol', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('enrol/manual:manage', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/cohort:manage', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/grade:managegradingforms', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/course:managegroups', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/badges:viewotherbadges', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('report/completion:view', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/user:update', CAP_ALLOW, $roleid, $context->id, true);

        // Restrict the connector to the standard course roles it needs to assign.
        foreach (['editingteacher', 'teacher', 'student'] as $archetype) {
            foreach (get_archetype_roles($archetype) as $targetrole) {
                $conditions = ['roleid' => $roleid, 'allowassign' => $targetrole->id];
                if (!$DB->record_exists('role_allow_assign', $conditions)) {
                    core_role_set_assign_allowed($roleid, $targetrole->id);
                }
            }
        }

        // Add user to role.
        $user = get_complete_user_data('username', 'joomdle_connector');
        role_assign($roleid, $user->id, $context->id);
    }

    /**
     * Creates the Joomdle external web service when it does not exist.
     *
     * @return void
     */
    public function create_webservice() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/webservice/lib.php');

        // Check that it does not exist yet.
        $webservicemanager = new webservice();

        // Get Joomdle web service.
        $service = $webservicemanager->get_external_service_by_shortname('joomdle');

        if ($service) {
            return;
        }

        // Check if there is already a service with name=Joomdle that could conflict because of index.
        $service = $DB->get_record(
            'external_services',
            ['name' => 'Joomdle'],
            '*'
        );

        if ($service) {
            // Change shortname of this service to joomdle so that we can use it.
            $service->shortname = 'joomdle';
            $webservicemanager->update_external_service($service);
            return;
        }

        $servicedata = new stdClass();
        $servicedata->name = 'Joomdle';
        $servicedata->shortname = 'joomdle';
        $servicedata->enabled = 1;
        $servicedata->restrictedusers = 1;
        $servicedata->downloadfiles = 0;
        $servicedata->uploadfiles = 0;
        $servicedata->requiredcapability = '';
        $servicedata->id = 0;

        $webservicemanager = new webservice();

        $servicedata->id = $webservicemanager->add_external_service($servicedata);
        $params = [
            'objectid' => $servicedata->id,
        ];
        $event = \core\event\webservice_service_created::create($params);
        $event->trigger();
    }

    /**
     * Adds the Joomdle external functions to its web service.
     *
     * @return void
     */
    public function add_functions() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/webservice/lib.php');

        $webservicemanager = new webservice();

        // Get Joomdle web service.
        $service = $webservicemanager->get_external_service_by_shortname('joomdle');

        if (!$service) {
            return;
        }

        // Get Joomdle functions from DB, as they were already added by Moodle.
        $select = "name LIKE 'joomdle_%'";
        $functions = $DB->get_records_select('external_functions', $select);

        foreach ($functions as $function) {
            // Make sure the function is not there yet.
            if (
                !$webservicemanager->service_function_exists(
                    $function->name,
                    $service->id
                )
            ) {
                $webservicemanager->add_external_function_to_service(
                    $function->name,
                    $service->id
                );
            }
        }
    }

    /**
     * Authorises the Joomdle connector user for the web service.
     *
     * @return void
     */
    public function add_user_to_service() {
        global $CFG;

        require_once($CFG->dirroot . '/webservice/lib.php');

        $webservicemanager = new webservice();

        // Get Joomdle web service.
        $service = $webservicemanager->get_external_service_by_shortname('joomdle');

        if (!$service) {
            return;
        }

        $user = get_complete_user_data('username', 'joomdle_connector');

        if (!$user) {
            return;
        }

        $serviceuser = new stdClass();
        $serviceuser->externalserviceid = $service->id;
        $serviceuser->userid = $user->id;
        $webservicemanager->add_ws_authorised_user($serviceuser);
    }

    /**
     * Creates a permanent token for the Joomdle connector user.
     *
     * @return void
     */
    public function create_token() {
        global $CFG, $OUTPUT;

        require_once($CFG->dirroot . '/webservice/lib.php');

        $webservicemanager = new webservice();

        $user = get_complete_user_data('username', 'joomdle_connector');

        if (!$user) {
            return;
        }

        // Get Joomdle web service.
        $selectedservice = $webservicemanager->get_external_service_by_shortname('joomdle');

        if (!$selectedservice) {
            return;
        }

        // Check the the user is allowed for the service.
        if ($selectedservice->restrictedusers) {
            $restricteduser = $webservicemanager->get_ws_authorised_user($selectedservice->id, $user->id);
            if (empty($restricteduser)) {
                $allowuserurl = new moodle_url(
                    '/' . $CFG->admin . '/webservice/service_users.php',
                    ['id' => $selectedservice->id]
                );
                $allowuserlink = html_writer::tag('a', $selectedservice->name, ['href' => $allowuserurl]);
                $errormsg = $OUTPUT->notification(get_string('usernotallowed', 'webservice', $allowuserlink));
            }
        }

        // Check if the user is deleted. unconfirmed, suspended or guest.
        if ($user->id == $CFG->siteguest || $user->deleted || !$user->confirmed || $user->suspended) {
            throw new moodle_exception('forbiddenwsuser', 'webservice');
        }

        // Process the creation.
        if (empty($errormsg)) {
            $this->external_generate_token(
                EXTERNAL_TOKEN_PERMANENT,
                $selectedservice->id,
                $user->id,
                context_system::instance(),
                0,
                ''
            );
        }
    }

    /**
     * Generates a token without requiring the core external library.
     *
     * @param int $tokentype The token type.
     * @param int|string|stdClass $serviceorid The service identifier, name, or object.
     * @param int $userid The user identifier.
     * @param int|context $contextorid The context identifier or object.
     * @param int $validuntil The token expiry timestamp, or zero for no expiry.
     * @param string $iprestriction The IP restriction.
     * @return string The generated token.
     */
    public function external_generate_token(
        $tokentype,
        $serviceorid,
        $userid,
        $contextorid,
        $validuntil = 0,
        $iprestriction = ''
    ) {
        if (is_numeric($serviceorid)) {
            $service = util::get_service_by_id($serviceorid);
        } else if (is_string($serviceorid)) {
            $service = util::get_service_by_name($serviceorid);
        } else {
            $service = $serviceorid;
        }

        if (!is_object($contextorid)) {
            $context = context::instance_by_id($contextorid, MUST_EXIST);
        } else {
            $context = $contextorid;
        }

        return util::generate_token(
            $tokentype,
            $service,
            $userid,
            $context,
            $validuntil,
            $iprestriction
        );
    }
}
