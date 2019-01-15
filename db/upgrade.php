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


function xmldb_auth_joomdle_upgrade($oldversion) {
    global $DB, $CFG;

    $dbman = $DB->get_manager();

    if ($oldversion < 2008080273) {
        $sql = "DELETE FROM {events_handlers} WHERE component = 'joomdle'";
        $DB->execute($sql);
        upgrade_plugin_savepoint (true, 2008080273, 'auth', 'joomdle');
    }

    // Add any new functions to the service.
    $joomdle_upgrade = new joomdle_upgrade ();
    $joomdle_upgrade->add_new_functions ();

    // Change in configuration storage
    if ($oldversion < 2008080289) {
        $joomdle_upgrade->change_config_storage ();
        upgrade_plugin_savepoint (true, 2008080289, 'auth', 'joomdle');
    }

    return true;
}

class joomdle_upgrade {

    public function add_new_functions () {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/webservice/lib.php');
        // We get functions array from this file.
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

    public function change_config_storage ()
    {
        global $CFG, $DB;

        $sql = "UPDATE {config_plugins} SET plugin = REPLACE(plugin, '/', '_') WHERE plugin='auth/joomdle'";
        $DB->execute($sql);
    }

}
