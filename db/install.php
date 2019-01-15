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
 * @package    auth_joomdle
 * @copyright  2009 Qontori Pte Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_auth_joomdle_install() {
    global $CFG, $DB;

    $joomdle_config = new joomdle_moodle_config ();
    $joomdle_config->enable_web_services ();
    $joomdle_config->enable_xmlrpc ();
    $joomdle_config->create_user ();
    $joomdle_config->add_user_capability ();
    $joomdle_config->create_webservice ();
    $joomdle_config->add_functions ();
    $joomdle_config->add_user_to_service ();
    $joomdle_config->create_token ();
}

class joomdle_moodle_config {

    public function enable_web_services () {
        set_config('enablewebservices', 1);
    }

    public function enable_xmlrpc () {
        global $CFG;

        $active_webservices = empty($CFG->webserviceprotocols) ? array() : explode(',', $CFG->webserviceprotocols);

        $webservice = 'xmlrpc';
        if (!in_array($webservice, $active_webservices)) {
            $active_webservices[] = $webservice;
            $active_webservices = array_unique($active_webservices);

            set_config('webserviceprotocols', implode(',', $active_webservices));
        }
    }

    public function create_user () {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/lib/moodlelib.php');
        require_once($CFG->dirroot . '/user/lib.php');

        // First check user does not exist already
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

    public function add_user_capability () {
        global $CFG, $DB;

        // Create new role.
        $role = $DB->get_record('role', array('shortname' => 'joomdlews'));
        if (!$role) {
            $roleid = create_role ('Joomdle Web Services', 'joomdlews',
                    'Role to give required capabilities to the Joomdle Connector user');
            set_role_contextlevels ($roleid, array (CONTEXT_SYSTEM));
        } else {
            $roleid = $role->id;
        }
        
        // Enable xmlrpc capability for role.
        $context = context_system::instance();
        assign_capability('webservice/xmlrpc:use', CAP_ALLOW, $roleid, $context->id, true);

        // Enable forums read.
        $context = context_system::instance();
        assign_capability('mod/forum:viewdiscussion', CAP_ALLOW, $roleid, $context->id, true);

        // Enable calendar manage.
        $context = context_system::instance();
        assign_capability('moodle/calendar:manageentries', CAP_ALLOW, $roleid, $context->id, true);

        // Add user to role.
        $user = get_complete_user_data('username', 'joomdle_connector');
        role_assign ($roleid, $user->id, $context->id);
    }

    public function create_webservice () {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/webservice/lib.php');

        // Check that it does not exist yet.
        $webservicemanager = new webservice;

        // Get Joomdle web service.
        $service = $webservicemanager->get_external_service_by_shortname ('joomdle');

        if ($service) {
            return;
        }

        $servicedata = new stdClass ();
        $servicedata->name = 'Joomdle';
        $servicedata->shortname = 'joomdle';
        $servicedata->enabled = 1;
        $servicedata->restrictedusers = 1;
        $servicedata->downloadfiles = 0;
        $servicedata->uploadfiles = 0;
        $servicedata->requiredcapability = '';
        $servicedata->id = 0;

        $webservicemanager = new webservice;

        $servicedata->id = $webservicemanager->add_external_service($servicedata);
        $params = array(
            'objectid' => $servicedata->id
        );
        $event = \core\event\webservice_service_created::create($params);
        $event->trigger();
    }

    public function add_functions () {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/webservice/lib.php');
        require_once($CFG->dirroot . '/auth/joomdle/db/services.php');

        $webservicemanager = new webservice;

        // Get Joomdle web service.
        $service = $webservicemanager->get_external_service_by_shortname ('joomdle');

        if (!$service) {
            return;
        }

        foreach ($functions as $name => $function) {
            // Make sure the function is not there yet.
            if (!$webservicemanager->service_function_exists($name,
                    $service->id)) {
                $webservicemanager->add_external_function_to_service(
                        $name, $service->id);
            }
        }
    }

    public function add_user_to_service () {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/webservice/lib.php');
        require_once($CFG->dirroot . '/auth/joomdle/db/services.php');

        $webservicemanager = new webservice;

        // Get Joomdle web service.
        $service = $webservicemanager->get_external_service_by_shortname ('joomdle');

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

    public function create_token () {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/webservice/lib.php');
        require_once($CFG->dirroot . '/auth/joomdle/db/services.php');

        $webservicemanager = new webservice;

        $user = get_complete_user_data('username', 'joomdle_connector');

        if (!$user) {
            return;
        }

        // Get Joomdle web service.
        $selectedservice = $webservicemanager->get_external_service_by_shortname ('joomdle');

        if (!$selectedservice) {
            return;
        }

        // Check the the user is allowed for the service.
        if ($selectedservice->restrictedusers) {
            $restricteduser = $webservicemanager->get_ws_authorised_user($selectedservice->id, $user->id);
            if (empty($restricteduser)) {
                $allowuserurl = new moodle_url('/' . $CFG->admin . '/webservice/service_users.php',
                        array('id' => $selectedservice->id));
                $allowuserlink = html_writer::tag('a', $selectedservice->name , array('href' => $allowuserurl));
                $errormsg = $OUTPUT->notification(get_string('usernotallowed', 'webservice', $allowuserlink));
            }
        }

        // Check if the user is deleted. unconfirmed, suspended or guest.
        if ($user->id == $CFG->siteguest or $user->deleted or !$user->confirmed or $user->suspended) {
            throw new moodle_exception('forbiddenwsuser', 'webservice');
        }

        // Process the creation.
        if (empty($errormsg)) {
            external_generate_token(EXTERNAL_TOKEN_PERMANENT, $selectedservice->id,
                    $user->id, context_system::instance(),
                    0, '');
        }
    }

}
