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


function xmldb_auth_joomdle_uninstall() {
    global $CFG, $DB;

    $joomdle_deconfig = new joomdle_moodle_deconfig ();
    $joomdle_deconfig->delete_user ();
    $joomdle_deconfig->delete_role ();
    $joomdle_deconfig->delete_webservice ();
}

class joomdle_moodle_deconfig {

    public function delete_user () {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/auth/joomdle/auth.php');

        $auth_joomdle = new auth_plugin_joomdle ();
        $auth_joomdle->delete_user ('joomdle_connector');
    }

    public function delete_role () {
        global $CFG, $DB;

        $conditions = array ('shortname' => 'joomdlews');
        $role = $DB->get_record('role', $conditions);

        if (!$role) {
            return;
        }

        delete_role ($role->id);
    }

    public function delete_webservice () {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/webservice/lib.php');

        $webservicemanager = new webservice;
        $servicedata = $webservicemanager->get_external_service_by_shortname ('joomdle');

        $webservicemanager->delete_service($servicedata->id);
        $params = array(
            'objectid' => $servicedata->id
        );
        $event = \core\event\webservice_service_deleted::create($params);
        $event->add_record_snapshot('external_services', $service);
        $event->trigger();
    }

}
