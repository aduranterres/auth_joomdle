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
 * Privacy Subsystem implementation for auth_joomdle.
 *
 * @package    auth_joomdle
 * @copyright  2009 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_joomdle\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;

/**
 * Privacy provider for the Joomdle authentication plugin.
 *
 * Joomdle does not store personal data in its own tables. It sends data to Joomla through
 * user synchronisation, SSO, event forwarding, and the Joomdle external web service.
 *
 * @package    auth_joomdle
 * @copyright  2009 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Describe the personal data which may be sent to Joomla.
     *
     * @param collection $collection The metadata collection to update.
     * @return collection The updated metadata collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link(
            'joomla',
            [
                // Account identifiers and profile data.
                'userid' => 'privacy:metadata:joomla:userid',
                'username' => 'privacy:metadata:joomla:username',
                'previoususername' => 'privacy:metadata:joomla:previoususername',
                'newusername' => 'privacy:metadata:joomla:newusername',
                'fullname' => 'privacy:metadata:joomla:fullname',
                'firstname' => 'privacy:metadata:joomla:firstname',
                'lastname' => 'privacy:metadata:joomla:lastname',
                'firstnamephonetic' => 'privacy:metadata:joomla:firstnamephonetic',
                'lastnamephonetic' => 'privacy:metadata:joomla:lastnamephonetic',
                'middlename' => 'privacy:metadata:joomla:middlename',
                'alternatename' => 'privacy:metadata:joomla:alternatename',
                'email' => 'privacy:metadata:joomla:email',
                'city' => 'privacy:metadata:joomla:city',
                'country' => 'privacy:metadata:joomla:country',
                'language' => 'privacy:metadata:joomla:language',
                'timezone' => 'privacy:metadata:joomla:timezone',
                'phone1' => 'privacy:metadata:joomla:phone1',
                'phone2' => 'privacy:metadata:joomla:phone2',
                'address' => 'privacy:metadata:joomla:address',
                'description' => 'privacy:metadata:joomla:description',
                'institution' => 'privacy:metadata:joomla:institution',
                'department' => 'privacy:metadata:joomla:department',
                'idnumber' => 'privacy:metadata:joomla:idnumber',
                'profilepicture' => 'privacy:metadata:joomla:profilepicture',
                'profilepictureurl' => 'privacy:metadata:joomla:profilepictureurl',
                'customfieldid' => 'privacy:metadata:joomla:customfieldid',
                'customfieldname' => 'privacy:metadata:joomla:customfieldname',
                'customfieldshortname' => 'privacy:metadata:joomla:customfieldshortname',
                'customfielddata' => 'privacy:metadata:joomla:customfielddata',
                'accountconfirmed' => 'privacy:metadata:joomla:accountconfirmed',
                'accountsuspended' => 'privacy:metadata:joomla:accountsuspended',
                'authenticationmethod' => 'privacy:metadata:joomla:authenticationmethod',
                'siteadmin' => 'privacy:metadata:joomla:siteadmin',

                // Authentication and SSO data.
                'password' => 'privacy:metadata:joomla:password',

                // Course context and enrolment data.
                'courseid' => 'privacy:metadata:joomla:courseid',
                'coursefullname' => 'privacy:metadata:joomla:coursefullname',
                'courseshortname' => 'privacy:metadata:joomla:courseshortname',
                'courseidnumber' => 'privacy:metadata:joomla:courseidnumber',
                'coursesummary' => 'privacy:metadata:joomla:coursesummary',
                'coursecategoryid' => 'privacy:metadata:joomla:coursecategoryid',
                'coursecategoryname' => 'privacy:metadata:joomla:coursecategoryname',
                'coursecategorydescription' => 'privacy:metadata:joomla:coursecategorydescription',
                'coursesectionid' => 'privacy:metadata:joomla:coursesectionid',
                'coursesectionname' => 'privacy:metadata:joomla:coursesectionname',
                'coursesectionsummary' => 'privacy:metadata:joomla:coursesectionsummary',
                'coursefileurl' => 'privacy:metadata:joomla:coursefileurl',
                'coursestartdate' => 'privacy:metadata:joomla:coursestartdate',
                'courseenddate' => 'privacy:metadata:joomla:courseenddate',
                'enrolled' => 'privacy:metadata:joomla:enrolled',
                'canunenrol' => 'privacy:metadata:joomla:canunenrol',
                'inenroldate' => 'privacy:metadata:joomla:inenroldate',
                'enrolstartdate' => 'privacy:metadata:joomla:enrolstartdate',
                'enrolenddate' => 'privacy:metadata:joomla:enrolenddate',
                'enrolperiod' => 'privacy:metadata:joomla:enrolperiod',
                'roleid' => 'privacy:metadata:joomla:roleid',
                'rolename' => 'privacy:metadata:joomla:rolename',
                'membershiptype' => 'privacy:metadata:joomla:membershiptype',
                'groupid' => 'privacy:metadata:joomla:groupid',
                'groupname' => 'privacy:metadata:joomla:groupname',
                'groupdescription' => 'privacy:metadata:joomla:groupdescription',
                'cohortid' => 'privacy:metadata:joomla:cohortid',
                'cohortname' => 'privacy:metadata:joomla:cohortname',

                // Events and learning activity.
                'eventtype' => 'privacy:metadata:joomla:eventtype',
                'eventid' => 'privacy:metadata:joomla:eventid',
                'eventname' => 'privacy:metadata:joomla:eventname',
                'eventdescription' => 'privacy:metadata:joomla:eventdescription',
                'eventstarttime' => 'privacy:metadata:joomla:eventstarttime',
                'eventduration' => 'privacy:metadata:joomla:eventduration',
                'moduleid' => 'privacy:metadata:joomla:moduleid',
                'modulename' => 'privacy:metadata:joomla:modulename',
                'activityname' => 'privacy:metadata:joomla:activityname',
                'quizname' => 'privacy:metadata:joomla:quizname',
                'quizattemptsubmitted' => 'privacy:metadata:joomla:quizattemptsubmitted',
                'coursecompleted' => 'privacy:metadata:joomla:coursecompleted',
                'completiontime' => 'privacy:metadata:joomla:completiontime',

                // Grades and feedback.
                'gradecategoryname' => 'privacy:metadata:joomla:gradecategoryname',
                'gradeitemname' => 'privacy:metadata:joomla:gradeitemname',
                'grademin' => 'privacy:metadata:joomla:grademin',
                'grademax' => 'privacy:metadata:joomla:grademax',
                'finalgrade' => 'privacy:metadata:joomla:finalgrade',
                'gradeletter' => 'privacy:metadata:joomla:gradeletter',
                'gradefeedback' => 'privacy:metadata:joomla:gradefeedback',
                'gradeduedate' => 'privacy:metadata:joomla:gradeduedate',
                'gradeiteminstance' => 'privacy:metadata:joomla:gradeiteminstance',
                'grademodifiedtime' => 'privacy:metadata:joomla:grademodifiedtime',

                // Certificates and badges.
                'certificateid' => 'privacy:metadata:joomla:certificateid',
                'certificatename' => 'privacy:metadata:joomla:certificatename',
                'certificateissuedate' => 'privacy:metadata:joomla:certificateissuedate',
                'certificatecode' => 'privacy:metadata:joomla:certificatecode',
                'certificatefilename' => 'privacy:metadata:joomla:certificatefilename',
                'certificatecontent' => 'privacy:metadata:joomla:certificatecontent',
                'badgename' => 'privacy:metadata:joomla:badgename',
                'badgehash' => 'privacy:metadata:joomla:badgehash',
                'badgeimageurl' => 'privacy:metadata:joomla:badgeimageurl',
            ],
            'privacy:metadata:joomla'
        );

        return $collection;
    }

    /**
     * Get the contexts containing data for a user.
     *
     * The plugin does not store personal data in Moodle.
     *
     * @param int $userid The user ID.
     * @return contextlist An empty context list.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        return new contextlist();
    }

    /**
     * Get users whose data is stored in a context.
     *
     * @param userlist $userlist The user list for the context.
     */
    public static function get_users_in_context(userlist $userlist) {
    }

    /**
     * Export personal data stored by the plugin.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
    }

    /**
     * Delete all personal data stored by the plugin in a context.
     *
     * @param \context $context The context to delete data from.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
    }

    /**
     * Delete personal data stored by the plugin for several users in a context.
     *
     * @param approved_userlist $userlist The approved context and user information.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
    }

    /**
     * Delete personal data stored by the plugin for a user.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
    }
}
