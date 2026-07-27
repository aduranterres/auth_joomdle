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
 * Upgrade steps for the Joomdle authentication plugin.
 *
 * @package    auth_joomdle
 * @copyright  2009 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrades the Joomdle authentication plugin.
 *
 * @param int $oldversion The version being upgraded from.
 * @return bool True when the upgrade is complete.
 */
function xmldb_auth_joomdle_upgrade($oldversion) {
    // Refresh the functions associated with the service.
    $joomdleupgrade = new joomdle_upgrade();
    $joomdleupgrade->remove_old_functions();
    $joomdleupgrade->add_new_functions();

    if ($oldversion < 2026071800) {
        // Add new required capabilities.
        $joomdleupgrade = new joomdle_upgrade();
        $joomdleupgrade->add_new_required_capabilities();

        // REST is now the only protocol used by Joomdle.
        $joomdleupgrade->enable_rest();
        unset_config('ws_protocol', 'auth_joomdle');
        upgrade_plugin_savepoint(true, 2026071800, 'auth', 'joomdle');
    }

    if ($oldversion < 2026071900) {
        // The service functions were refreshed at the start of the upgrade.
        upgrade_plugin_savepoint(true, 2026071900, 'auth', 'joomdle');
    }

    if ($oldversion < 2026071901) {
        upgrade_plugin_savepoint(true, 2026071901, 'auth', 'joomdle');
    }

    if ($oldversion < 2026072000) {
        // Permit the connector to access hidden courses and assign approved course roles.
        $joomdleupgrade->add_new_required_capabilities();
        upgrade_plugin_savepoint(true, 2026072000, 'auth', 'joomdle');
    }

    return true;
}

/**
 * Performs the Joomdle upgrade operations.
 */
class joomdle_upgrade {
    /**
     * Enables Moodle's REST web service protocol.
     *
     * XML-RPC is not disabled globally because other components may still use it.
     *
     * @return void
     */
    public function enable_rest() {
        global $CFG;

        $activewebservices = empty($CFG->webserviceprotocols) ? [] : explode(',', $CFG->webserviceprotocols);
        if (!in_array('rest', $activewebservices)) {
            $activewebservices[] = 'rest';
            set_config('webserviceprotocols', implode(',', array_unique($activewebservices)));
        }
    }

    /**
     * Adds newly defined functions to the Joomdle web service.
     *
     * @return void
     */
    public function add_new_functions() {
        global $CFG;

        require_once($CFG->dirroot . '/webservice/lib.php');
        // We get functions array from this file.
        require($CFG->dirroot . '/auth/joomdle/db/services.php');

        $webservicemanager = new webservice();

        // Get Joomdle web service.
        $service = $webservicemanager->get_external_service_by_shortname('joomdle');

        if (!$service) {
            return;
        }

        foreach ($functions as $name => $function) {
            // Make sure the function is not there yet.
            if (
                !$webservicemanager->service_function_exists(
                    $name,
                    $service->id
                )
            ) {
                $webservicemanager->add_external_function_to_service(
                    $name,
                    $service->id
                );
            }
        }
    }

    /**
     * Removes obsolete functions from the Joomdle web service.
     *
     * @return void
     */
    public function remove_old_functions() {
        global $CFG;

        require_once($CFG->dirroot . '/webservice/lib.php');
        require($CFG->dirroot . '/auth/joomdle/db/services.php');

        $definedfunctions = $functions;

        $webservicemanager = new webservice();

        // Get Joomdle web service.
        $service = $webservicemanager->get_external_service_by_shortname('joomdle');

        if (!$service) {
            return;
        }

        $servicefunctions = $webservicemanager->get_external_functions([$service->id]);

        foreach ($servicefunctions as $function) {
            if (!isset($definedfunctions[$function->name])) {
                $webservicemanager->remove_external_function_from_service(
                    $function->name,
                    $service->id
                );
            }
        }
    }

    /**
     * Adds the capabilities required by the Joomdle web service role.
     *
     * @return void
     */
    public function add_new_required_capabilities() {
        global $DB;

        // Create new role.
        $role = $DB->get_record('role', ['shortname' => 'joomdlews']);
        if (!$role) {
            return;
        }

        $roleid = $role->id;

        // Enable REST capability for role.
        $context = context_system::instance();

        // Enable required capabilities for Joomdle work.
        assign_capability('moodle/user:create', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/user:delete', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/user:viewdetails', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/user:viewalldetails', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/course:view', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/course:viewhiddencourses', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/category:viewcourselist', CAP_ALLOW, $roleid, $context->id, true);
        assign_capability('moodle/course:viewparticipants', CAP_ALLOW, $roleid, $context->id, true);
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
        assign_capability('moodle/calendar:manageentries', CAP_ALLOW, $roleid, $context->id, true);
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
    }
}
