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
 * Uninstallation support for the Joomdle authentication plugin.
 *
 * @package    auth_joomdle
 * @copyright  2009 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Removes the Moodle data created by the Joomdle authentication plugin.
 *
 * @return void
 */
function xmldb_auth_joomdle_uninstall() {
    $joomdledeconfig = new joomdle_moodle_deconfig();
    $joomdledeconfig->delete_user();
    $joomdledeconfig->delete_role();
    $joomdledeconfig->delete_webservice();
}

/**
 * Removes the Moodle resources created for the Joomdle integration.
 */
class joomdle_moodle_deconfig {
    /**
     * Deletes the Joomdle connector user.
     *
     * @return void
     */
    public function delete_user() {
        global $CFG;

        require_once($CFG->dirroot . '/auth/joomdle/auth.php');

        $authjoomdle = new auth_plugin_joomdle();
        $authjoomdle->delete_user('joomdle_connector');
    }

    /**
     * Deletes the Joomdle web service role.
     *
     * @return void
     */
    public function delete_role() {
        global $DB;

        $conditions = ['shortname' => 'joomdlews'];
        $role = $DB->get_record('role', $conditions);

        if (!$role) {
            return;
        }

        delete_role($role->id);
    }

    /**
     * Deletes the Joomdle external web service.
     *
     * @return void
     */
    public function delete_webservice() {
        global $CFG;

        require_once($CFG->dirroot . '/webservice/lib.php');

        $webservicemanager = new webservice();
        $servicedata = $webservicemanager->get_external_service_by_shortname('joomdle');

        if (!$servicedata) {
            return;
        }

        $webservicemanager->delete_service($servicedata->id);
        $params = [
            'objectid' => $servicedata->id,
        ];
        $event = \core\event\webservice_service_deleted::create($params);
        $event->add_record_snapshot('external_services', $servicedata);
        $event->trigger();
    }
}
