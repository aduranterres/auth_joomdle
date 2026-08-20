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
$string['cannotgeneratecertificate'] = 'The certificate PDF could not be generated.';
$string['cantopencurlfile'] = 'Cannot open CURL file: {$a}';
$string['cantwritecurlfile'] = 'Cannot write CURL file: {$a}';
$string['joomla_sp_description'] = 'Services for Joomla Integration<br>';
$string['joomla_sp_name'] = 'Joomdle';
$string['pluginname'] = 'Joomdle';
$string['privacy:metadata:joomla'] = 'Joomdle exchanges personal data with Joomla for account synchronisation, single sign-on, course integration and event forwarding.';
$string['privacy:metadata:joomla:accountconfirmed'] = 'Whether the user account has been confirmed.';
$string['privacy:metadata:joomla:accountsuspended'] = 'Whether the user account is suspended or blocked.';
$string['privacy:metadata:joomla:activityname'] = 'The name of a course activity associated with the user.';
$string['privacy:metadata:joomla:address'] = 'The user address.';
$string['privacy:metadata:joomla:alternatename'] = 'The alternative name of the user.';
$string['privacy:metadata:joomla:authenticationmethod'] = 'The authentication method used by the user account.';
$string['privacy:metadata:joomla:badgehash'] = 'The unique hash of a badge awarded to the user.';
$string['privacy:metadata:joomla:badgeimageurl'] = 'The URL of the image for a badge awarded to the user.';
$string['privacy:metadata:joomla:badgename'] = 'The name of a badge awarded to the user.';
$string['privacy:metadata:joomla:canunenrol'] = 'Whether the user can unenrol from the course.';
$string['privacy:metadata:joomla:certificatecode'] = 'The unique code of a certificate issued to the user.';
$string['privacy:metadata:joomla:certificatecontent'] = 'The encoded content of a certificate generated for the user.';
$string['privacy:metadata:joomla:certificatefilename'] = 'The filename of a certificate generated for the user.';
$string['privacy:metadata:joomla:certificateid'] = 'The identifier of a certificate issued to the user.';
$string['privacy:metadata:joomla:certificateissuedate'] = 'The date when a certificate was issued to the user.';
$string['privacy:metadata:joomla:certificatename'] = 'The name of a certificate issued to the user.';
$string['privacy:metadata:joomla:city'] = 'The city in the user profile.';
$string['privacy:metadata:joomla:cohortid'] = 'The identifier of a cohort associated with the user.';
$string['privacy:metadata:joomla:cohortname'] = 'The name of a cohort associated with the user.';
$string['privacy:metadata:joomla:completiontime'] = 'The time when the user completed a course.';
$string['privacy:metadata:joomla:country'] = 'The country in the user profile.';
$string['privacy:metadata:joomla:coursecategorydescription'] = 'The description of the category containing a course associated with the user.';
$string['privacy:metadata:joomla:coursecategoryid'] = 'The identifier of the category containing a course associated with the user.';
$string['privacy:metadata:joomla:coursecategoryname'] = 'The name of the category containing a course associated with the user.';
$string['privacy:metadata:joomla:coursecompleted'] = 'Whether a course completion event has occurred for the user.';
$string['privacy:metadata:joomla:courseenddate'] = 'The end date of a course associated with the user.';
$string['privacy:metadata:joomla:coursefileurl'] = 'The URL of a file in a course summary associated with the user.';
$string['privacy:metadata:joomla:coursefullname'] = 'The full name of a course associated with the user.';
$string['privacy:metadata:joomla:courseid'] = 'The identifier of a course associated with the user.';
$string['privacy:metadata:joomla:courseidnumber'] = 'The external identifier of a course associated with the user.';
$string['privacy:metadata:joomla:coursesectionid'] = 'The identifier of a course section associated with the user.';
$string['privacy:metadata:joomla:coursesectionname'] = 'The name of a course section associated with the user.';
$string['privacy:metadata:joomla:coursesectionsummary'] = 'The summary of a course section associated with the user.';
$string['privacy:metadata:joomla:courseshortname'] = 'The short name of a course associated with the user.';
$string['privacy:metadata:joomla:coursestartdate'] = 'The start date of a course associated with the user.';
$string['privacy:metadata:joomla:coursesummary'] = 'The summary of a course associated with the user.';
$string['privacy:metadata:joomla:customfielddata'] = 'The value of a custom profile field belonging to the user.';
$string['privacy:metadata:joomla:customfieldid'] = 'The identifier of a custom profile field belonging to the user.';
$string['privacy:metadata:joomla:customfieldname'] = 'The name of a custom profile field belonging to the user.';
$string['privacy:metadata:joomla:customfieldshortname'] = 'The short name of a custom profile field belonging to the user.';
$string['privacy:metadata:joomla:department'] = 'The department in the user profile.';
$string['privacy:metadata:joomla:description'] = 'The description in the user profile.';
$string['privacy:metadata:joomla:email'] = 'The user email address.';
$string['privacy:metadata:joomla:enrolenddate'] = 'The end date of the user enrolment.';
$string['privacy:metadata:joomla:enrolled'] = 'Whether the user is enrolled in a course.';
$string['privacy:metadata:joomla:enrolperiod'] = 'The duration of the user enrolment.';
$string['privacy:metadata:joomla:enrolstartdate'] = 'The start date of the user enrolment.';
$string['privacy:metadata:joomla:eventdescription'] = 'The description of a calendar or Moodle event associated with the user.';
$string['privacy:metadata:joomla:eventduration'] = 'The duration of a calendar event associated with the user.';
$string['privacy:metadata:joomla:eventid'] = 'The identifier of a calendar event associated with the user.';
$string['privacy:metadata:joomla:eventname'] = 'The name of a calendar event associated with the user.';
$string['privacy:metadata:joomla:eventstarttime'] = 'The start time of a calendar event associated with the user.';
$string['privacy:metadata:joomla:eventtype'] = 'The type of calendar or Moodle event associated with the user.';
$string['privacy:metadata:joomla:finalgrade'] = 'The final grade awarded to the user.';
$string['privacy:metadata:joomla:firstname'] = 'The first name of the user.';
$string['privacy:metadata:joomla:firstnamephonetic'] = 'The phonetic first name of the user.';
$string['privacy:metadata:joomla:fullname'] = 'The full name of the user.';
$string['privacy:metadata:joomla:gradecategoryname'] = 'The name of a grade category containing the user grade.';
$string['privacy:metadata:joomla:gradeduedate'] = 'The due date of the activity for which the user is graded.';
$string['privacy:metadata:joomla:gradefeedback'] = 'The feedback provided with the user grade.';
$string['privacy:metadata:joomla:gradeiteminstance'] = 'The activity instance associated with the user grade.';
$string['privacy:metadata:joomla:gradeitemname'] = 'The name of the item for which the user is graded.';
$string['privacy:metadata:joomla:gradeletter'] = 'The letter representation of the user grade.';
$string['privacy:metadata:joomla:grademax'] = 'The maximum possible value for the user grade.';
$string['privacy:metadata:joomla:grademin'] = 'The minimum possible value for the user grade.';
$string['privacy:metadata:joomla:grademodifiedtime'] = 'The time when the user grade was last modified.';
$string['privacy:metadata:joomla:groupdescription'] = 'The description of a group associated with the user.';
$string['privacy:metadata:joomla:groupid'] = 'The identifier of a group associated with the user.';
$string['privacy:metadata:joomla:groupname'] = 'The name of a group associated with the user.';
$string['privacy:metadata:joomla:idnumber'] = 'The identification number in the user profile.';
$string['privacy:metadata:joomla:inenroldate'] = 'Whether the user is within the permitted enrolment dates.';
$string['privacy:metadata:joomla:institution'] = 'The institution in the user profile.';
$string['privacy:metadata:joomla:language'] = 'The preferred language of the user.';
$string['privacy:metadata:joomla:lastname'] = 'The last name of the user.';
$string['privacy:metadata:joomla:lastnamephonetic'] = 'The phonetic last name of the user.';
$string['privacy:metadata:joomla:membershiptype'] = 'The type of course, group or mailing-list membership held by the user.';
$string['privacy:metadata:joomla:middlename'] = 'The middle name of the user.';
$string['privacy:metadata:joomla:moduleid'] = 'The identifier of a course module associated with the user activity.';
$string['privacy:metadata:joomla:modulename'] = 'The type or name of a course module associated with the user activity.';
$string['privacy:metadata:joomla:newusername'] = 'The new username when the username is changed.';
$string['privacy:metadata:joomla:password'] = 'The user password, which is sent during registration, login or password change.';
$string['privacy:metadata:joomla:phone1'] = 'The primary phone number in the user profile.';
$string['privacy:metadata:joomla:phone2'] = 'The secondary phone number in the user profile.';
$string['privacy:metadata:joomla:previoususername'] = 'The previous username when the username is changed.';
$string['privacy:metadata:joomla:profilepicture'] = 'The user profile picture identifier.';
$string['privacy:metadata:joomla:profilepictureurl'] = 'The URL from which the user profile picture can be obtained.';
$string['privacy:metadata:joomla:quizattemptsubmitted'] = 'Whether the user has submitted a quiz attempt.';
$string['privacy:metadata:joomla:quizname'] = 'The name of a quiz for which the user submitted an attempt.';
$string['privacy:metadata:joomla:roleid'] = 'The identifier of a role assigned to the user in a course.';
$string['privacy:metadata:joomla:rolename'] = 'The name of a role assigned to the user in a course.';
$string['privacy:metadata:joomla:siteadmin'] = 'Whether the user is a site administrator.';
$string['privacy:metadata:joomla:timezone'] = 'The time zone in the user profile.';
$string['privacy:metadata:joomla:userid'] = 'The Moodle identifier of the user.';
$string['privacy:metadata:joomla:username'] = 'The username of the user.';
$string['redirectlessssoerror'] = 'Redirect-less SSO login in Joomla failed: {$a}';
$string['redirectlessssohosterror'] = 'Redirect-less SSO requires Moodle and Joomla to use the same host.';
