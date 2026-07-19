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
 * Language strings for the Joomdle authentication plugin.
 *
 * @package    auth_joomdle
 * @copyright  2009 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$string['auth_joomdledescription'] = 'This method uses Joomdle web services to know whether a user has a valid session in Joomla';
$string['auth_joomla_auto_mailing_lists'] = 'Auto mailing lists';
$string['auth_joomla_auto_mailing_lists_description'] = 'Automatically manage mailing lists following Joomla configuration';
$string['auth_joomla_connection_method'] = 'Connection method<br>';
$string['auth_joomla_connection_method_description'] = 'Connection method to use for Web Services<br>';
$string['auth_joomla_forward_events'] = 'Forward Moodle events to Joomla';
$string['auth_joomla_forward_events_description'] = 'Forward events so Joomla plugins can react to them';
$string['auth_joomla_joomla_auth_token'] = 'Joomdle\'s Joomla  authentication token';
$string['auth_joomla_joomla_auth_token_description'] = 'Auth token as configured in Joomdle in Joomla';
$string['auth_joomla_joomla_lang'] = 'Joomla default language';
$string['auth_joomla_joomla_lang_description'] = 'Joomla default language string. Only needed when multi language is enabled in Joomla';
$string['auth_joomla_joomla_sef'] = 'Joomla SEF enabled';
$string['auth_joomla_joomla_sef_description'] = 'Joomla SEF setting. Only needed when multi language is enabled in Joomla';
$string['auth_joomla_joomla_user_groups'] = 'Use Joomla user groups';
$string['auth_joomla_joomla_user_groups_description'] = 'Create Joomla user groups for students and teachers.';
$string['auth_joomla_logout_redirect_to_joomla'] = 'Redirect to Joomla on Moodle logout';
$string['auth_joomla_logout_redirect_to_joomla_description'] = 'Redirect to Joomla on Moodle logout';
$string['auth_joomla_logout_with_redirect'] = 'Use logout with redirect';
$string['auth_joomla_logout_with_redirect_description'] = 'Required only for cross-domain and "remember me" set';
$string['auth_joomla_redirectless_sso'] = 'Use redirect-less SSO';
$string['auth_joomla_redirectless_sso_description'] = 'Use SSO without redirection. Requires cURL';
$string['auth_joomla_single_log_out'] = 'Single log out';
$string['auth_joomla_single_log_out_description'] = 'Log out from Joomla when logging out of Moodle';
$string['auth_joomla_sync_to_joomla'] = 'Sync users to Joomla';
$string['auth_joomla_sync_to_joomla_description'] = 'Syncs new users and profile updates to Joomla';
$string['auth_joomla_url'] = 'Joomla URL<br>';
$string['auth_joomla_url_desc'] = 'Joomla server URL<br>';
$string['cantopencurlfile'] = 'Cannot open CURL file: {$a}';
$string['cantwritecurlfile'] = 'Cannot write CURL file: {$a}';
$string['joomla_sp_description'] = 'Services for Joomla Integration<br>';
$string['joomla_sp_name'] = 'Joomdle';
$string['redirectlessssoerror'] = 'Redirect-less SSO login in Joomla failed: {$a}';
$string['redirectlessssohosterror'] = 'Redirect-less SSO requires Moodle and Joomla to use the same host.';
$string['pluginname'] = 'Joomdle';
