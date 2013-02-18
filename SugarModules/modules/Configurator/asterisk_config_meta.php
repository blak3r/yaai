<?php
// This $config_meta array is used by configuratorGeneratorUtil.php to automatically generate the .tpl file html
// The default type = "varchar" & by default it's 'required'
// Put all the config params in order.  They're processed sequentially, each time the 'section' changes a new section header is placed in the template.
$config_meta['asterisk_host'] 	= array('default' => '127.0.0.1', 'section'=>'Asterisk Server Settings');
$config_meta['asterisk_port'] 	= array('default' => '5038', 'type'=>'int', 'section'=>'Asterisk Server Settings');
$config_meta['asterisk_user'] = array('default' => 'ami_user', 'section'=>'Asterisk Server Settings');
$config_meta['asterisk_secret'] = array('default' => 'ami_pass', 'section'=>'Asterisk Server Settings');

$config_meta['asterisk_soapuser'] = array('default' => 'admin','section'=>'SugarCRM SOAP Settings' );
$config_meta['asterisk_soappass'] = array('default' => 'soap_password','section'=>'SugarCRM SOAP Settings');

$config_meta['asterisk_prefix'] = array('default' => '', 'section'=>'Call Configuration', 'required'=>'FALSE');
$config_meta['asterisk_dialinPrefix'] = array('default' => '','section'=>'Call Configuration', 'required'=>'FALSE');
$config_meta['asterisk_context'] = array('default' => 'from-internal','section'=>'Call Configuration');
$config_meta['asterisk_expr'] = array('default' => '^(sip\/[1-9][0-9][0-9]?[0-9]?-|Local)','section'=>'Call Configuration');
$config_meta['asterisk_dialout_channel'] = array('default' => 'SIP/###','section'=>'Call Configuration');
$config_meta['asterisk_dialin_ext_match'] = array('default' => 'Local\/(?:.*?)(\d{2,10})#?@','section'=>'Call Configuration');
$config_meta['asterisk_rg_detect_expr'] = array('default'=>"^Local\/RG",'section'=>'Call Configuration');
$config_meta['asterisk_rg_cell_ring_expr'] = array('default'=>"^Local\/\d{7,10}",'section'=>'Call Configuration');
$config_meta['asterisk_digits_to_match'] = array('default' => '8', 'section'=>'Call Configuration');

$config_meta['asterisk_call_subject_inbound_abbr'] = array('default' => "IBC: ", 'section'=>'Misc');
$config_meta['asterisk_call_subject_outbound_abbr'] = array('default' => "OBC: ",'section'=>'Misc');
$config_meta['asterisk_call_subject_max_length'] = array('default' => '50','section'=>'Misc');
$config_meta['asterisk_listener_poll_rate'] = array('default' => '5000','section'=>'Misc');
//$config_meta['asterisk_gravatar_enabled'] = array('default' => 'false','section'=>'Misc') ;
$config_meta['asterisk_short_call_status'] = array('default' => "Held",'section'=>'Misc');
$config_meta['asterisk_hide_call_popups_after_mins'] = array('default' => '60','section'=>'Misc');

$config_meta['asterisk_log_file'] = array('default' => '', 'section'=>'Logging');


$config_meta['asterisk_opencnam_enabled'] = array('default' => 'true','section'=>'OpenCNAM');
$config_meta['asterisk_opencnam_account_sid'] = array('default'=> '', 'section' => 'OpenCNAM');
$config_meta['asterisk_opencnam_auth_token'] = array('default' => '', 'section'=>'OpenCNAM');


$config_meta['asterisk_fop_url'] = array('default' => '', 'section'=>'Flash Operator Panel Addon');
$config_meta['asterisk_fop_master_password'] = array('default' => '', 'section'=>'Flash Operator Panel Addon');

$config_meta['asterisk_block_button_enabled'] = array('default' => 'false', 'section'=>'Popup UI');
//$config_meta['asterisk_fop_button_enabled'] = array('default' => 'false', 'section'=>'Popup UI');
$config_meta['asterisk_transfer_button_enabled'] = array('default' => 'true', 'section'=>'Popup UI');
$config_meta['asterisk_relate_to_account_enabled'] = array('default' => 'true', 'section'=>'Popup UI');
$config_meta['asterisk_relate_to_contact_enabled'] = array('default' => 'true', 'section'=>'Popup UI');
$config_meta['asterisk_create_new_contact_enabled'] = array('default' => 'true', 'section'=>'Popup UI');
$config_meta['asterisk_max_popups'] = array('default' => '5', 'section'=>'Popup UI');
$config_meta['asterisk_filtered_call_states'] = array('default' => '', 'section'=>'Popup UI');
$config_meta['asterisk_window_height'] = array('default' => '', 'section'=>'Popup UI');

/*
$config_meta['asterisk_callinize_username'] = array('default' => '', 'section'=>'Callinize');
$config_meta['asterisk_callinize_password'] = array('default' => '', 'section'=>'Callinize');
*/

$config_meta['asterisk_contact_phone_fields'] = array('default' => 'phone_work,phone_home,phone_mobile,phone_other,assistant_phone', 'section' => 'Phone Fields');
$config_meta['asterisk_account_phone_fields'] = array('default' => 'phone_office,phone_alternate', 'section' => 'Phone Fields');

/*
$config_meta['asterisk_recordings_enabled'] = array('default'=> 'false', 'section'=>'Recordings');
$config_meta['asterisk_recordings_path'] = array('default' => '/var/spool/asterisk/monitor', 'section'=>'Recordings');
*/
