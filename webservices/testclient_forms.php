<?php
/**
 * @package    auth_joomdle
 * @copyright  2009 Qontori Pte Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class joomdle_get_user_id_form extends moodleform {
    public function definition() {
        global $CFG;
 
        $mform = $this->_form;
 
        $mform->addElement('header', 'wstestclienthdr', get_string('testclient', 'webservice'));
 
        //note: these element are intentionally text aera without validation - we want users to test any rubbish as parameters
        $mform->addElement('text', 'wsusername', 'wsusername');
        $mform->addElement('text', 'wspassword', 'wspassword');
        $mform->addElement('text', 'username', 'username');
 
        $mform->addElement('hidden', 'function');
        $mform->setType('function', PARAM_SAFEDIR);
 
        $mform->addElement('hidden', 'protocol');
        $mform->setType('protocol', PARAM_SAFEDIR);
 
        $mform->addElement('static', 'warning', '', get_string('executewarnign', 'webservice'));
 
        $this->add_action_buttons(true, get_string('execute', 'webservice'));
    }
 
    public function get_params() {
        if (!$data = $this->get_data()) {
            return null;
        }
        // remove unused from form data
        unset($data->submitbutton);
        unset($data->protocol);
        unset($data->function);
        unset($data->wsusername);
        unset($data->wspassword);
 
        $params = array();
        $params['username'] = $data->username;
 
        return $params;
    }
}
?>
