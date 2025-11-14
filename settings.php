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
 * @package   auth_joomdle
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/auth/joomdle/lib.php');

    // We use a custom admin setting since we need to set things up on install
    require_once($CFG->dirroot.'/auth/joomdle/classes/admin_setting_configtext_initial_config.php');
    require_once($CFG->dirroot.'/auth/joomdle/classes/admin_setting_configselect_initial_config.php');

    $settings->add(new auth_joomdle_admin_setting_configtext_initial_config ('auth_joomdle/joomla_url',
                get_string('auth_joomla_url', 'auth_joomdle'),
                get_string('auth_joomla_url_desc', 'auth_joomdle'), '', PARAM_URL));

    $settings->add(new admin_setting_configtext('auth_joomdle/joomla_auth_token', get_string('auth_joomla_joomla_auth_token', 'auth_joomdle'),
                       get_string('auth_joomla_joomla_auth_token_description', 'auth_joomdle'), '', PARAM_RAW));

    $settings->add(new admin_setting_configselect('auth_joomdle/connection_method',
        get_string('auth_joomla_connection_method', 'auth_joomdle'),
        get_string('auth_joomla_connection_method_description', 'auth_joomdle'), 'fgc', joomdle_get_connection_methods ()));

    $settings->add(new auth_joomdle_admin_setting_configselect_initial_config('auth_joomdle/ws_protocol',
        get_string('auth_joomla_ws_protocol', 'auth_joomdle'),
        get_string('auth_joomla_ws_protocol_description', 'auth_joomdle'), 'rest', joomdle_get_ws_protocols ()));

    $settings->add(new admin_setting_configcheckbox('auth_joomdle/sync_to_joomla', get_string('auth_joomla_sync_to_joomla', 'auth_joomdle'),
                       get_string('auth_joomla_sync_to_joomla_description', 'auth_joomdle'), 0));

    $settings->add(new admin_setting_configtext('auth_joomdle/joomla_lang', get_string('auth_joomla_joomla_lang', 'auth_joomdle'),
                       get_string('auth_joomla_joomla_lang_description', 'auth_joomdle'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configcheckbox('auth_joomdle/joomla_sef', get_string('auth_joomla_joomla_sef', 'auth_joomdle'),
                       get_string('auth_joomla_joomla_sef_description', 'auth_joomdle'), 0));

    $settings->add(new admin_setting_configcheckbox('auth_joomdle/redirectless_sso',
        get_string('auth_joomla_redirectless_sso', 'auth_joomdle'),
        get_string('auth_joomla_redirectless_sso_description', 'auth_joomdle'), 0));

    $settings->add(new admin_setting_configcheckbox('auth_joomdle/single_log_out',
        get_string('auth_joomla_single_log_out', 'auth_joomdle'),
        get_string('auth_joomla_single_log_out_description', 'auth_joomdle'), 1));

    $settings->add(new admin_setting_configcheckbox('auth_joomdle/logout_redirect_to_joomla',
        get_string('auth_joomla_logout_redirect_to_joomla', 'auth_joomdle'),
        get_string('auth_joomla_logout_redirect_to_joomla_description', 'auth_joomdle'), 0));

    $settings->add(new admin_setting_configcheckbox('auth_joomdle/logout_with_redirect',
        get_string('auth_joomla_logout_with_redirect', 'auth_joomdle'),
        get_string('auth_joomla_logout_with_redirect_description', 'auth_joomdle'), 0));

    $settings->add(new admin_setting_configcheckbox('auth_joomdle/jomsocial_activities',
        get_string('auth_joomla_jomsocial_activities', 'auth_joomdle'),
        get_string('auth_joomla_jomsocial_activities_description', 'auth_joomdle'), 0));

    $settings->add(new admin_setting_configcheckbox('auth_joomdle/jomsocial_groups',
        get_string('auth_joomla_jomsocial_groups', 'auth_joomdle'),
        get_string('auth_joomla_jomsocial_groups_description', 'auth_joomdle'), 0));

    $settings->add(new admin_setting_configcheckbox('auth_joomdle/jomsocial_groups_delete',
        get_string('auth_joomla_jomsocial_groups_delete', 'auth_joomdle'),
        get_string('auth_joomla_jomsocial_groups_delete_description', 'auth_joomdle'), 0));

    $settings->add(new admin_setting_configcheckbox('auth_joomdle/enrol_parents',
        get_string('auth_joomla_enrol_parents', 'auth_joomdle'),
        get_string('auth_joomla_enrol_parents_description', 'auth_joomdle'), 0));

    $roles = role_fix_names(get_all_roles(), null, ROLENAME_ORIGINALANDSHORT);
    $r = array ();
    foreach ($roles as $role) {
        $rolename = $role->localname;
        $r[$role->id] = $rolename;
    }
    $settings->add(new admin_setting_configselect('auth_joomdle/parent_role_id',
        get_string('auth_joomla_parent_role_id', 'auth_joomdle'),
        get_string('auth_joomla_parent_role_id_description', 'auth_joomdle'), 0, $r));

    $settings->add(new admin_setting_configcheckbox('auth_joomdle/give_points',
        get_string('auth_joomla_give_points', 'auth_joomdle'),
        get_string('auth_joomla_give_points_description', 'auth_joomdle'), 0));

    $settings->add(new admin_setting_configcheckbox('auth_joomdle/auto_mailing_lists',
        get_string('auth_joomla_auto_mailing_lists', 'auth_joomdle'),
        get_string('auth_joomla_auto_mailing_lists_description', 'auth_joomdle'), 0));

    $settings->add(new admin_setting_configcheckbox('auth_joomdle/joomla_user_groups',
        get_string('auth_joomla_joomla_user_groups', 'auth_joomdle'),
        get_string('auth_joomla_joomla_user_groups_description', 'auth_joomdle'), 0));

    $settings->add(new admin_setting_configcheckbox('auth_joomdle/use_kunena_forums',
        get_string('auth_joomla_use_kunena_forums', 'auth_joomdle'),
        get_string('auth_joomla_use_kunena_forums_description', 'auth_joomdle'), 0));

    $settings->add(new admin_setting_configcheckbox('auth_joomdle/forward_events',
        get_string('auth_joomla_forward_events', 'auth_joomdle'),
        get_string('auth_joomla_forward_events_description', 'auth_joomdle'), 0));


    if ($CFG->version >= 2017051500)  {
        $authplugin = get_auth_plugin('joomdle');
        display_auth_lock_options ($settings, $authplugin->authtype, $authplugin->userfields,
            get_string('auth_fieldlocks_help', 'auth'), false, false);
    }
}
