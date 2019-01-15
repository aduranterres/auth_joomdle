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
 * Joomdle login landing script
 *
 * @package    auth_joomdle
 * @copyright  2009 Qontori Pte Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot.'/auth/joomdle/auth.php');

// It gives a warning if no context set, I guess it does nor matter which we use.
$PAGE->set_context(context_system::instance());

// Grab the GET params.
$token         = optional_param('token',  '',  PARAM_TEXT);
$username = optional_param('username',   '',   PARAM_TEXT);
$username = strtolower ($username);
$create_user = optional_param('create_user', '',     PARAM_TEXT);
$wantsurl      = optional_param('wantsurl', '', PARAM_TEXT);
$use_wrapper      = optional_param('use_wrapper', '', PARAM_TEXT);
$id      = optional_param('id', '', PARAM_TEXT);
$course_id      = optional_param('course_id', '', PARAM_TEXT); // Additional course_id param used for quiz view.
$mtype      = optional_param('mtype', '', PARAM_TEXT);
$day      = optional_param('day', '', PARAM_TEXT);
$mon      = optional_param('mon', '', PARAM_TEXT);
$year      = optional_param('year', '', PARAM_TEXT);
$time      = optional_param('time', '', PARAM_TEXT);
$itemid      = optional_param('Itemid', '', PARAM_TEXT);
$lang      = optional_param('lang', '', PARAM_TEXT);
$topic      = optional_param('topic', '', PARAM_TEXT);
$redirect      = optional_param('redirect', '', PARAM_TEXT); // Redirect moodle param.

$auth = new auth_plugin_joomdle ();

$override_itemid = $auth->call_method ('getDefaultItemid');

if ($override_itemid) {
    $itemid = $override_itemid;
}

// First check this is a Joomdle user.
$user = get_complete_user_data('username', $username);
if (($user->auth == 'joomdle') || (!$user)) {
    if (($username != 'guest') && ((!isloggedin()) || (isguestuser()))) {

        /* Logged user trying to access */
        $logged = $auth->call_method ("confirmJoomlaSession", $username, $token);

        if (is_array ($logged) && xmlrpc_is_fault($logged)) {
            trigger_error("xmlrpc: $response[faultString] ($response[faultCode])");
        } else {
            if ($logged) {
                // Log in.
                $user = get_complete_user_data('username', $username);
                if (!$user) {
                    if ($create_user) {
                        $auth->create_joomdle_user ($username);
                    } else {
                        /* If the user does not exists and we don't have to create it, we are done */
                        $redirect_url = get_config ('auth_joomdle', 'joomla_url');
                        redirect($redirect_url);
                    }
                }
                $user = get_complete_user_data('username', $username);
                complete_user_login($user);

                // Call user_authenticated_hook.
                $authsenabled = get_enabled_auth_plugins();
				foreach ($authsenabled as $hau) {
					$hauth = get_auth_plugin($hau);
					$hauth->user_authenticated_hook($user, $username, "");
				}
            } // Logged.
        }
    } // Username != guest.
} // auth = joomdle

// Redirect.
if ($use_wrapper) {
    $redirect_url = get_config ('auth_joomdle', 'joomla_url');
    switch ($mtype)
    {
        case "event":
            $redirect_url .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id" .
                "&time=$time&Itemid=$itemid";
            break;
        case "course":
            $redirect_url .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&Itemid=$itemid";
            if ($topic)
                $redirect_url .= '&topic='.$topic;
            break;
        case "news":
            $redirect_url .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&Itemid=$itemid";
            break;
        case "forum":
            $redirect_url .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id" .
                "&course_id=$course_id&Itemid=$itemid";
            break;
        case "user":
            $redirect_url .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&Itemid=$itemid";
            break;
        case "edituser":
            $redirect_url .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&Itemid=$itemid";
            break;
        case "resource":
        case "quiz":
        case "page":
        case "assignment":
        case "folder":
            $redirect_url .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&course_id=$course_id&Itemid=$itemid";
        default:
            if ($mtype) {
                $redirect_url .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id" .
                    "&course_id=$course_id&Itemid=$itemid";
            } else {
                if ($wantsurl) {
                    $redirect_url = urldecode ($wantsurl);
                } else {
                    $redirect_url = get_config ('auth_joomdle', 'joomla_url');
                }
            }
    }
    if ($redirect) {
        $redirect_url .= "&redirect=1";
    }
} else {
    $redirect_url = $CFG->wwwroot;
    switch ($mtype) {
        case "course":
            $redirect_url .= "/course/view.php?id=$id";

            if ($topic) {
                $redirect_url .= '&topic='.$topic;
            }
            break;
        case "news":
            $redirect_url .= "/mod/forum/discuss.php?d=$id";
            break;
        case "forum":
            $redirect_url .= "/mod/forum/view.php?id=$id";
            break;
        case "event":
            $redirect_url .= "/calendar/view.php?view=day&time=$time";
            break;
        case "user":
            $redirect_url .= "/user/view.php?id=$id";
            break;
        case "resource":
            $redirect_url .= "/mod/resource/view.php?id=$id";
            break;
        case "quiz":
            $redirect_url .= "/mod/quiz/view.php?id=$id";
            break;
        case "page":
            $redirect_url .= "/mod/page/view.php?id=$id";
            break;
        case "assignment":
            $redirect_url .= "/mod/assignment/view.php?id=$id";
            break;
        case "folder":
            $redirect_url .= "/mod/folder/view.php?id=$id";
            break;
        default:
            if ($mtype) {
                $redirect_url .= "/mod/$mtype/view.php?id=$id";
            } else {
                preg_match('@^(?:https?://)?([^/]+)@i',
                    get_config ('auth_joomdle', 'joomla_url'), $matches);
                $host = $matches[0];

                /* If not full URL, see if path/host is needed */
                if (($wantsurl) &&
                    (substr ($wantsurl, 0, 7) != 'http://') &&
                    (substr ($wantsurl, 0, 8) != 'https://')) {
                    /* If no initial slash, it is a joomla relative path. We add path */
                    if ($wantsurl[0] != '/') {
                        $path = parse_url (get_config ('auth_joomdle', 'joomla_url'), PHP_URL_PATH);
                        $wantsurl = $path.'/'.$wantsurl;
                    }

                    if ($wantsurl) {
                        $redirect_url = $host.urldecode ($wantsurl);
                    } else {
                        $redirect_url = get_config ('auth_joomdle', 'joomla_url');
                    }
                } else {
                    $redirect_url = $wantsurl;
                }
            }

    }
    if ($redirect) {
        $redirect_url .= "&redirect=1";
    }
}

if ($lang) {
    $redirect_url .= '&lang='.$lang;
}

redirect($redirect_url);
