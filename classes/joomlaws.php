<?php
class joomlaws {

    public static function getUserInfo_parameters() {
        return     array(
                    'username',
                    'app'
                    );
    }

    public static function test_parameters() {
        return     array(
                    );
    }

    public static function login_parameters() {
        return     array(
                    'username',
                    'password'
                    );
    }

    public static function getDefaultItemid_parameters() {
        return     array(
                    );
    }

    public static function confirmJoomlaSession_parameters() {
        return     array(
                    'username',
                    'joomdle_auth_token'
                    );
    }

    public static function logout_parameters() {
        return     array(
                    'username',
                    'ua_string'
                    );
    }

    public static function deleteUserKey_parameters() {
        return     array(
                    'series'
                    );
    }

    public static function createUser_parameters() {
        return     array(
                    'userinfo'
                    );
    }

    public static function activateUser_parameters() {
        return     array(
                    'username'
                    );
    }

    public static function updateUser_parameters() {
        return     array(
                    'userinfo'
                    );
    }

    public static function changePassword_parameters() {
        return     array(
                    'username',
                    'password'
                    );
    }

    public static function changeUsername_parameters() {
        return     array(
                    'old_username',
                    'new_username'
                    );
    }

    public static function deleteUser_parameters() {
        return     array(
                    'username'
                    );
    }

    public static function addActivityCourse_parameters() {
        return     array(
                    'id',
                    'name',
                    'desc',
                    'cat_id',
                    'cat_name'
                    );
    }

    public static function addActivityCourseEnrolment_parameters() {
        return     array(
                    'username',
                    'course_id',
                    'course_name',
                    'cat_id',
                    'cat_name'
                    );
    }

    public static function addSocialGroup_parameters() {
        return     array(
                    'description',
                    'course_id'
                    );
    }

    public static function updateSocialGroup_parameters() {
        return     array(
                    'name',
                    'desc',
                    'course_id'
                    );
    }

    public static function deleteSocialGroup_parameters() {
        return     array(
                    'course_id'
                    );
    }

    public static function addSocialGroupMember_parameters() {
        return     array(
                    'username',
                    'permissions',
                    'course_id'
                    );
    }

    public static function removeSocialGroupMember_parameters() {
        return     array(
                    'username',
                    'course_id'
                    );
    }

    public static function addPoints_parameters() {
        return     array(
                    'action',
                    'username',
                    'course_id',
                    'course_name'
                    );
    }

    public static function addActivityQuizAttempt_parameters() {
        return     array(
                    'username',
                    'course_id',
                    'course_name',
                    'quiz_name'
                    );
    }

    public static function addMailingSub_parameters() {
        return     array(
                    'username',
                    'course_id',
                    'type'
                    );
    }

    public static function removeMailingSub_parameters() {
        return     array(
                    'username',
                    'course_id',
                    'type'
                    );
    }

    public static function addUserGroups_parameters() {
        return     array(
                    'course_id',
                    'username'
                    );
    }

    public static function updateUserGroups_parameters() {
        return     array(
                    'course_id',
                    'username'
                    );
    }

    public static function removeUserGroups_parameters() {
        return     array(
                    'course_id'
                    );
    }

    public static function addGroupMember_parameters() {
        return     array(
                    'course_id',
                    'username',
                    'type'
                    );
    }

    public static function removeGroupMember_parameters() {
        return     array(
                    'course_id',
                    'username',
                    'type'
                    );
    }

    public static function addForum_parameters() {
        return     array(
                    'course_id',
                    'forum_id',
                    'forum_name'
                    );
    }

    public static function updateForum_parameters() {
        return     array(
                    'course_id',
                    'forum_id',
                    'forum_name'
                    );
    }

    public static function removeForum_parameters() {
        return     array(
                    'course_id',
                    'forum_id'
                    );
    }

    public static function addForumsModerator_parameters() {
        return     array(
                    'course_id',
                    'username'
                    );
    }

    public static function removeForumsModerator_parameters() {
        return     array(
                    'course_id',
                    'username'
                    );
    }

    public static function removeCourseForums_parameters() {
        return     array(
                    'course_id'
                    );
    }

    public static function getSellUrl_parameters() {
        return     array(
                    'course_id'
                    );
    }

    public static function addActivityCourseCompleted_parameters() {
        return     array(
                    'username',
                    'course_id',
                    'course_name'
                    );
    }

    public static function moodleEvent_parameters() {
        return     array(
                    'event',
                    'params'
                    );
    }

}
