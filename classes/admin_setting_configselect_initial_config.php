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

defined('MOODLE_INTERNAL') || die();

/**
 * Special setting for auth_joomdle to enable the selected ws protocol
 *
 * @package    auth_joomdle
 * @copyright  2021 Qontori
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_joomdle_admin_setting_configselect_initial_config extends admin_setting_configselect {

    /**
     * We need to enable the selected ws protocol
     *
     * @param string $data Form data.
     * @return string Empty when no errors.
     */
    public function write_setting($data) {

        global $CFG;

        require_once($CFG->dirroot.'/auth/joomdle/db/install.php');
        require_once($CFG->dirroot.'/lib/upgradelib.php');

        $joomdle_config = new joomdle_moodle_config ();
        $joomdle_config->enable_protocol ($data);

        return parent::write_setting($data);
    }
}
