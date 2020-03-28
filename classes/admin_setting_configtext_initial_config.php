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
 * Special setting for auth_joomdle that does initial setup
 *
 * @package    auth_joomdle
 * @copyright  2019 Qontori
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Special setting for auth_joomdle that does initial setup
 *
 * @package    auth_joomdle
 * @copyright  2019 Qontori
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_joomdle_admin_setting_configtext_initial_config extends admin_setting_configtext {

    /**
     * We need to set things up if they are not yet
     *
     * @param string $data Form data.
     * @return string Empty when no errors.
     */
    public function write_setting($data) {

        global $CFG;

        // Joomdle initial config

        require_once($CFG->dirroot.'/auth/joomdle/db/install.php');
        require_once($CFG->dirroot.'/lib/upgradelib.php');

        if (!$this->initial_config_already_done ()) {
            $joomdle_config = new joomdle_moodle_config ();
            $joomdle_config->enable_web_services ();
            $joomdle_config->enable_xmlrpc ();
            $joomdle_config->create_user ();
            $joomdle_config->add_user_capability ();
            $joomdle_config->create_webservice ();
            $joomdle_config->add_functions ();
            $joomdle_config->add_user_to_service ();
            $joomdle_config->create_token ();

            external_update_descriptions ('auth_joomdle');
        }

        return parent::write_setting($data);
    }

    private function initial_config_already_done () {
        global $CFG, $DB;

        // We need to check if config was already done
        // We check the presence of Joomdle service

        require_once($CFG->dirroot . '/webservice/lib.php');

        $webservicemanager = new webservice;
        $service = $webservicemanager->get_external_service_by_shortname ('joomdle');

        if ($service)
            return true;
        else 
            return false;
    }
}
