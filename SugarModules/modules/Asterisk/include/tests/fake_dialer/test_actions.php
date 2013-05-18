<?php

require_once('include/utils.php');
require_once("modules/Contacts/Contact.php");


if ($_REQUEST["action"] == 'ringing') {

    $call_setup_data = action_ringing();
    print json_encode($call_setup_data);
    sugar_cleanup();
}

if ($_REQUEST["action"] == 'connected') {

    action_connected();
}

if ($_REQUEST["action"] == 'hangup') {

    action_hangup();
}

if ($_REQUEST["action"] == 'closed') {
    action_closed();
}

if ($_REQUEST["action"] == 'create_contacts'){
    $contacts_data = action_create_contacts();
    print json_encode($contacts_data);
    sugar_cleanup();   
}

function action_ringing() {
    $extension = get_extension_input();
    $extension_four_digit = get_extension_four_digit($extension);
    $new_id = get_new_call_record_id();
    $gmTimestamp = gmdate("Y-m-d H:i:s");
    $asterisk_id = get_random_asterisk_id();
    $phone_number = $_REQUEST['phone_number'];
    $GLOBALS['log']->fatal($phone_number);
    // Note: Can't use FROM_UNIXTIME like we do in asteriskLogger... reason is we have mysql timezone set to UTC there...
    $GLOBALS['current_user']->db->query(
            "INSERT INTO asterisk_log (call_record_id, asterisk_id, callstate, callerID, channel, remote_channel, timestamp_call, direction, user_extension, inbound_extension) 
            VALUES ('{$new_id}', '{$asterisk_id}', 'Ringing', '{$phone_number}', '{$extension}', 'SIP/flowroute-00000023', '{$gmTimestamp}', 'I', '{$extension_four_digit}', '{$extension_four_digit}')"
    );
            
    $GLOBALS['current_user']->db->query(
            "INSERT INTO calls (id, direction, status) 
             VALUES ('{$new_id}', 'I', 'Planned')"
            );
    
    $call_setup_data = array();
    $call_setup_data['call_record_id'] = $new_id;

    return $call_setup_data;
}

function action_connected() {
    $GLOBALS['log']->fatal($_REQUEST["call_record_id"]);
    $call_record_id = $_REQUEST["call_record_id"];
    $GLOBALS['current_user']->db->query("UPDATE asterisk_log SET callstate = 'Connected' WHERE call_record_id = '{$call_record_id}'");
}

function action_hangup() {
    $call_record_id = $_REQUEST["call_record_id"];
    $GLOBALS['current_user']->db->query("UPDATE asterisk_log SET callstate = 'Hangup' WHERE call_record_id = '{$call_record_id}'");
}

function action_closed() {
    $call_record_id = $_REQUEST["call_record_id"];
    $GLOBALS['current_user']->db->query("UPDATE asterisk_log SET uistate = 'Closed' WHERE call_record_id = '{$call_record_id}'");
    
    $GLOBALS['log']->fatal($_REQUEST["contact_1"]);
    //only delete contacts when you have them
    if($_REQUEST["contact_1"]){delete_fake_contact($_REQUEST["contact_1"]);}
    if($_REQUEST["contact_2"]){delete_fake_contact($_REQUEST["contact_2"]);}
}

function action_create_contacts() {
    if ($_REQUEST['contacts'] >= 1 ) {
        $GLOBALS['log']->fatal('start creation of ' .  $_REQUEST['contacts'] . 'contacts');
        $phone_number = $_REQUEST['phone_number'];
        $GLOBALS['log']->fatal($phone_number);

        // TODO: First check to see if there are more then one matching contacts and don't create them if they do...

        $results = $GLOBALS['current_user']->db->query("SELECT UUID() AS newid");
        $result = $GLOBALS['current_user']->db->fetchByAssoc($results);
        $new_id_jon = $result["newid"];
        $insert = $GLOBALS['current_user']->db->query("
            INSERT INTO contacts (id, date_entered, date_modified, modified_user_id, first_name, last_name, phone_mobile) 
            VALUES ('{$new_id_jon}', NOW(), NOW(), 1,  'Jon', 'Doe', '{$phone_number}')");

        $GLOBALS['log']->fatal('Created John Doe');

        $contact_ids = array(
            'contact_1' => $new_id_jon
        );

        if ($_REQUEST['contacts'] == 2) {
            $results = $GLOBALS['current_user']->db->query("SELECT UUID() AS newid");
            $result = $GLOBALS['current_user']->db->fetchByAssoc($results);
            $new_id_jane = $result["newid"];

            $insert = $GLOBALS['current_user']->db->query("
            INSERT INTO contacts (id, date_entered, date_modified, modified_user_id, first_name, last_name, phone_mobile) 
            VALUES ('{$new_id_jane}', NOW(), NOW(), 1,  'Jane', 'Doe', '{$phone_number}')");

            $GLOBALS['log']->fatal('Created Jan Doe');

            $contact_ids['contact_2'] = $new_id_jane;
        }
        
        return $contact_ids;
    }
}

function delete_fake_contact($contact_id) {
    $GLOBALS['current_user']->db->query("DELETE FROM contacts WHERE id = '{$contact_id}'");
}

function get_new_call_record_id() {
    $results = $GLOBALS['current_user']->db->query("SELECT UUID() AS newid");
    $result = $GLOBALS['current_user']->db->fetchByAssoc($results);
    $new_id = $result["newid"];

    return $new_id;
}

function get_extension_input() {
    $extension = "SIP/{$_REQUEST['extension']}-00000036";

    return $extension;
}

function get_current_server_time() {
    $time = time();

    return $time;
}

function get_random_asterisk_id() {
    $asterisk_id = rand(10000000000, 9999999999) . '.' . rand(10, 99);

    return $asterisk_id;
}
function get_extension_four_digit($extension){
    //example = SIP/7025-0000003
    $result = explode('/', $extension); 
    $result = explode('-', $result[1]);
    
    return $result[0];
  
}

?>
