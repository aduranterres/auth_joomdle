<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

class joomlaws {
    public static function getUserInfo_parameters() {
        return     [
                    'username',
                    'app',
                    ];
    }

    public static function test_parameters() {
        return     [
                    ];
    }

    public static function login_parameters() {
        return     [
                    'username',
                    'password',
                    ];
    }

    public static function getDefaultItemid_parameters() {
        return     [
                    ];
    }

    public static function confirmJoomlaSession_parameters() {
        return     [
                    'username',
                    'joomdle_auth_token',
                    ];
    }

    public static function logout_parameters() {
        return     [
                    'username',
                    'ua_string',
                    ];
    }

    public static function deleteUserKey_parameters() {
        return     [
                    'series',
                    ];
    }

    public static function createUser_parameters() {
        return     [
                    'userinfo',
                    ];
    }

    public static function activateUser_parameters() {
        return     [
                    'username',
                    ];
    }

    public static function updateUser_parameters() {
        return     [
                    'userinfo',
                    ];
    }

    public static function changePassword_parameters() {
        return     [
                    'username',
                    'password',
                    ];
    }

    public static function changeUsername_parameters() {
        return     [
                    'old_username',
                    'new_username',
                    ];
    }

    public static function deleteUser_parameters() {
        return     [
                    'username',
                    ];
    }

    public static function addMailingSub_parameters() {
        return     [
                    'username',
                    'course_id',
                    'type',
                    ];
    }

    public static function removeMailingSub_parameters() {
        return     [
                    'username',
                    'course_id',
                    'type',
                    ];
    }

    public static function addUserGroups_parameters() {
        return     [
                    'course_id',
                    'course_name',
                    ];
    }

    public static function updateUserGroups_parameters() {
        return     [
                    'course_id',
                    'course_name',
                    ];
    }

    public static function removeUserGroups_parameters() {
        return     [
                    'course_id',
                    ];
    }

    public static function addGroupMember_parameters() {
        return     [
                    'course_id',
                    'username',
                    'type',
                    ];
    }

    public static function removeGroupMember_parameters() {
        return     [
                    'course_id',
                    'username',
                    'type',
                    ];
    }

    public static function getSellUrl_parameters() {
        return     [
                    'course_id',
                    ];
    }

    public static function moodleEvent_parameters() {
        return     [
                    'event',
                    'params',
                    ];
    }
}
