<?php
/**
 * Asterisk SugarCRM Integration
 * (c) KINAMU Business Solutions AG 2009
 *
 * Project Home: www.github.com/blak3r/yaai
 *
 * Parts of this code are (c) 2006. RustyBrick, Inc. http://www.rustybrick.com/
 * Parts of this code are (c) 2008 vertico software GmbH
 * Parts of this code are (c) 2009 abcona e. K. Angelo Malaguarnera E-Mail admin@abcona.de
 * Parts of this code are (c) 2011 Blake Robertson http://www.blakerobertson.com
 * Sugarforge: http://www.sugarforge.org/projects/yaai/
 * GitHub: http://www.github.com/blak3r/yaai
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact KINAMU Business Solutions AG at office@kinamu.com
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 */

require_once 'parse.php';
//
// Debug flags
//
$dial_events_log = 'c:/sugarcrm/htdocs/dial_events_log_ast1.html';
$mysql_loq_queries = 0;
$mysql_log_results = 0;
$verbose_log = 0;
$log_memory_usage = 0;
$memory_usage_log_file = "c:\mem_usage.csv";
$memory_usage_log_frequency_secs = 10*60;
$last_memory_log_entry = "";
$last_push_time=0;

dev_clearDialEventsLog();


// All Sugar timestamps are UTC
date_default_timezone_set('UTC');

//
// Say hello, setup include path(s)
//
define('sugarEntry', TRUE);
logLine("\n\n\n******** Asterisk Logger Starting " . getTimestamp() . "**************\n", true);


// Determine SugarCRM's root dir (we'll use this to find the config filez
$scriptRoot = dirname(__FILE__);
$sugarRoot = $scriptRoot . "/../../../";
print "[Config Settings]\n";
print " Sugar root set to [$sugarRoot]\n";
//set_include_path(get_include_path() . PATH_SEPARATOR . $sugarRoot . "include");
//print "# PHP include path set to [" . get_include_path() . "]\n";
// Specify a custom sugar root on commandline when in test mode (I used this to debug from eclipse)
if ($argc > 2) {
    $sugarRoot = $argv[2];
    if (!endsWith($sugarRoot, "/")) {
        $sugarRoot .= "/";
    }
    print " New sugar root is: " . $sugarRoot;
}


//
// Required libraries
//
require_once($sugarRoot . "include/nusoap/nusoap.php");

// Wrapper class auto logins in if session expires.
class SugarSoap extends nusoapclient {

    public $sessionid;
    public $auth_array;

    function __construct($endpoint, $something, $auth_array) {
        $this->auth_array = $auth_array;
        parent::__construct($endpoint, $something);
        $this->login();
    }

    function login() {
        $result = parent::call('login', $this->auth_array);
        $this->sessionid = $result['id'];
        if ($this->sessionid == -1) {
            logLine("! Soap login failed!\n");
            print_r($result);
        }
        return ($result);
    }

    function call($method, $params) {
        $params['session'] = $this->sessionid;
        $result = parent::call($method, $params);
        if (is_array($result) && array_key_exists("error", $result) && $result['error']['number'] != 0) {
            $this->login();
            $result = parent::call($method, $params);
        }
        //print_r($result);
        return ($result);
    }

}

//
// Pull in config file(s)
//
require_once($sugarRoot . 'config.php');
include_once($sugarRoot . 'config_override.php');

logLine(" Logging to: " . $sugar_config['asterisk_log_file'] . "\n");
$asteriskServer = $sugar_config['asterisk_host'];
$asteriskManagerPort = (int) $sugar_config['asterisk_port'];
$asteriskUser = "Username: " . $sugar_config['asterisk_user'] . "\r\n";
$asteriskSecret = "Secret: " . $sugar_config['asterisk_secret'] . "\r\n";
$asteriskMatchInternal = $sugar_config['asterisk_expr'];

// Load localization if available
$locale_path = $sugarRoot . 'custom/modules/Asterisk/language/' . $sugar_config['default_language'] . '.lang.php';
if (file_exists($locale_path)) {
    logLine(" Found Language file for " . $sugar_config['default_language']  );
    include_once $locale_path;
}
else {
    logLine("### WARNING: No language file exists for: " .  $sugar_config['default_language'] . ".  Defaulting to english");
    logLine(" __See https://github.com/blak3r/yaai/wiki/How-to-Translate__ for translation instructions.");
    include_once( $sugarRoot . 'custom/modules/Asterisk/language/en_us.lang.php' );
}

// Make regex pattern compatible with preg_match
if (!startsWith($asteriskMatchInternal, '/')) {
    $asteriskMatchInternal = '/' . $asteriskMatchInternal . '/i';
}

// Fetch Asterisk dialprefix - must strip this from inbound callerIDs if set
$calloutPrefix = isset($sugar_config['asterisk_prefix']) ? $sugar_config['asterisk_prefix'] : "";
echo (" Callout prefix is [$calloutPrefix]\n");

$callinPrefix = isset($sugar_config['asterisk_dialinPrefix']) ? $sugar_config['asterisk_dialinPrefix'] : "";
echo (" Callin prefix is [$callinPrefix]\n");
echo (" Match Internal Regex = $asteriskMatchInternal\n");




//
// Connect to mySQL DB
//
logLine("[Database Connection]\n");
logLine(" Selecting DB Name: {$sugar_config['dbconfig']['db_name']}\n");
$sql_connection = mysql_connect($sugar_config['dbconfig']['db_host_name'], $sugar_config['dbconfig']['db_user_name'], $sugar_config['dbconfig']['db_password']);
$sql_db = mysql_select_db($sugar_config['dbconfig']['db_name']);
// Prune asterisk_log
// Note only use this for development
//mysql_query('DELETE FROM asterisk_log');
// Set all MySQL dates to UTC
mysql_query("SET time_zone='+00:00'");
purgeExpiredEventsFromDb();

// Get SOAP config
$sugarSoapEndpoint = $sugar_config['site_url'] . "/soap.php"; //"/soap.php";
$sugarSoapUser = $sugar_config['asterisk_soapuser'];
$sugarSoapCredential = md5($sugar_config['asterisk_soappass']);

// Here we check if LDAP Authentication is used, if so we must build credential differently
$q = mysql_query('select value from config where category=\'system\' and name=\'ldap_enabled\'');
$r = mysql_fetch_assoc($q);
if ($r['value'] != 1) {
    $sugarSoapCredential = md5($sugar_config['asterisk_soappass']);
} else {
    $q = mysql_query('select value from config where category=\'ldap\' and name=\'enc_key\'');
    $r = mysql_fetch_assoc($q);
    $ldap_enc_key = substr(md5($r['value']), 0, 24);
    $sugarSoapCredential = bin2hex(mcrypt_cbc(MCRYPT_3DES, $ldap_enc_key, $sugar_config['asterisk_soappass'], MCRYPT_ENCRYPT, 'password'));
}

// Added this while loop to keep retrying the soap login b/c I found when I started it through daemon script...
// despite specifying apache as a dependency... it was failing to connect... (this will keep retrying login until it's successful).
// Probably should have this script reload the SOAP config from config.php. Wasn't sure how to do that since it's already been
// included at top of file in require_once.
$successfullyLoggedInThroughSoap = false;
while (!$successfullyLoggedInThroughSoap) {
    //
    // And finally open a SOAP connection to SugarCRM
    //
    logLine("[SOAP Connection]\n");
    logLine(" Trying SOAP login endpoint=[$sugarSoapEndpoint] user=[$sugarSoapUser] password=[$sugarSoapCredential]\n");

    $auth_array = array(
        'user_auth' => array(
            'user_name' => $sugarSoapUser,
            'password' => $sugarSoapCredential
        )
    );
    $soapClient = new SugarSoap($sugarSoapEndpoint . '?wsdl', true, $auth_array); // This method logs in also
    $soapSessionId = $soapClient->sessionid;
    $userGUID = $soapClient->call('get_user_id', array(
        $soapSessionId
            ));

    if (is_array($userGUID) && array_key_exists("error", $userGUID) && $userGUID['error']['number'] != 0) {
        logLine(" **WARNING Unable to make initial SOAP Call " . $userGUID['error']['number'] . " " . $userGUID['error']['name'] . " // " . $userGUID['error']['description'] . "**\n");
        logLine(" Retrying in 5 seconds...\n\n");
        sleep(5);
    }
    // This case might be obsolete at this point...
    else if (empty($userGUID) || empty($soapSessionId) || $userGUID == -1) {
        logLine(" __FATAL: SOAP login failed, something didn't get set by login... check your site_url, and make sure sugarcrm is running. SessionID:" . $soapSessionId . " user=" . $auth_array['user_auth']['user_name'] . " GUID=" . $userGUID . "__\n");
        logLine(" Sleeping for 5 seconds then retrying...\n\n");
        sleep(5);
    } else {
        logLine(" Successfully logged into Sugar via SOAP! SessionId=" . $soapSessionId . " user=" . $auth_array['user_auth']['user_name'] . " GUID=" . $userGUID . "\n");
        $successfullyLoggedInThroughSoap = true;
    }
}



if ($argc > 1 && $argv[1] == "test") {
    // MODIFY these variables to help diagnose misc incompatibilities between your configuration / sugarcrm edition.
    $ext1 = 51;
    $ext2 = 52;
    //$extensions = (51,52,207);
    $phones = array('4102152497', '4108464565', 'sdfasdf', '(267) 222-8385', '2672228385');

    print "Entered test mode!";

    $obj = findSugarObjectByPhoneNumber("4102152497");
    $obj = findSugarObjectByPhoneNumber("4108464565");
    print "findUserByAsteriskExtension(51) returned: " . findUserByAsteriskExtension("51") . "\n";
    print "findUserByAsteriskExtension(207) returned: " . findUserByAsteriskExtension("207") . "\n";
    print "findUserByAsteriskExtension(710) returned: " . findUserByAsteriskExtension('710') . "\n";
    findUserByAsteriskExtension('206');

    foreach ($phones as $currPhone) {
        $obj = findSugarObjectByPhoneNumber($currPhone);
        if ($obj === FALSE) {
            print "!!! DIDN'T FIND OBJ for $currPhone\n";
        } else {
            print " For $currPhone I found: " . $obj['values']['id'] . ' ' . $obj['values']['first_name'] . ' ' . $obj['values']['last_name'] . "\n";
        }
    }

    // Test for memory leaks...
    $cnt = 0;
    gc_enable();
    $GLOBALS['_transient']['static']['nusoap_base']->globalDebugLevel = 0;
    $lastMem = 0;
    while( $cnt++ < 1000 ) {
        $set_entry_params = array(
            'session' => $soapSessionId,
            'module_name' => 'Calls',
            'name_value_list' => array(
                array(
                    'name' => 'name',
                    'value' => $mod_strings['YAAI']['CALL_AUTOMATIC_RECORD']
                ),
                array(
                    'name' => 'status',
                    'value' => $mod_strings['YAAI']['CALL_IN_LIMBO']
                ),
                array(
                    'name' => 'assigned_user_id',
                    'value' => '1'
                )
            )
        );
        $soapResult = $soapClient->call('set_entry', $set_entry_params);
        unset($set_entry_params);
        unset($soapResult);
        $currMem = memory_get_usage() ;

        if( $lastMem > $currMem ) {
            logLine("\n\nmemory usage decreased!!!  $lastMem --> $currMem\n\n");
        }

        $lastMem = $currMem;
        if( $cnt % 10 == 0 ) {
            logLine( "mem usage: " . memory_get_usage() . "\n");
        }
    }
    exit;
}


// BR: Added this while loop to keep loging in to AMI if asterisk goes down.
while (true) {

    /*
    $cString = "tls://ast2.alertus.com:5039";
    $context = stream_context_create();
    //$errno = 0;
    //$errstr = '';
    $amiSocket = stream_socket_client(
        $cString, $errno, $errstr,
        5000, STREAM_CLIENT_CONNECT, $context
    );
    if ($sock === false) {
        print('Error connecting to ami: ' . $errstr);
    }
      //  $amiSocket = fsockopen('tls://' . $asteriskServer, $asteriskManagerPort, $errno, $errstr, 5);
  //  $asteriskManagerPort = 5038;
    */

    logLine("[Asterisk Manager Interface (AMI) Connection]\n");

   $amiSocket = fsockopen($asteriskServer, $asteriskManagerPort, $errno, $errstr, 5); // connect to Asterisk server
    if (!$amiSocket) {
        logLine(" __ ERROR $errno connecting to Asterisk: $errstr __");
        sleep(5); // retry connecting
        continue;
    } else {
        logLine(" Successfully opened socket connection to $asteriskServer:$asteriskManagerPort\n");
    }

    // Here we extract the AMI Version Number and set a varible with it for creating special if cases for special versions of AMI in the future.
    $result = AMI_ReadResponse($amiSocket, 2000000);
    logLine(" AMI Version Info:\n" . markdown_indent($result)); // Prints the AMI Version
    if (preg_match("/Call Manager\/(\d\.\d)/", $result, $ver_matches)) {
        $managerVersion = $ver_matches[1];

        if ($managerVersion === "1.1") {
            logLine(" Supported AMI version: $managerVersion Detected");
        }
        else if($managerVersion === "1.0") {
            logLine("\n __READ!!! You have a partially supported AMI version: $managerVersion Detected__");
            logLine(" __Some features like Ring Groups and Queues probably will not work.__\n\n ");
        }
    } else {
        logLine(" __WARNING: Unable to detect the manager version. Setting to 1.1 and hoping for the best. But, this is probably an error__");
        $managerVersion = "1.1";
    }

    // Perform AMI Login command
    fputs($amiSocket, "Action: Login\r\n");
    fputs($amiSocket, $asteriskUser);
    fputs($amiSocket, $asteriskSecret);
    fputs($amiSocket, "Events: call,hud\r\n\r\n"); // to monitor just call data, for Asterisk Manager 1.0 remove hud
    $result = AMI_ReadResponse($amiSocket, 2000000);
    logLine(" AMI Login action raw response:\n" . markdown_indent($result));
    if (AMI_WasCmdSuccessful($result)) {
        logLine(" AMI Login was a *success!*");
        logLine("Waiting for call events...");
    } else {
        logLine(" __ERROR: AMI Login FAILED__, Depending on your asterisk version helpful info may be above.\n **Check Asterisk Username / Password in config, then verify AMI user has proper permissions in manager.conf**\n\n");
        sleep(5);
        continue;
    }

    $event = '';
    $stack = 0;

    $event_started = false;

    $start = NULL;
    $timeout = ini_get('default_socket_timeout');

    stream_set_timeout($amiSocket, 60); // sets timeout to 60 seconds.
    $consecutiveFailures = 0;
    $queueChannels = array();

    // Keep a loop going to read the socket and parse the resulting commands.
    // Apparently there is no good way to detect if socket is still alive???
    // This is my hack... if we fail 60 times in a row we reconnect to manager...
    // I suspect that fgets will return very quickly if socket error has occurrs
    // So, it'll reach 60 very quickly and then force relogin.
    // Otherwise, every hour it'll just relogin.
    // Perhaps you can check socket error some other way then socket_read?
    // All I know is this reconnect method has made my asteriskLogger a lot more stable.
    while ($consecutiveFailures < 60 && !safe_feof($amiSocket, $start) && (microtime(true) - $start) < $timeout) {
        $buffer = fgets($amiSocket, 4096);
        // echo("# Read " . strlen($buffer) . " " . $buffer . "\n");
        if ($buffer === FALSE) {
            logLine(getTimestamp() . " Patiently Waiting...! (mem_used: " . memory_get_usage() . ")\n");
            purgeExpiredEventsFromDb();
            $consecutiveFailures++;
        } else {
            $consecutiveFailures = 0;
            if ($buffer == "\r\n") { // handle partial packets
                $event_started = false;
                // parse the event and get the result hashtable
                $e = getEvent($event);
                dumpEvent($e); // prints to screen
                //if ($e['Event'] == 'Join' && !empty($e['Queue']) /*&& in_array($e['Queue'], $allowedQueueIds)*/ )
                //{
                // $queueChannels[ AMI_getUniqueIdFromEvent($e) ] = $e['Channel']; // TODO: This array will grow indefinitely... the data put into it is pretty small so probably fine for now but would be best to have a expiration policy.
                // // Easy solution would be to test during the hangup event... IF( isset($queueChannels[ $e['UniqueID'] ] ) remove the index for $e['UniqueID']
                // logLine("Incoming Queue Event, channel = " . $e['Channel']);
                //}

                //
                // Call Event
                //
                if (($e['Event'] == 'Dial' && $e['SubEvent'] == 'Begin') ||
                    ($e['Event'] == 'Join' && !empty($e['Queue'])))
                {
                    purgeExpiredEventsFromDb(); // clears out db of old events... also called when timeouts occcur

                    logLine("! Dial Event src=" . $e['Channel'] . " dest=" . $e['Destination'] . "\n"); //Asterisk Manager 1.1
                    //print "! Dial Event src=" . $e['Source'] . " dest=" . $e['Destination'] . "\n"; //Asterisk Manager 1.0

                    $eChannel = $e['Channel'];

                    // Attempt to make compatible with AMI 1.0
                    if (!empty($e['Source'])) {
                        $eChannel = $e['Source'];
                    }

                    $eDestination = $e['Destination'];

                    if (empty($e['DestUniqueID']) && $e['Event'] == 'Join') {
                        logLine("DestUniqueID is empty, this must be a queue call... or logic error");
                        $e['DestUniqueID'] = AMI_getUniqueIdFromEvent($e); // We set destination id because Join events don't set them, and the destination id is what is used to lookup hangup events.
                    }

                    //
                    // Before we log this Dial event, we create a corresponding object in Calls module.
                    // We'll need this later to record the call when finished, but create it right here
                    // to get the ID
                    //
                    $set_entry_params = array(
                        'session' => $soapSessionId,
                        'module_name' => 'Calls',
                        'name_value_list' => array(
                            array(
                                'name' => 'name',
                                'value' => $mod_strings['YAAI']['CALL_AUTOMATIC_RECORD']
                            ),
                            array(
                                'name' => 'status',
                                'value' => $mod_strings['YAAI']['CALL_IN_LIMBO']
                            ),
                            array(
                                'name' => 'assigned_user_id',
                                'value' => $userGUID
                            )
                        )
                    );
                    $soapResult = $soapClient->call('set_entry', $set_entry_params);
                    //print_r( $soapResult );
                    $callRecordId = $soapResult['id'];
                    logLine("! Successfully created CALL record with id=" . $callRecordId . "\n");

                    $call = NULL;

                    $tmpCallerID = trim(AMI_getCallerIdFromEvent($e)); //Asterisk Manager 1.0 $e['CallerID']
                    // Typically for outbound calls there are NewCallerID events which contain the phone number dialed.
                    // This isn't the case on POTS lines.
                    // The dialstring will be like g0/14101234567 for outbound calls and 14101234567 for inbound
                    // Regex only matches the outbound case since in the inbound case the CallerIDNum variable is set properly.
                    // Note: this cases also seems to happen on the INTERNAL inbound call events to Ring Groups which is harmless.
                    if (!empty($e['Dialstring'])) {
                        if (preg_match("/(.*?\/)N?(\d+)/", $e['Dialstring'], $ds_matches)) {
                            $tmpCallerID = $ds_matches[2];
                            logLine(" CallerID set from Dialstring to: " . $tmpCallerID);
                        }
                    }

                    // Fix for issue on some asterisk 1.8 boxes where CallerId on click to dial is not set. See https://github.com/blak3r/yaai/issues/75
                    if ($tmpCallerID == '<unknown>' && !empty($e['ConnectedLineNum'])) {
                        $tmpCallerID = trim($e['ConnectedLineNum']);

                        // If Call ID is blocked it comes in as "<unknown>"
                        if( $tmpCallerID == "<unknown>") {
                            $tmpCallerID = "BLOCKED";
                        }

                        logLine(" CallerID set from ConnectedLineNum to $tmpCallerID");
                    }

                    if (startsWith($tmpCallerID, $calloutPrefix)) {
                        logLine(" Stripping callout prefix: $calloutPrefix\n");
                        $tmpCallerID = substr($tmpCallerID, strlen($calloutPrefix));
                    }

                    if (startsWith($tmpCallerID, $callinPrefix)) {
                        logLine(" Stripping callin prefix: $calloutPrefix\n");
                        $tmpCallerID = substr($tmpCallerID, strlen($callinPrefix));
                    }

                    logLine(" CallerID is: $tmpCallerID\n");

                    $rgDetectRegex = "/" . $sugar_config['asterisk_rg_detect_expr'] . "/i";
                    $rgCellRingRegex = "/" . $sugar_config['asterisk_rg_cell_ring_expr'] . "/i"; // This detects in a RG when an outside line is called (usually for a cellphone)... for some reason the cell shows up as the Channel (aka the source)... We detect this by finding a number thats at least 7-10 characters long..
                    // Check if both ends of the call are internal (then delete created (** Automatic record **) record)
                    // 2nd condition looks for Local/RG-52-4102152497
                    if ((preg_match($asteriskMatchInternal, $eChannel) && preg_match($asteriskMatchInternal, $eDestination)) ||
                            preg_match($rgDetectRegex, $eDestination) ||
                            preg_match($rgCellRingRegex, $eChannel)) {
                        deleteCall($callRecordId);
                        logLine("INTERNAL call detected, Deleting Call Record $callRecordId\n");

							// FIXME Delete this!  Trying to visualize internal call for RingGroups
                        /*
							$userExtension = extractExtensionNumberFromChannel($eDestination);
                            $inboundExtension = NULL;
                            if (!empty($e['Queue']) ) {
                                $inboundExtension = $e['Queue'];
                            }
                            else {
                                // Extract from eDestination
                                $inboundExtension = extractExtensionNumberFromChannel($eDestination);
                            }
							$query = sprintf("INSERT INTO asterisk_log (asterisk_id, call_record_id, channel, remote_channel, callstate, direction, CallerID, timestamp_call, asterisk_dest_id,user_extension,inbound_extension) VALUES('%s','%s','%s','%s','%s','%s','%s',%s,'%s','%s','%s')", AMI_getUniqueIdFromEvent($e), $callRecordId, $eDestination, $eChannel, 'Dial', 'X', $tmpCallerID, 'FROM_UNIXTIME(' . time() . ')', $e['DestUniqueID'], $userExtension, $inboundExtension);
                            $callDirection = 'Inbound';
                            logLine("INTERNAL FIXME REMOVE state detected... $asteriskMatchInternal is astMatchInternal eChannel= " . $eChannel . ' eDestination=' . $eDestination . "\n");
							mysql_checked_query($query);
                        */

						// HERE We detect if this is the outbound call to a cell phone...
                        /*
						$query = "SELECT * FROM asterisk_log WHERE channel like '%" . $e['ConnectedLineNum'] . "%' AND callerID = '" . $tmpCallerID . "'";
                        $result = mysql_checked_query($query);
                        while ($pd = mysql_fetch_array($result)) {
                            callinize_push("207", $tmpCallerID,$pd['call_record_id'], $e['ConnectedLineNum'] ); // FIXME
                        }
                        */
                    } else {
                        //Asterisk Manager 1.1 (If the call is internal, this will be skipped)
                        if (preg_match($asteriskMatchInternal, $eChannel) && !preg_match($asteriskMatchInternal, $eDestination)) {
                            $userExtension = extractExtensionNumberFromChannel($eChannel);
                            $query = sprintf("INSERT INTO asterisk_log (asterisk_id, call_record_id, channel, remote_channel, callstate, direction, CallerID, timestamp_call,user_extension) VALUES('%s','%s','%s','%s','%s','%s','%s',%s,'%s')", AMI_getUniqueIdFromEvent($e)/*BR: In an attempt to make Dial End work for Outbound calls switching this over to use Unique ID... I have no idea why DestId was used originally... TBD $e['DestUniqueID']*/, $callRecordId, $eChannel, $eDestination, 'NeedID', 'O', $tmpCallerID, 'FROM_UNIXTIME(' . time() . ')', $userExtension);
                            dev_logString("Insert Outbound");
                            $callDirection = 'Outbound';
                            logLine("OUTBOUND state detected... $asteriskMatchInternal is astMatchInternal eChannel= " . $eChannel . ' eDestination=' . $eDestination . "\n");
                        } else if (!preg_match($asteriskMatchInternal, $eChannel)) {
                            $userExtension = extractExtensionNumberFromChannel($eDestination);
                            if( $e['Event'] == 'Join' && !empty($e['Queue'])) {
                                $userExtension = $e['Queue'];
                            }

                            $inboundExtension = NULL;
                            if (!empty($e['Queue']) ) {
                                $inboundExtension = $e['Queue'];
                            }
                            else {
                                // Extract from eDestination
                                $inboundExtension = extractExtensionNumberFromChannel($eDestination);
                            }
                            logLine("  inbound_extension = " . $inboundExtension );

                            $query = sprintf("INSERT INTO asterisk_log (asterisk_id, call_record_id, channel, remote_channel, callstate, direction, CallerID, timestamp_call, asterisk_dest_id,user_extension,inbound_extension) VALUES('%s','%s','%s','%s','%s','%s','%s',%s,'%s','%s','%s')", AMI_getUniqueIdFromEvent($e), $callRecordId, $eDestination, $eChannel, 'Dial', 'I', $tmpCallerID, 'FROM_UNIXTIME(' . time() . ')', $e['DestUniqueID'], $userExtension, $inboundExtension);
                            $callDirection = 'Inbound';
                            dev_logString("Insert Inbound");
                            logLine("Inbound state detected... $asteriskMatchInternal is astMatchInternal eChannel= " . $eChannel . ' eDestination=' . $eDestination . "\n");

							//FIXME REENABLE
                            if( $inboundExtension == "211" || $inboundExtension == "52") {
                                // TODO Fix
                                callinize_push($inboundExtension,$tmpCallerID, $callRecordId, "+14102152497");
                            }
                            else if( $inboundExtension == "216") {
                                // TODO Fix
                                callinize_push($inboundExtension,$tmpCallerID, $callRecordId, "+14153435295");
                            }
							else if( $inboundExtension == "215") {
                                // TODO Fix
                                callinize_push($inboundExtension,$tmpCallerID, $callRecordId, "+14153435295");
                            }
                            else if( $inboundExtension == "217") {
                                // TODO Fix
                                callinize_push($inboundExtension,$tmpCallerID, $callRecordId, "+12026883230");
                            }

                        }
                        mysql_checked_query($query);

                        //
                        // Update CALL record with direction...
                        //
                        $set_entry_params = array(
                            'session' => $soapSessionId,
                            'module_name' => 'Calls',
                            'name_value_list' => array(
                                array(
                                    'name' => 'id',
                                    'value' => $callRecordId
                                ),
                                array(
                                    'name' => 'direction',
                                    'value' => $callDirection
                                )
                            )
                        );

                        $soapResult = $soapClient->call('set_entry', $set_entry_params);
                    }
                }

                //
                // NewCallerID for Outgoing Call
                //
                // NewCallerid == 1.1, Newcallerid == 1.0
                if ($e['Event'] == 'NewCallerid' || $e['Event'] == 'Newcallerid') {
                    $id = AMI_getUniqueIdFromEvent($e);
                    $tmpCallerID = AMI_getCallerIdFromEvent($e);
                    if ((strlen($calloutPrefix) > 0) && (strpos($tmpCallerID, $calloutPrefix) === 0)) {
                        logLine("* Stripping prefix: $calloutPrefix");
                        $tmpCallerID = substr($tmpCallerID, strlen($calloutPrefix));
                    }
                    logLine(" CallerID Changed to: $tmpCallerID\n");
                    $query = "UPDATE asterisk_log SET CallerID='" . $tmpCallerID . "', callstate='Dial' WHERE asterisk_id='" . $id . "'";
                    mysql_checked_query($query);
                }

                //
                // Process "Hangup" events
                // Yup, we really get TWO hangup events from Asterisk! (Even more with Ringgroups)
                // Obviously, we need to take only one of them....
                //
                // Asterisk Manager 1.1
                /*$e['Event'] == 'Hangup'*/
                if ($e['Event'] == 'Dial' && $e['SubEvent'] == 'End')  {
                    $id = AMI_getUniqueIdFromEvent($e);
                    logLine(" In DialEnd... $id");
                    $query = "SELECT direction,bean_module,bean_id,user_extension,inbound_extension FROM asterisk_log WHERE asterisk_dest_id = '$id' OR asterisk_id = '$id'";
                    $result = mysql_checked_query($query);
                    $direction = mysql_fetch_array($result);
                    //var_dump($direction);
                    if ($direction['direction'] == "I") {
                        $callDirection = "Inbound";
                    } else {
                        $callDirection = "Outbound";
                    }
                    if ($callDirection == "Outbound") { //Outbound callhandling
                        //
                        // Fetch associated call record
                        //
                         $callRecord = findCallByAsteriskId($id);
                        if ($callRecord) {
                            logLine("### [$id] FOUND outbound CALL\n");
                            //
                            // update entry in asterisk_log...
                            //
                            $rawData = $callRecord['bitter']; // raw data from asterisk_log
                            $query = sprintf("UPDATE asterisk_log SET callstate='%s', timestamp_hangup=%s WHERE asterisk_id='%s'", //asterisk_dest_id was asterisk_id
                                    'Hangup', 'FROM_UNIXTIME(' . time() . ')', $id);
                            dev_logString("Hungup $id");
                            $updateResult = mysql_checked_query($query);
                            if ($updateResult) {
                                $assignedUser = findUserIdFromChannel($rawData['channel']);

                                //
                                // ... on success also update entry in Calls module
                                //
                                //
                                // Calculate call duration...
                                //
                                $failedCall = FALSE;
                                $hangupTime = time();
                                $callDurationRaw = 0; // call duration in seconds, only matters if timestamp_link != NULL
                                if ($rawData['timestamp_link'] != NULL) {
                                    $callStartLink = strtotime($rawData['timestamp_link']);
                                    $callDurationRaw = $hangupTime - $callStartLink;
                                } else {
                                    $failedCall = TRUE;
                                }
                                $callStart = strtotime($rawData['timestamp_call']);

                                logLine(" [$id] Measured call duration is $callDurationRaw seconds\n");

                                // Recalculate call direction in minutes
                                $callDuration = (int) ($callDurationRaw / 60);
                                $callDurationHours = (int) ($callDuration / 60);
                                //$callDurationMinutes = ceil($callDuration / 60); //voor afronden naar boven.
                                $callDurationMinutes = ($callDuration % 60);

                                //
                                // Calculate final call state
                                //
                                $callStatus = NULL;
                                $callName = NULL;
                                $callDescription = "";

                                // BR: 3/16/2012 I originally had this check to make sure call was longer then 5 seconds... I don't know why. Whey you have callStatus of Missed it creates a task which is undesirable.
                                // So i'm commenting it out. If it's April and I still haven't deleted this comment it's safe to delete this code.
                                //if (!$failedCall) {
                                $callStatus = 'Held';
                                $callName = $mod_strings['YAAI']['ASTERISKLBL_GOING_OUT'];

                                // This means call description was updated through AJAX so lets not overwrite the subject/description already assigned to the call.
                                if (!empty($callRecord['sweet']['description'])) {
                                    $callName = $callRecord['sweet']['name'];
                                    $callDescription = $callRecord['sweet']['description'];
                                }
                                // } else {
                                // $callStatus = 'Missed';
                                // $callName = $mod_strings['YAAI']['CALL_NAME_MISSED'];
                                                                // $callDescription = "{$mod_strings['YAAI']['CALL_DESCRIPTION_MISSED']} ({$e['Cause-txt']}\n";
                                // $callDescription .= "------------------\n";
                                // $callDescription .= sprintf(" %-20s : %-40s\n", $mod_strings['YAAI']['CALL_DESCRIPTION_PHONE_NUMBER'], $rawData['callerID']);
                                                                // if( $rawData['opencnam'] ) {
                                                                // $callName .= " - " . $rawData['opencnam'];
                                                                // $callDescription .= sprintf(" %-20s : %-40s\n", $mod_strings['YAAI']['CALL_DESCRIPTION_CALLER_ID'], $rawData['opencnam']);
                                                                // }
                                // logLine("Adding OUTBOUND Failed Call, id=$id, call_id = " . $callRecord['sweet']['id'] . "\n");
                                // }
                                // Establish Relationships with the Call and Contact/Account
                                $beanID = NULL;
                                $beanType = NULL;
                                $parentID = NULL;
                                $parentType = NULL;
                                if (!empty($direction['bean_id'])) {
                                    logLine("Bean Id already set by callListener to: " . $direction['bean_id'] . "\n");
                                    $beanID = $direction['bean_id'];
                                    $beanType = ucfirst($direction['bean_module']);
                                } else {

                                    $assocAccount = findSugarAccountByPhoneNumber($rawData['callerID']);
                                    if ($assocAccount != FALSE) {
                                        logLine("Found a matching account, relating to account instead of contact\n");
                                        $beanID = $assocAccount['values']['id'];
                                        $beanType = $assocAccount['type'];
                                        $parentType = 'Accounts';
                                        $parentID = $beanID;
                                    } else {
                                        $assoSugarObject = findSugarObjectByPhoneNumber($rawData['callerID']);
                                        $beanID = $assoSugarObject['values']['id'];
                                        $beanType = $assoSugarObject['type'];
                                    }
                                }
                                setRelationshipBetweenCallAndBean($callRecord['sweet']['id'], $beanType, $beanID);

                                if ($beanType == "Contacts" && !empty($beanID)) {
                                    $assocAccount = findAccountForContact($beanID);
                                    if ($assocAccount) {
                                        $parentType = 'Accounts';
                                        $parentID = $assocAccount;
                                    }
                                } else if ($beanType == "Accounts") {
                                    $parentType = "Accounts";
                                    $parentID = $beanID;
                                }

                                //var_dump($parentType);
                                //var_dump($parentID);
                                logLine(" Call start was " . gmdate('Y-m-d H:i:s', $callStart) . "\n");

                                //
                                // ... on success also update entry in Calls module
                                //
                                logLine(" [$id] (OUTBOUND) Now updating record in /Calls/ id=" . $callRecord['sweet']['id'] . "...\n");

                                //print_r($callRecord);
                                logLine("NAME: " . $callRecord['sweet']['name'] . "\n");
                                logLine("DESCRIPTION: " . $callRecord['sweet']['description'] . "\n");

                                gitimg_log("call-out");

                                $soapResult = $soapClient->call('set_entry', array(
                                    'session' => $soapSessionId,
                                    'module_name' => 'Calls',
                                    'name_value_list' => array(
                                        array(
                                            'name' => 'id',
                                            'value' => $callRecord['sweet']['id']
                                        ),
                                        array(
                                            'name' => 'name',
                                            'value' => $callName
                                        ),
                                        array(
                                            'name' => 'duration_hours',
                                            'value' => $callDurationHours
                                        ),
                                        array(
                                            'name' => 'duration_minutes',
                                            'value' => $callDurationMinutes
                                        ),
                                        array(
                                            'name' => 'status',
                                            'value' => $callStatus
                                        ),
                                        array(
                                            'name' => 'description',
                                            'value' => $callDescription
                                        ),
                                        array(
                                            'name' => 'asterisk_caller_id_c',
                                            'value' => $rawData['callerID']
                                        ),

                                        array(
                                            'name' => 'asterisk_user_extension_c',
                                            'value' => $direction['user_extension']
                                        ),

                                        array(
                                            'name' => 'asterisk_inbound_extension_c',
                                            'value' => $direction['inbound_extension']
                                        ),

                                        array(
                                            'name' => 'date_start',
                                            'value' => gmdate('Y-m-d H:i:s', $callStart)
                                        ),
                                        array(
                                            'name' => 'parent_type',
                                            'value' => $parentType
                                        ),
                                        array(
                                            'name' => 'parent_id',
                                            'value' => $parentID
                                        ),
                                        array(
                                            'name' => 'assigned_user_id',
                                            'value' => $assignedUser
                                        )
                                    )
                                ));
                            }
                        } else {
                            logLine("[$id] FAILED TO FIND A CALL (note: there are two hangups per call, so this might not be an error)\n");
                        }
                    } else {
                    //-----------------[ INBOUND HANGUP HANDLING ]----------------------

                        $id = AMI_getUniqueIdFromEvent($e);
                        //
                        // Fetch associated call record
                        //
                        //$callRecord = findCallByAsteriskDestId($id);
                        $callRecord = findCallByAsteriskId($id);

                        if ($callRecord) {

                            //
                            // update entry in asterisk_log...
                            //
                            $rawData = $callRecord['bitter']; // raw data from asterisk_log
                            // 2013 - march removed hangup_cause=%d, hangup_cause_txt='%s'
                            $query = sprintf("UPDATE asterisk_log SET callstate='%s', timestamp_hangup=%s, answered='%s' WHERE asterisk_id='%s'", //asterisk_dest_id was asterisk_id
                                    'Hangup', 'FROM_UNIXTIME(' . time() . ')', was_call_answered($id), $id);
                            dev_logString("Hungup Inbound $id");
                            $updateResult = mysql_checked_query($query);
                            if ($updateResult) {
                                $assignedUser = findUserIdFromChannel($rawData['channel']);

                                //
                                // ... on success also update entry in Calls module
                                //
                                //
                                // Calculate call duration...
                                //
                                $failedCall = FALSE;
                                $hangupTime = time();
                                $callDurationRaw = 0; // call duration in seconds, only matters if timestamp_link != NULL
                                if ($rawData['timestamp_link'] != NULL) {
                                    $callStartLink = strtotime($rawData['timestamp_link']);
                                    $callDurationRaw = $hangupTime - $callStartLink;
                                } else {
                                    $failedCall = TRUE;
                                }
                                $callStart = strtotime($rawData['timestamp_call']);

                                logLine(" Measured call duration is $callDurationRaw seconds\n");

                                // Recalculate call direction in minutes
                                $callDuration = (int) ($callDurationRaw / 60);
                                $callDurationHours = (int) ($callDuration / 60);
                                //$callDurationMinutes = ceil($callDuration / 60); //voor afronden naar boven.
                                $callDurationMinutes = ($callDuration % 60);

                                //
                                // Calculate final call state
                                //
                                $callStatus = NULL;
                                $callName = NULL;
                                $callDescription = "";
                                if (!$failedCall) {
                                    $callStatus = 'Held';
                                    $callName = $mod_strings['YAAI']['ASTERISKLBL_COMING_IN'];

                                    // This means call description was updated through AJAX so lets not overwrite the subject/description already assigned to the call.
                                    if (!empty($callRecord['sweet']['description'])) {
                                        $callName = $callRecord['sweet']['name'];
                                        $callDescription = $callRecord['sweet']['description'];
                                    }
                                } else {
                                    $callStatus = $sugar_config['asterisk_short_call_status']; // User gets to choose if they should be Missed or Held, if Missed then it leaves an open activity which has to be closed.
                                    $callName = $mod_strings['YAAI']['CALL_NAME_MISSED'];
                                    $callDescription = "{$mod_strings['YAAI']['CALL_DESCRIPTION_MISSED']} ({$e['Cause-txt']}\n";
                                    $callDescription .= "------------------\n";
                                    $callDescription .= sprintf(" %-20s : %-40s\n", $mod_strings['YAAI']['CALL_DESCRIPTION_PHONE_NUMBER'], $rawData['callerID']);
                                    if ($rawData['opencnam']) {
                                        $callName .= " - " . $rawData['opencnam'];
                                        $callDescription .= sprintf(" %-20s : %-40s\n", $mod_strings['YAAI']['CALL_DESCRIPTION_CALLER_ID'], $rawData['opencnam']);
                                    }

                                    logLine(" Adding INBOUND Missed (or Failed) Call, id=$id, call_id = " . $callRecord['sweet']['id'] . "\n");
                                    dev_logString(" Adding INBOUND Missed (or Failed) Call, id=$id, call_id = " . $callRecord['sweet']['id'] . "\n");
                                }


                                // Establish Relationships with the Call and Contact/Account
                                $beanID = NULL;
                                $beanType = NULL;
                                $parentID = NULL;
                                $parentType = NULL;
                                if (!empty($direction['bean_id'])) {
                                    logLine("Bean Id already set by callListener to: " . $direction['bean_id'] . "\n");
                                    $beanID = $direction['bean_id'];
                                    $beanType = ucfirst($direction['bean_module']);
                                } else {
                                    $assocAccount = findSugarAccountByPhoneNumber($rawData['callerID']);
                                    if ($assocAccount != FALSE) {
                                        logLine("Found a matching account, relating to account instead of contact\n");
                                        $beanID = $assocAccount['values']['id'];
                                        $beanType = $assocAccount['type'];
                                        $parentType = 'Accounts';
                                        $parentID = $beanID;
                                    } else {
                                        $assoSugarObject = findSugarObjectByPhoneNumber($rawData['callerID']);
                                        $beanID = $assoSugarObject['values']['id'];
                                        $beanType = $assoSugarObject['type'];
                                    }
                                }
                                setRelationshipBetweenCallAndBean($callRecord['sweet']['id'], $beanType, $beanID);

                                if ($beanType == "Contacts" && !empty($beanID)) {
                                    $assocAccount = findAccountForContact($beanID);
                                    if ($assocAccount) {
                                        $parentType = 'Accounts';
                                        $parentID = $assocAccount;
                                    }
                                } else if ( $beanType == "Accounts") {
                                    $parentType = "Accounts";
                                    $parentID = $beanID;
                                }

                                echo ("! Call start was " . gmdate('Y-m-d H:i:s', $callStart) . "\n");

                                //
                                // ... on success also update entry in Calls module
                                //
                                logLine(" Updating record in /Calls/ id=" . $callRecord['sweet']['id'] . "...\n");

                                //print_r($callRecord);
                                logLine(" NAME: " . $callRecord['sweet']['name'] . "\n");
                                logLine(" DESCRIPTION: " . $callRecord['sweet']['description'] . "\n");

                                gitimg_log("call-in");

                                $soapResult = $soapClient->call('set_entry', array(
                                    'session' => $soapSessionId,
                                    'module_name' => 'Calls',
                                    'name_value_list' => array(
                                        array(
                                            'name' => 'id',
                                            'value' => $callRecord['sweet']['id']
                                        ),
                                        array(
                                            'name' => 'name',
                                            'value' => $callName
                                        ),
                                        array(
                                            'name' => 'duration_hours',
                                            'value' => $callDurationHours
                                        ),
                                        array(
                                            'name' => 'duration_minutes',
                                            'value' => $callDurationMinutes
                                        ),
                                        array(
                                            'name' => 'status',
                                            'value' => $callStatus
                                        ),
                                        array(
                                            'name' => 'description',
                                            'value' => $callDescription
                                        ),
                                        array(
                                            'name' => 'asterisk_caller_id_c',
                                            'value' => $rawData['callerID']
                                        ),
                                        array(
                                            'name' => 'asterisk_user_extension_c',
                                            'value' => $direction['user_extension']
                                        ),

                                        array(
                                            'name' => 'asterisk_inbound_extension_c',
                                            'value' => $direction['inbound_extension']
                                        ),
                                        array(
                                            'name' => 'date_start',
                                            'value' => gmdate('Y-m-d H:i:s', $callStart)
                                        ),
                                        array(
                                            'name' => 'parent_type',
                                            'value' => $parentType
                                        ),
                                        array(
                                            'name' => 'parent_id',
                                            'value' => $parentID
                                        ),
                                        array(
                                            'name' => 'assigned_user_id',
                                            'value' => $assignedUser
                                        )
                                    )
                                        ));
                            } // End Inbound Case
                            // In case of multiple extensions when a call is not answered, every extensions produces a failed call record,
                            // this will keep the first of those records but delete the rest. (LIMIT 1,999999999999 in query returns all but first match.)
                            $query = "SELECT asterisk_id FROM asterisk_log WHERE asterisk_id='$id'";
                            $result = mysql_checked_query($query);
                            $result_id = mysql_fetch_array($result);
                            logLine("Cleaning up Failed Calls part1, asterisk_id = " . $result_id['asterisk_id'] . "\n");

                            $query = "SELECT call_record_id FROM asterisk_log WHERE asterisk_id='" . $result_id['asterisk_id'] . "' ORDER BY id ASC LIMIT 1, 999999999999";
                            $result = mysql_checked_query($query);

                            while ($call_record_id = mysql_fetch_array($result)) {
                                $query = "DELETE FROM calls WHERE id='" . $call_record_id['call_record_id'] . "' AND name LIKE '{$mod_strings['YAAI']['CALL_NAME_MISSED']}%'";
                                $rq = mysql_checked_query($query);

                                if (mysql_affected_rows() > 0) {
                                    logLine("Cleaning up Failed Calls part2, DELETED call_record_id = {$call_record_id['call_record_id']}\n");
                                    // TODO Change this to Delete Call
                                    $query = "DELETE FROM calls_cstm WHERE id_c='{$call_record_id['call_record_id']}'";
                                    dev_logString("Deleting Call Rec: " . $call_record_id['call_record_id'] );

                                    mysql_checked_query($query);
                                }
                                //$total_result = mysql_fetch_array($rq);
                                //var_dump($total_result);
                            }
                        } // End if callRecordId
                    } // End if INBOUND hangup event
                }// End of HangupEvent.
                // success
                //Asterisk Manager 1.1
                if ($e['Event'] == 'Bridge') {
                    logLine("DEBUG: Entered Bridge");
                    $query = "SELECT direction, callstate FROM asterisk_log WHERE asterisk_id='" . $e['Uniqueid2'] . "' OR asterisk_dest_id='" . $e['Uniqueid2'] . "'" . " OR asterisk_id='" . $e['Uniqueid1'] . "' OR asterisk_dest_id='" . $e['Uniqueid1'] . "'"; // Second half of this is for outgoing
                    $result = mysql_checked_query($query);
                    $direction = mysql_fetch_array($result);
                    if( $direction['callstate'] != "Connected" ) {
                        if ($direction['direction'] == "I") {
                            $callDirection = "Inbound";
                            logLine("DEBUG: bridge inbound, updating the Link state");
                            // Inbound bridge event
                            $query = "UPDATE asterisk_log SET callstate='Connected', timestamp_link=FROM_UNIXTIME(" . time() . ") WHERE asterisk_dest_id='" . $e['Uniqueid1'] . "' OR asterisk_dest_id='" . $e['Uniqueid2'] . "'";
                            dev_logString("Set callState = Connected IBC");
                            $rc = mysql_checked_query($query);

                            // Delete all the extra inbound records
                            $id1 = $e['Uniqueid1'];
                            $id2 = $e['Uniqueid2'];
                            $query = "SELECT call_record_id FROM asterisk_log WHERE asterisk_id='" . $id1 . "' AND asterisk_dest_id!='" . $id2 . "'";
                            $result = mysql_checked_query($query);
                            while ($call_rec_id = mysql_fetch_array($result)) {
                                logLine("Deleting Call Record: " . $call_rec_id['call_record_id']);
                                deleteCall($call_rec_id['call_record_id']);
                            }
                        } else if($direction['direction'] == "O") {
                            $query = "UPDATE asterisk_log SET callstate='Connected', timestamp_link=FROM_UNIXTIME(" . time() . ") WHERE asterisk_id='" . $e['Uniqueid1'] . "' OR asterisk_id='" . $e['Uniqueid2'] . "'";
                            dev_logString("Set callState = Connected OBC");
                            $rc = mysql_checked_query($query);
                        }
                    }

                    // Here we add support for complicated Ring Groups such as x1 ---> 615 ---> 710,722,735
                    // \--> 620 ---> 810,811,812
                    // Check if both channels are internal... Then, check the asterisk_log table to see if an entry exists where Channel matches one of them... if so then change it out.
                    // TBD: does answering on a cell phone and not pressing 1 to accept cause a bridge event that messes this up?
                    if (isCallInternal($e['Channel1'], $e['Channel2'])) {
                        logLine("Internal Bridge Event Detected\n");
                        if (preg_match('/(.*);(.*)/', $e['Channel1'], $matches)) {
                            $chanToFind = $matches[1] . '%';
                            $query = "SELECT id FROM asterisk_log WHERE channel like '$chanToFind' and direction='I' ";
                            logLine("Internal: $query\n");
                            $result = mysql_checked_query($query);
                            // TODO clean up all these logLines.
                            if (mysql_num_rows($result) > 1) {
                                logLine("RG-Bridge ERROR: MULTIPLE MATCHING LINES IN ASTERISK LOG... BRIDGE LOGIC ISN'T BULLETPROOF\n");
                            } else if (mysql_num_rows($result) == 1) {
                                logLine(" RG-Bridge Detected changing the channel to: {$e['Channel2']}\n");
                                $result_id = mysql_fetch_array($result);
                                $chan2 = $e['Channel2'];
                                $theId = $result_id['id'];
                                $userExtension = extractExtensionNumberFromChannel($chan2);
                                $query = "UPDATE asterisk_log SET channel='$chan2', user_extension='$userExtension' WHERE id='$theId'";
                                dev_logString("RG Bridge Set Channel of $theId to $chan2");
                                logLine("UPDATE QUERY: $query\n");
                                mysql_checked_query($query);
                            } else {
                                logLine("DEBUG: RG-BRIDGE ROWS found");
                            }
                        } else {
                            logLine("RG-Bridge didn't match regex.\n");
                        }
                    } else {
                        logLine("Bridge isn't internal to internal");

                        // This could be a call queue event...
                        //--
                        logLine("Internal Queue Event Detected\n");
                        // if( preg_match('/(.*);(.*)/',$e['Channel1'],$matches) ) {
                        $chanToFind = $e['Channel1'];
                        $query = "SELECT id FROM asterisk_log WHERE remote_channel like '$chanToFind' and direction='I' and (channel = '' OR channel is NULL) ";
                        logLine("Internal Queue: $query\n");
                        $result = mysql_checked_query($query);
                        // TODO clean up all these logLines.
                        if (mysql_num_rows($result) > 1) {
                            logLine("Queue-Bridge ERROR: MULTIPLE MATCHING LINES IN ASTERISK LOG... BRIDGE LOGIC ISN'T BULLETPROOF\n");
                        } else if (mysql_num_rows($result) == 1) {
                            logLine(" Queue-Bridge Detected changing the channel to: {$e['Channel2']}\n");
                            $result_id = mysql_fetch_array($result);
                            $chan2 = $e['Channel2'];
                            $user_device = extractUserDeviceFromChannel($e['Channel2']);
                            $theId = $result_id['id'];
                            $userExtension = extractExtensionNumberFromChannel($chan2);
                            $query = "UPDATE asterisk_log SET channel='$chan2', user_extension='$userExtension', user_device='$user_device' WHERE id='$theId'";
                            dev_logString("Queue-Bridge Set Channel to $chan2 for $theId\n");
                            logLine("Queue UPDATE QUERY: $query\n");
                            mysql_checked_query($query);
                        } else {
                            logLine("DEBUG: NO Queue BRIDGE ROWS found");
                        }
                        //}
                        //else {
                        // logLine("Queue-Bridge didn't match regex.\n");
                        //}
                    }
                }

               //Asterisk Manager 1.0 (Only)... This is the equivalent of bridge
               if($e['Event'] == 'Link')
               {
                  $query = "UPDATE asterisk_log SET callstate='Connected', timestamp_link=FROM_UNIXTIME(".time().") WHERE asterisk_id='" . $e['Uniqueid1'] . "' OR asterisk_id='" . $e['Uniqueid2'] . "'";
                  $rc = mysql_checked_query($query);
                  // NOTE: AMI v1.0 will not support Ring Groups and Queues like AMI v1.1 does until it's ported.
               };

// Reset event buffer
                $event = '';
            }
        }

        // handle partial packets
        if ($event_started) {
            $event .= $buffer;
        } else if (strstr($buffer, 'Event:')) {
            $event = $buffer;
            $event_started = true;
        }


        if($log_memory_usage)
        {
            $diff = time() - $last_memory_log_entry;
            if( $diff > $memory_usage_log_frequency_secs ) {
                logLine( getTimestamp() . "," . memory_get_usage(), $memory_usage_log_file);
                $last_memory_log_entry = time();
            }
        }

        // for if the connection to the sql database gives out.
        // TODO Find a better way to check the connection. I think on Shared Hosting Servers mysql_ping might be disabled which causes this to always reconnect.
        if (!mysql_ping($sql_connection)) {
            //here is the major trick, you have to close the connection (even though its not currently working) for it to recreate properly.
            logLine("__MySQL connection lost, reconnecting__\n");
            mysql_close($sql_connection);
            $sql_connection = mysql_connect($sugar_config['dbconfig']['db_host_name'], $sugar_config['dbconfig']['db_user_name'], $sugar_config['dbconfig']['db_password']);
            $sql_db = mysql_select_db($sugar_config['dbconfig']['db_name']);
        }
    }

    logLine(getTimestamp() . "Event loop terminated, attempting to login again\n");
    sleep(1);
}

// TODO i've seen asteriskLogger crash due to a script executing too long error... It was on a pretty budget shared hosting server.
// Hasn't ever happened to me personally... but still something noteworthy.

exit(0);

// ******************
// Helper functions *
// ******************

    /**
     * Method which initiates the callinize push notification to cell phones.
     * @param $inboundExtension - this is the cell phone number to send push notification to
     * @param $phone_number - this is the number of the person calling.
     * @param $call_record_id - record id of the call.
     * @param $cell_number - is the phone number who's being called.
     * @return bool
     */
    function callinize_push($inboundExtension,$phone_number, $call_record_id, $cell_number)
    {
        global $sugar_config;
        global $last_push_time;
        $now_time = time();
        $duration = abs($last_push_time - $now_time);
        if( strlen($inboundExtension) < 4 && $duration > 10) {
            $last_push_time = time();
            logLine(getTimeStamp() . "### Callinize START ####");
            logLine(getTimeStamp() . "Last Push was $duration secs ago");
			mt_start();

            $assocAccount = findSugarAccountByPhoneNumber($phone_number);
            if ($assocAccount != FALSE) {
                logLine("Found a matching account, relating to account instead of contact\n");
                $beanID = $assocAccount['values']['id'];
                $beanType = $assocAccount['type'];
                $parentType = 'Accounts';
                $parentID = $beanID;
            } else {
                $assoSugarObject = findSugarObjectByPhoneNumber($phone_number);
                $beanID = $assoSugarObject['values']['id'];
                $beanType = $assoSugarObject['type'];
            }
            // FIXME doesn't handle multiple matching contacts right now.
            logLine(" Details:  callId:" . $call_record_id . " beanId:" . $beanID . " " . $beanType . " x" . $inboundExtension . " targetPhone: " . $cell_number);

            // GET Contact Info
            $sql = "select contacts.*, accounts.name from contacts left join accounts_contacts on contacts.id = accounts_contacts.contact_id left join accounts on accounts_contacts.account_id = accounts.id where contacts.id = '$beanID'";
            $queryResult = mysql_checked_query($sql);
            if ($queryResult === FALSE) {
                logLine("! Contact lookup failed");
                return FALSE;
            }
            else {
                $row = mysql_fetch_assoc($queryResult);
				
				$emailSql = "select email_addresses.email_address from email_addr_bean_rel left join email_addresses on email_addr_bean_rel.email_address_id = email_addresses.id where email_addr_bean_rel.bean_id = '" . $row['id'] . "'";
				logLine("Email SQL: " . $emailSql);
				$queryEmailResult = mysql_checked_query($emailSql);
				$emailRow = mysql_fetch_assoc($queryEmailResult);
            }
			

			$dur_search = mt_end();

            // TODO Finalize exactly what we let the backend handle... $body below is all the contact info.
            $body = json_encode($row);

            $c = array();

            // Actions
            $c['action'] = 'add';
            $c['send_push'] = 'true';

            // Organization Credentials
            $c['organizationId'] =  $sugar_config['asterisk_callinize_api_organizationId'];
            $c['organizationSecret'] = $sugar_config['asterisk_callinize_api_organizationSecret'];


            // Call Table Stuff
            $c['callerName'] = $row['first_name'] . " " . $row['last_name'];
            $c['callerAccountName'] = empty($row['name']) ? $row['department'] : $row['name'];  // TODO remove department here.
            $c['callerShortInfo'] = $row['description']; // TODO change this to be the last note logged.
			$c['callerDescription'] = $row['description'];
			//$c['callerLongInfo'] = 
			$c['callerEmail'] =  $emailRow['email_address'];//"todo@todotown.com";
            $c['callerTitle'] = $row['title'];
            $c['callerCrmId'] = $row['id'];
            $c['crmCallRecordId'] = $call_record_id;
            $c['callerPhone'] = $phone_number;  // e164 TODO
            $c['userPhone'] = $cell_number; // e164 TODO
            $c['inboundExtension'] = $inboundExtension;
            //$c['provider'] = "parse";

            logLine( print_r($row,true) );

            // Creates the message for the push notification
            if( !empty($row['first_name']) ) {
                $pushMessage = "x$inboundExtension: {$row['first_name']} {$row['last_name']},{$row['title']}\n{$c['caller_account']}\n{$row['description']}";
                $c['contactCount'] = 1;
				$dur_opencnam = "N/A";
            }
            else {
                require_once 'include/opencnam.php';
                $opencnam = new opencnam($sugar_config['asterisk_opencnam_account_sid'], $sugar_config['asterisk_opencnam_auth_token']);
                logLine(getTimeStamp() . " opencnam Start");
                mt_start();
                $callerid = $opencnam->fetch($phone_number);
				$dur_opencnam = mt_end();
                logLine(getTimeStamp() . " OpenCNAM took: " . $dur_opencnam);
                $callerIdInfo = "";
                if( !empty($callerid) ) {
                    $callerIdInfo = "CallerID: " . $callerid;
                }
                $pushMessage = "x$inboundExtension: Not in CRM\n" . $callerIdInfo;
                $c['contactCount'] = 0;
            }
            $c['message'] = $pushMessage;
            $c['searchTime'] = intval($dur_search*1000);

            // Moved delay to parse
			//mt_start();
			//$sleep = (4.5-$dur_search)*1000000;
			//usleep($sleep);
			//$dur_sleep = mt_end();
            $dur_sleep = 0;
            logLine( print_r($c, true) );
            $parse = new ParseBackendWrapper();
            mt_start();
			
		
            $add_call_resp = $parse->customCodeMethod('manage_calls', $c);
			$dur_parse = mt_end();
            dev_logString("Callinize push took " . $dur_parse . "s response: " . $add_call_resp );
            //$add_call_resp = $parse->customCodeMethod('add_call', $c);
            logLine(getTimeStamp() . " Add Call Response: " . $add_call_resp . "\nTook: $dur_parse");
            //$send_push_resp = $parse->customCodeMethod('send_push', $c);
            //logLine("Send Push Response: " . $send_push_resp);
			logLine(getTimeStamp() . " Callinize Timing: search: $dur_search opencnam: $dur_opencnam parse: $dur_parse  delay=$dur_sleep");
    }
    else {
        logLine("Callinize Push Surpressed... last push was $duration secs ago");
    }
}

/**
 * Removes calls from asterisk_log that have expired or closed.
 * 1) Call Popup Closed Manually by user in sugar.
 * 2) Call has been hungup for at least an hour
 * 3) Call was created over 5 hours ago (this is just in case of bugs where hangup isn't set for some reason).
 */
function purgeExpiredEventsFromDb() {
    global $sugar_config;
    $popupsExpireMins = 60;
    if( !empty( $sugar_config['asterisk_hide_call_popups_after_mins'] ) ) {
        $popupsExpireMins = $sugar_config['asterisk_hide_call_popups_after_mins'];
    }

    $calls_expire_time = date('Y-m-d H:i:s', time() - ($popupsExpireMins * 60) );
    $five_hours_ago = date('Y-m-d H:i:s', time() - 5 * 60 * 60);

    // BR: 2013-04-30 fixed bug where closing the call popup before the call was over the duration would potentially not get set right.
    $query = " DELETE FROM asterisk_log WHERE (uistate = 'Closed' AND timestamp_hangup is not NULL) OR ( timestamp_hangup is not NULL AND '$calls_expire_time' > timestamp_hangup ) OR ('$five_hours_ago' > timestamp_call )";
    mysql_checked_query($query);
    $rowsDeleted = mysql_affected_rows();
   // logLine("DEBUG: $query");
    if( $rowsDeleted > 0 ) {
        logLine("  Purged $rowsDeleted row(s) from the call log table.");
    }
}

function isCallInternal($chan1, $chan2) {
    global $asteriskMatchInternal;
    return (preg_match($asteriskMatchInternal, $chan1) && preg_match($asteriskMatchInternal, $chan2));
}

// go through and parse the event
function getEvent($event) {
    $e = array();
    $e['Event'] = '';

    $event_params = explode("\n", $event);

    foreach ($event_params as $event) {
        if (strpos($event, ": ") > 0) {
            list($key, $val) = explode(": ", $event);
            // $values = explode(": ", $event);
            $key = trim($key);
            $val = trim($val);

            if ($key) {
                $e[$key] = $val;
            }
        }
    }
    return ($e);
}

function getTimestamp() {
    return date('[Y-m-d H:i:s]');
}

function dumpEvent(&$event) {
    // Skip 'Newexten' events - there just toooo many of 'em || For Asterisk manager 1.1 i choose to ignore another stack of events cause the log is populated with useless events
    $eventType = $event['Event'];

    // Not surpressing new channel
    // $eventType == 'Newchannel' ||
    if ($eventType == 'Newexten' || $eventType == 'UserEvent' || $eventType == 'AGIExec' ||  $eventType == 'Newstate' || $eventType == 'ExtensionStatus') {
        logLine("! AMI Event '" . $eventType . " suppressed.\n");
        
		if( $eventType == 'Newexten') {
			dumpEventHelper($event, "c:/newexten.log");
		}
        return;
    }

	switch($eventType) {
        case "Dial":    dev_DialPrinter($event); break;
        case "Bridge":  dev_BridgePrinter($event); break;
        case "Join":    dev_JoinPrinter($event); break;
        case "Hangup":  dev_HangupPrinter($event); break;
        case "Newchannel": dev_NewChannelPrinter($event); break;
    }

    dumpEventHelper($event);
}

function dumpEventHelper(&$event, $logFile = "default" ) {
    logLine(getTimeStamp() . "\n", $logFile);
    logLine("! --- Event -----------------------------------------------------------\n",$logFile);
    foreach ($event as $eventKey => $eventValue) {
        logLine(sprintf("! %20s --> %-50s\n", $eventKey, $eventValue),$logFile);
    }
    logLine("! ---------------------------------------------------------------------\n", $logFile);
}

function dev_DialPrinter(&$e) {
	dev_GenericEventPrinter("Dial", $e['SubEvent'], $e['UniqueID'], $e['DestUniqueID'], $e['Channel'], $e['Destination'], $e["CallerIDNum"], $e['DialString']);
}

function dev_BridgePrinter(&$e) {
   dev_GenericEventPrinter("Bridge", $e['Bridgestate'], $e['Uniqueid1'], $e['Uniqueid2'], $e['Channel1'], $e['Channel2'], $e["CallerID1"], $e['CallerID2']);
}

function dev_JoinPrinter(&$e) {
    dev_GenericEventPrinter("Join", $e['Position'], $e['Uniqueid'], "--", $e['Channel'], "--", $e["CallerIDNum"], $e['Queue']);
}

function dev_HangupPrinter(&$e) {
    dev_GenericEventPrinter("Hangup", $e['Cause'], $e['Uniqueid'], '--', $e['Channel'], '--', $e["CallerIDNum"], $e['ConnectedLineNum']);
}

function dev_NewChannelPrinter(&$e) {
    dev_GenericEventPrinter("NewChan", $e['ChannelStateDesc'], $e['Uniqueid'], '--', $e['Channel'], '--', $e["CallerIDNum"], $e['Exten']);
}

function dev_logString($str) {
    global $dial_events_log;
    logLine( $str, $dial_events_log);
}

function dev_clearDialEventsLog() {
    global $dial_events_log;
    $fp = fopen($dial_events_log, 'w');
    $theHtml = <<<HTML_HEAD
<html><head></head><body>
<div style="font-family: monospaced;">

HTML_HEAD;

    fclose($fp);
}



function dev_GenericEventPrinter($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8) {
    global $dial_events_log;
    if( !empty($dial_events_log) ) {
        $s = getTimeStamp() . " ";
        $s .= str_pad($arg1, 8, " ", STR_PAD_BOTH);
        $s .= str_pad($arg2, 6, " ", STR_PAD_BOTH);
        $s .= str_pad($arg3, 16, " ", STR_PAD_BOTH);
        $s .= str_pad($arg4, 16, " ", STR_PAD_BOTH);
        $s .= str_pad($arg5, 55, " ", STR_PAD_BOTH);
        $s .= str_pad($arg6, 55, " ", STR_PAD_BOTH);
        $s .= str_pad($arg7, 17, " ", STR_PAD_BOTH);
        $s .= str_pad($arg8, 20, " ", STR_PAD_BOTH);
        logLine( $s, $dial_events_log . ".txt");

        $s = '<div style="font-size:90%;white-space:nowrap;"><span style="font-family:monospace;">' . date('[H:i:s]') . "</span>";

        $s .= colorize(str_pad($arg1, 8, "_", STR_PAD_BOTH));
        $s .= colorize(str_pad($arg2, 6, "_", STR_PAD_BOTH));
        $s .= colorize(str_pad($arg3, 16, "_", STR_PAD_BOTH));
        $s .= colorize(str_pad($arg4, 16, "_", STR_PAD_BOTH));
        $s .= colorize(str_pad($arg5, 55, "_", STR_PAD_BOTH));
        $s .= colorize(str_pad($arg6, 55, "_", STR_PAD_BOTH));
        $s .= colorize(str_pad($arg7, 17, "_", STR_PAD_BOTH));
        $s .= colorize(str_pad($arg8, 20, "_", STR_PAD_BOTH));
        $s = preg_replace("/_/", "&nbsp;", $s);
        $s =
        logLine( $s ."</div>", $dial_events_log);
    }

}

/**
 * Takes a string, does a md5 hash of it to get a random color, then sets background to white or black to enhance readability.
 * @param $str
 * @return string
 */
function colorize($str) {
    $hash = md5($str);
    $forecolor = substr($hash,0,6);
    $backcolor = "FFFFFF";
    if( hexdec($forecolor) > hexdec("CCCCCC") ) {
        //$backcolor = "000000";
    }

    return "<span style=\"font-family:monospace;color: #$forecolor;background-color:$backcolor\">$str</span>";
}



/**
 * Removes a call record from the database.
 * @param $callRecordId - Call Record ID. Note: param is assumed to be sanitized.
 */
function deleteCall($callRecordId) {
    // NOTE: there is one other place in this file that Delete's a call, so if this code is ever refactored
    // to use SOAP, be sure to refactor that one.
    $query = "DELETE FROM calls WHERE id='$callRecordId'";
    if( mysql_checked_query_returns_affected_rows_count($query) > 0 ) {
        dev_logString("Deleted " . mysql_affected_rows() . " rows from calls");
        $query = "DELETE FROM calls_cstm WHERE id='$callRecordId'";
        mysql_checked_query($query);
    }

    $query = "DELETE FROM asterisk_log WHERE call_record_id='$callRecordId'";
    if( mysql_checked_query_returns_affected_rows_count($query) > 0 ) {
        dev_logString("Deleted " . mysql_affected_rows() . " rows from asterisk_log");
    }
}

//
// Locate associated record in "Calls" module
//
function findCallByAsteriskId($asteriskId) {
    global $soapClient, $soapSessionId;
    logLine("# +++ findCallByAsteriskId($asteriskId)\n");

    //
    // First, fetch row in asterisk_log...
    //

    $sql = sprintf("SELECT * from asterisk_log WHERE asterisk_id='$asteriskId'", $asteriskId);
    $queryResult = mysql_checked_query($sql);
    if ($queryResult === FALSE) {
        logLine("Asterisk ID NOT FOUND in asterisk_log (db query returned FALSE)");
        return FALSE;
    }

    while ($row = mysql_fetch_assoc($queryResult)) {
        $callRecId = $row['call_record_id'];
        logLine("! Found entry in asterisk_log recordId=$callRecId\n");

        //
        // ... then locate Object in Calls module:
        //
        $soapResult = $soapClient->call('get_entry', array(
            'session' => $soapSessionId,
            'module_name' => 'Calls',
            'id' => $callRecId
                ));
        $resultDecoded = decode_name_value_list($soapResult['entry_list'][0]['name_value_list']);
        // echo ("# ** Soap call successfull, dumping result ******************************\n");
        // var_dump($soapResult);
        // var_dump($resultDecoded);
        // var_dump($row);
        // echo ("# ***********************************************************************\n");
        //
        // also store raw sql data in case we need it later...
        //
        return array(
            'bitter' => $row,
            'sweet' => $resultDecoded
        );
    }
    logLine("! Warning, results set was empty!\n");
    return FALSE;
}

// AsteriskManager 1.1 for inbound calling
function findCallByAsteriskDestId($asteriskDestId) {
    global $soapClient, $soapSessionId, $verbose_logging;
    logLine("# +++ findCallByAsteriskDestId($asteriskDestId)\n");

    //
    // First, fetch row in asterisk_log...
    //

    $sql = sprintf("SELECT * from asterisk_log WHERE asterisk_dest_id='$asteriskDestId'", $asteriskDestId);
    $queryResult = mysql_checked_query($sql);
    if ($queryResult === FALSE) {
        return FALSE;
    }

    while ($row = mysql_fetch_assoc($queryResult)) {
        $callRecId = $row['call_record_id'];
        logLine("! FindCallByAsteriskDestId - Found entry in asterisk_log recordId=$callRecId\n");

        //
        // ... then locate Object in Calls module:
        //
        $soapResult = $soapClient->call('get_entry', array(
            'session' => $soapSessionId,
            'module_name' => 'Calls',
            'id' => $callRecId
                ));
        $resultDecoded = decode_name_value_list($soapResult['entry_list'][0]['name_value_list']);

// echo ("# ** Soap call successfull, dumping result ******************************\n");
        // var_dump($soapResult);
        if ($verbose_logging) {
            var_dump($resultDecoded);
        }
        // var_dump($row);
        // echo ("# ***********************************************************************\n");
        //
        // also store raw sql data in case we need it later...
        //
        return array(
            'bitter' => $row,
            'sweet' => $resultDecoded
        );
    }
    logLine("! Warning, FindCallByAsteriskDestId results set was empty!\n");
    return FALSE;
}

//
// Repacks a name_value_list eg returned by get_entry() into a hash (aka associative array in PHP speak)
//
function decode_name_value_list(&$nvl) {
    $result = array();

    foreach ($nvl as $nvlEntry) {
        $key = $nvlEntry['name'];
        $val = $nvlEntry['value'];
        $result[$key] = $val;
    }
    return $result;
}

//
// Attempt to find a Sugar Account with a matching phone number.
//
function findSugarAccountByPhoneNumber($origPhoneNumber) {
    global $soapClient, $soapSessionId, $sugar_config;
    logLine("# +++ find AccountByPhoneNumber($origPhoneNumber)\n");

    if( empty($sugar_config['asterisk_account_phone_fields']) ) {
        logLine("  Account Phone Fields are ignored.  Returning...");
        return FALSE;
    }

    $aPhoneNumber = preg_replace('/\D/', '', $origPhoneNumber); // removes everything that isn't a digit.
    if (preg_match('/([0-9]{' . $sugar_config['asterisk_digits_to_match'] . '})$/', $aPhoneNumber, $matches)) {
        $aPhoneNumber = $matches[1];
    }

    if( strlen($aPhoneNumber) < 5 ) {
        logLine("Phone number is invalid/too short, CallerID is most likely blocked" );
        return FALSE;
    }

    $regje = preg_replace('/(\d)/', '$1\[^\\d\]*', $aPhoneNumber);
    $regje = '(' . $regje . ')$';

    // TODO make this dynamic from config (see contacts below for example)
    $soapArgs = array(
        'session' => $soapSessionId,
        'module_name' => 'Accounts',
        'query' => "accounts.phone_office REGEXP '$regje' OR accounts.phone_alternate REGEXP '$regje'",
    );

    // print "--- SOAP get_entry_list() ----- ARGS ----------------------------------------\n";
    // var_dump($soapArgs);
    // print "-----------------------------------------------------------------------------\n";

    $soapResult = $soapClient->call('get_entry_list', $soapArgs);

    //print "--- SOAP get_entry_list() FOR GET CONTACT ------------------------------------\n";
    //var_dump($soapResult);
    //print "-----------------------------------------------------------------------------\n";

    if (!isSoapResultAnError($soapResult)) {
        $resultDecoded = decode_name_value_list($soapResult['entry_list'][0]['name_value_list']);
        //print "--- Decoded get_entry_list() FOR GET CONTACT --------------------------------------\n";
        //var_dump($resultDecoded);
        //print "-----------------------------------------------------------------------------\n";
        return array(
            'type' => 'Accounts',
            'values' => $resultDecoded
        );
    }

    return FALSE;
}

//
// Attempt to find a Sugar object (Contact,..) by phone number
//
// NOTE: As of v2.2, callListener now updates a column in asterisk_log table with bean id so it doesn't have to perform
// a complex query each time. But, since callListener only works when you're logged into sugar and have "Call Notification" on...
// we still have to try and find object related to phone number here for the other cases.
//
//
function findSugarObjectByPhoneNumber($aPhoneNumber) {
    global $soapClient, $soapSessionId, $sugar_config;
    logLine("### +++ find ContactByPhoneNumber($aPhoneNumber)\n");

    if( empty($sugar_config['asterisk_contact_phone_fields']) ) {
        logLine("  Contact Phone Fields are ignored.  Returning...");
        return FALSE;
    }

    // Add if phonenumber .length == 10
    $searchPattern = $aPhoneNumber;
    //$searchPattern = regexify($aPhoneNumber);
    //
    // Plan A: Attempt to locate an object in Contacts
    // $soapResult = $soapClient->call('get_entry' , array('session' => $soapSessionId, 'module_name' => 'Calls', 'id' => $callRecId));
    //


    //************ CID.agi
    // =~ <--- use the left side as input string to regex.
    // s -- replace.
    //
    //$number =~ s/\D//g;
    //$number =~ m/([0-9]{7})$/;
    //$number = $1 if ($1);
    //
    //if ($1) {
    // $regje = $number;
    // $regje =~ s/(\d)/$1\[^\\d\]*/g;
    // $regje = '(' . $regje . ')' . '$';
    //} elsif($number) {
    // $regje = '^' . $number . '$';
    //} else {
    // debugAndQuit("No caller ID found for this call");
    //}
    //
    //debug("Searching for regexp $regje",5);
    //
    //# lookup the number @ contacts
    //$result = $service->get_entry_list($sid,"Contacts","contacts.phone_home REGEXP '$regje' OR contacts.phone_mobile REGEXP '$regje' OR contacts.phone_work REGEXP '$regje' OR contacts.phone_other REGEXP '$regje' OR contacts.phone_fax REGEXP '$regje'","",0,{a=>"first_name",b=>"last_name",c=>"account_name"},1,0)->result;
    //$id = $result->{entry_list}[0]{id};
    // TODO figure out what that 2nd case could be the elseif part...

    $aPhoneNumber = preg_replace('/\D/', '', $aPhoneNumber); // removes everything that isn't a digit.
    if (preg_match('/([0-9]{' . $sugar_config['asterisk_digits_to_match'] . '})$/', $aPhoneNumber, $matches)) {
        $aPhoneNumber = $matches[1];
    }

    if( strlen($aPhoneNumber) < 5 ) {
        logLine("Phone number is too short, CallerID is most likely blocked" );
        return FALSE;
    }

    $regje = preg_replace('/(\d)/', '$1\[^\\d\]*', $aPhoneNumber);
    $regje = '(' . $regje . ')$';

    logLine(" findSugarObjectByPhoneNumber: Contact query components- Phone: $aPhoneNumber RegEx: $regje\n");
    //*******/

    $phoneFields = array();
    // Here we build the list of phone fields to search
    if( !empty($sugar_config['asterisk_contact_phone_fields']) ) {
        $customPhoneFields = explode(',', $sugar_config['asterisk_contact_phone_fields'] );
        foreach ($customPhoneFields as $currCol) {
            $tablePrefix = "contacts.";
            if( endsWith($currCol,"_c") ) {
                $tablePrefix = "contacts_cstm.";
            }
             array_push($phoneFields, $tablePrefix . $currCol . " REGEXP '" . $regje . "'" );
        }
    }
    $phoneFieldsWherePortion = implode(' OR ', $phoneFields);

    $soapArgs = array(
        'session' => $soapSessionId,
        'module_name' => 'Contacts',
        'select_fields' => array('id', 'account_id', 'first_name', 'last_name'),
        // 2nd version 'query' => "((contacts.phone_work = '$searchPattern') OR (contacts.phone_mobile = '$searchPattern') OR (contacts.phone_home = '$searchPattern') OR (contacts.phone_other = '$searchPattern'))", );
        // Original...
        //'query' => "((contacts.phone_work LIKE '$searchPattern') OR (contacts.phone_mobile LIKE '$searchPattern') OR (contacts.phone_home LIKE '$searchPattern') OR (contacts.phone_other LIKE '$searchPattern'))"
        // Liz Version: Only works on mysql
        //'query' => "contacts.phone_home REGEXP '$regje' OR contacts.phone_mobile REGEXP '$regje' OR contacts.phone_work REGEXP '$regje' OR contacts.phone_other REGEXP '$regje' OR contacts.phone_fax REGEXP '$regje' OR phone_custom_c REGEXP '$regje'",
        'query' => $phoneFieldsWherePortion,
    );

    // print "--- SOAP get_entry_list() ----- ARGS ----------------------------------------\n";
    // var_dump($soapArgs);
    // print "-----------------------------------------------------------------------------\n";

    $soapResult = $soapClient->call('get_entry_list', $soapArgs);

    //print "--- SOAP get_entry_list() FOR GET CONTACT ------------------------------------\n";
    //var_dump($soapResult);
    //print "-----------------------------------------------------------------------------\n";

    if (!isSoapResultAnError($soapResult)) {

        // What this loop does is removes all the duplicates.
        // There is no way to get distinct contacts back from SOAP... I've had cases where 2 contacts with a matching number returned 43 entries!
        // -- This should resolve issues where you have contacts that aren't related to an account also..
        $matchingContacts = array();
        $contactIds = array();
        $matchingContactsNamesString = '';
        //var_dump($soapResult['entry_list']);
        for($i=0; $i<count($soapResult['entry_list']); $i++)
        {
            $curr = decode_name_value_list( $soapResult['entry_list'][$i]['name_value_list']);
            if( !in_array( $curr['id'], $contactIds ) )
            {
                $contactIds[] = $curr['id'];
                $matchingContacts[] = $curr;
                $matchingContactsNamesString = $matchingContactsNamesString . $curr['first_name'] . " " . $curr['last_name'] . " "; // used in logLines
            }
        }

        //print "DECODED LIST:\n\n";
        //var_dump($decodedList);
        $resultDecoded = $matchingContacts[0];

        if(count($matchingContacts) > 1) {
            $foundMultipleAccounts = FALSE;
            $matchingAccounts = array();
            //logLine(print_r($resultDecoded,true));
            for ($i = 0; $i < count($matchingContacts); $i++) {
                if( isset($matchingContacts[$i]['account_id'] ) ){
                    if( !in_array($matchingContacts[$i]['account_id'], $matchingAccounts) ) {
                        $matchingAccounts[] = $matchingContacts[$i]['account_id'];
                    }
                }
                else {
                    logLine("  No Account ID Set for Contact: {$matchingContacts[$i]['first_name']} {$matchingContacts[$i]['last_name']}... ignoring");
                }
            }

            if( count($matchingAccounts) == 1 ) {
                $result = array();
                $result['id'] = $matchingAccounts[0];
                logLine("Found multiple contacts (" . count($matchingContacts) . ") -- all belong to same account (or some contacts don't have an account assigned), associating call with account {$result['id']}\n");
                logLine("Matching contact names are: $matchingContactsNamesString" );
                return array('type' => 'Accounts', 'values' => $result);
            }
            else if( count($matchingAccounts) == 0 ) {
                logLine("__Multiple matching contacts and none of them are assigned to an account, Not associating with anyone!__");
                return FALSE;
            }
            else {
                logLine("__Multiple contacts matched multiple accounts, Not associating__");
                return FALSE;
            }
        }


        //print "--- Decoded get_entry_list() FOR GET CONTACT --------------------------------------\n";
        //var_dump($resultDecoded);
        //print "-----------------------------------------------------------------------------\n";
        return array(
            'type' => 'Contacts',
            'values' => $resultDecoded
        );
    }

    // Oops nothing found :-(
    return FALSE;
}

// Function only necessary in case of the original query used.
// Replace a phone number to search with a universal-match-anyway(tm) expression to be used
// in a SQL 'LIKE' condition - eg 1234 --> %1%2%3%4%
//

/* function regexify($aPhoneNumber)
  {
  return '%' . join('%', str_split($aPhoneNumber)) . '%';
  }
 */
//
// Finds related account for given contact id
//
function findAccountForContact($aContactId) {
    global $soapClient, $soapSessionId;
    logLine("### +++ findAccountForContact($aContactId)\n");

    $soapArgs = array(
        'session' => $soapSessionId,
        'module_name' => 'Contacts',
        'module_id' => $aContactId,
        'related_module' => 'Accounts',
        'related_module_query' => '',
        'deleted' => 0
    );

    $soapResult = $soapClient->call('get_relationships', $soapArgs);

    // TODO check if error exists first to prevent Notice about index not existing in log.
    if (!isSoapResultAnError($soapResult)) {
        // var_dump($soapResult);

        $assocCount = count($soapResult['ids']);

        if ($assocCount == 0) {
            logLine(" + No associated account found\n");
            return FALSE;
        } else {
            if ($assocCount > 1) {
                logLine("! WARNING: More than one associated account found, using first one.\n");
            }
            $assoAccountID = $soapResult['ids'][0]['id'];
            logLine(" + Associated account is $assoAccountID\n");
            return $assoAccountID;
        }
    }
}

/**
 * prints soap result info.  Known ISSUE: Can't use this for get_entry method.... it doesn't return result_count
 * Returns true if results were returned, FALSE if an error or no results are returned.
 *
 * @param $soapResult
 */
function isSoapResultAnError($soapResult) {
    $retVal = FALSE;
    if (isset($soapResult['error']['number']) && $soapResult['error']['number'] != 0) {
        logLine("! ***Warning: SOAP error*** " . $soapResult['error']['number'] . " " . $soapResult['error']['string'] . "\n");
        $retVal = TRUE;
    } else if (!isset($soapResult['result_count']) || $soapResult['result_count'] == 0) {
        logLine("! No results returned\n");
        $retVal = TRUE;
    }
    return $retVal;
}

/**
 * Performs a soap call to set a relationship between a call record and a bean (contact)
 * @param $callRecordId the call record id.
 * @param $beanType usually "Contacts"
 * @param $beanId
 */
function setRelationshipBetweenCallAndBean($callRecordId, $beanType, $beanId) {
    global $soapSessionId, $soapClient, $verbose_logging;

    if (!empty($callRecordId) && !empty($beanId) && !empty($beanType)) {
        $soapArgs = array(
            'session' => $soapSessionId,
            'set_relationship_value' => array(
                'module1' => 'Calls',
                'module1_id' => $callRecordId,
                'module2' => $beanType,
                'module2_id' => $beanId
            )
        );

        logLine(" Establishing relation to $beanType... Call ID: $callRecordId to Bean ID: $beanId\n");
        if ($verbose_logging) {
            var_dump($soapArgs);
        }
        $soapResult = $soapClient->call('set_relationship', $soapArgs);
        isSoapResultAnError($soapResult);
    } else {
        logLine("! Invalid Arguments passed to setRelationshipBetweenCallAndBean callRecordId=$callRecordId, beanId=$beanId, beanType=$beanType\n");
    }
}

///
/// Given the channel ($rawData['channel']) from the AMI Event, this returns the user ID the call should be assigned to.
/// If a suitable user extension cannot be found, Admin is returned
///
function findUserIdFromChannel($channel) {
    global $userGUID;
    $assignedUser = $userGUID; // Use logged in user as fallback

    $asteriskExt = extractExtensionNumberFromChannel($channel);

    $maybeAssignedUser = findUserByAsteriskExtension($asteriskExt);
    if ($maybeAssignedUser) {
        $assignedUser = $maybeAssignedUser;
        logLine("! Assigned user id set to $assignedUser\n");
    } else {
        $assignedUser = $userGUID;
        logLine(" ! Assigned user will be set to Administrator.\n");
    }

    return $assignedUser;
}

/**
 * attempts to find the "device" which is either the extension number or remote phone number if calling an external number
 * @param $channel
 * @return mixed
 */
function extractUserDeviceFromChannel($channel) {
    if( preg_match('/Local\/(.+?)@.+/', $channel, $matches ) ){
        return $matches[1];
    }
    logLine(" !WARNING: wasn't able to extract the user device from channel : $channel");
    return $matches[0]; // If we get here we probably need to add more cases.
}

//
// Attempt to find assigned user by asterisk ext
// PRIVATE METHOD: See findUserIdFromChannel
//
function extractExtensionNumberFromChannel($channel) {
    global $sugar_config;
    $asteriskExt = FALSE;
    $channelSplit = array();
    logLine("Looking for user extension number in: $channel\n");

// KEEP THIS BLOCK OF CODE IN SYNC WITH OUTBOUND
// BR: This cases matches things like Local/LC-52@from-internal-4bbbb
    $pattern = $sugar_config['asterisk_dialin_ext_match'];
    if (!startsWith($pattern, '/')) {
        $pattern = '/' . $pattern . '/i';
    }
    if (!empty($sugar_config['asterisk_dialin_ext_match']) && preg_match($pattern, $channel, $regs)) {
        logLine("Matched User REGEX. Regex: " . $regs[1] . "\n");
        $asteriskExt = $regs[1];
    }
// This matches the standard cases such as SIP/### or IAX/###
    else if (eregi('^([[:alpha:]]+)/([[:alnum:]]+)-', $channel, $channelSplit) > 0) {
        $asteriskExt = $channelSplit[2];
        logLine("Channel Matched SIP/### style regex. Ext is:" . $asteriskExt . "\n");
    } else {
        $asteriskExt = FALSE;
    }

    return $asteriskExt;
}

//
// Locate user by asterisk extension
// NOTE: THIS RETURNS JUST AN ID
// PRIVATE METHOD: See findUserIdFromChannel
//
function findUserByAsteriskExtension($aExtension) {
    logLine("### +++ findUserByAsteriskExtension($aExtension)\n");
    // The query below is actually pretty clever. Recall that user extensions can be a comma seperated list.
    // The 4 conditions are necessary 1) To match single extension case, 2) to match first extension in the list
    // 3) to match one in the middle of list, 4) matches one at the end of a list.
    $qry = "select id,user_name from users join users_cstm on users.id = users_cstm.id_c where " .
            "(users_cstm.asterisk_ext_c='$aExtension' or users_cstm.asterisk_ext_c LIKE '$aExtension,%' " .
            "OR users_cstm.asterisk_ext_c LIKE '%,$aExtension,%' OR users_cstm.asterisk_ext_c LIKE '%,$aExtension') and status='Active'";

    $result = mysql_checked_query($qry);
    if ($result) {
        $row = mysql_fetch_array($result);

        // All this if statement does is detect if multiple users were returned and if so display warning.
        if (mysql_num_rows($result) > 1) {
            $firstUser = $row['user_name'];
            $usernames = $row['user_name'];
            while ($row2 = mysql_fetch_array($result)) {
                $usernames .= ", " . $row2['user_name'];
            }
            logLine("### __WARNING__ Extension $aExtension matches the following users: $usernames! Call will be assigned to: $firstUser!");
        }

        return $row['id'];
    }

    return FALSE;

///// OLD WAY OF DOING IT IS WITH SOAP... DIDN"T WORK FOR ME... so reverted to db query.
    /*
      global $soapClient, $soapSessionId;
      print("# +++ findUserByAsteriskExtension($aExtension)\n");

      //'select_fields'=>array('id', 'first_name', 'last_name'),
      //'deleted' => 0,
      $soapArgs = array(
      'session' => $soapSessionId,
      'module_name' => 'Users',
      'query' => '(users_cstm.asterisk_ext_c=\'710\')',
      // 'query' => sprintf("(users_cstm.asterisk_ext_c='%s')", $aExtension),
      'select_fields'=>array('id', 'first_name', 'last_name'),
      );
      //var_dump($soapArgs);

      $soapResult = $soapClient->call('get_entry_list', $soapArgs);

      var_dump($soapResult);

      if ($soapResult['error']['number'] != 0) {
      logLine("! Warning: SOAP error " . $soapResult['error']['number'] . " " . $soapResult['error']['string'] . "\n");
      }
      else if( $soapResult['result_count'] == 0 ) {
      logLine("! No results returned\n");
      }
      else {
      $resultDecoded = decode_name_value_list($soapResult['entry_list'][0]['name_value_list']);
      // print "--- SOAP get_entry_list() ----- RESULT --------------------------------------\n";
      var_dump($resultDecoded);
      // print "-----------------------------------------------------------------------------\n";
      return $resultDecoded['id'];
      }

      return FALSE;
     */
}

//
// Checked execution of a MySQL query
//
// This function provides a wrapper around mysql_query(), providing SQL and error loggin
//
function mysql_checked_query($aQuery) {
    global $mysql_loq_queries;
    global $mysql_log_results;

    if ($mysql_loq_queries || $mysql_log_results) {
        logLine(" +++ mysql_checked_query()\n");
    }

    $query = trim($aQuery);
    if ($mysql_loq_queries) {
        logLine(" ! SQL: $query\n");
    }

    // Is this is a SELECT ?
    $isSelect = eregi("^select", $query);

    $sqlResult = mysql_query($query);

    if ($mysql_log_results) {
        if (!$sqlResult) {
            // Error occured
            logLine("! SQL error " . mysql_errno() . " (" . mysql_error() . ")\n");
        } else {
            // SQL succeeded
            if ($isSelect) {
                logLine(" --> Rows in result set: " . mysql_num_rows($sqlResult) . "\n");
            } else {
                logLine(" --> Rows affected: " . mysql_affected_rows() . "\n");
            }
        }
    }
    return $sqlResult;
}

/**
 * Method is equivalent to calling mysql_affected_rows( mysql_checked_query($query));
 * @param $query
 */
function mysql_checked_query_returns_affected_rows_count($query){
    mysql_checked_query($query);
    return mysql_affected_rows();
}

// mt_get: returns the current microtime
function mt_get(){
    global $mt_time;
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

// mt_start: starts the microtime counter
function mt_start(){
    global $mt_time; $mt_time = mt_get();
}

// mt_end: calculates the elapsed time
function mt_end($len=4){
    global $mt_time;
    $time_end = mt_get();
    return round($time_end - $mt_time, $len);
}

/**
 * Performs an async get request (doesn't wait for response)
 * Note: One limitation of this approach is it will not work if server does any URL rewriting
 */
function gitimg_log($event) {
    $host = "gitimg.com";
    $path = "/rs/track/blak3r/yaai-stats/$event/increment";
    $fp = fsockopen($host,80, $errno, $errstr, 30);
    $out = "GET " . $path . " HTTP/1.1\r\n";
    $out.= "Host: " . $host . "\r\n";
    $out.= "Connection: Close\r\n\r\n";
    fwrite($fp, $out);
    fclose($fp);
}


function logLine($str, $logFile = "default") {
    global $sugar_config;

    if (!endsWith($str, "\n")) {
        $str = $str . "\n";
    }

    if( $logFile == "default") {
        print($str);
    }

// if logging is enabled.
    if (!empty($sugar_config['asterisk_log_file']) && !empty($logFile)) {
        if( $logFile == "default" ) {
            $myFile = $sugar_config['asterisk_log_file'];
        }
        else {
            $myFile = $logFile;
        }
        $fh = fopen($myFile, 'a') or die("can't open file");
        fwrite($fh, $str);
        fclose($fh);
    }
}

// Theoretically safe method, feof will block indefinitely.
function safe_feof($fp, &$start = NULL) {
    $start = microtime(true);
    return feof($fp);
}

function startsWith($haystack, $needle) {
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle) {
    $length = strlen($needle);
    $start = $length * -1; //negative
    return (substr($haystack, $start) === $needle);
}

/**
 * Reads all lines from the socket until timeout occurs.
 * @param $socket
 * @param $timeout OPTIONAL (default is 500000 == 1/2 sec)
 * @return string
 */
function AMI_ReadResponse($socket, $timeout = 500000) {
    $retVal = '';
    // Sets timeout to 1/2 a second
    stream_set_timeout($socket, 0, $timeout);
    while (($buffer = fgets($socket, 20)) !== false) {
        $retVal .= $buffer;
    }
    return $retVal;
}

function AMI_WasCmdSuccessful($response) {
    return preg_match('/.*Success.*/s', $response);
}

/**
 * formats the string in a markdown ``` code block indented by 4 spaces
 * @param $str
 * @param $indent - OPTIONAL by default it's " " (4 spaces)
 * @return string
 */
function markdown_indent($str, $indent = " ") {
    $str = preg_replace("/(\r?\n)/i", "$1$indent", $str);
    $str = trim($str);
    $str = "$indent```\n$indent$str\n$indent```";
    return $str;
}

/**
 * AMI event params are not consistent.
 * Dial Events use 'UniqueID'
 * Join and NewCallerID events use 'Uniqueid' and for all we know it might also vary between versions of asterisk
 * So, this method just helps get the Unique ID for the call from the event.
 * @return string set to either $event['UniqueID'], $event['Uniqueid'] or NULL (if neither is set).
 */
function AMI_getUniqueIdFromEvent($event) {
    if (isset($event['UniqueID'])) {
        return $event['UniqueID']; // Dial Event Style, others too maybe
    } else if (isset($event['Uniqueid'])) {
        return $event['Uniqueid']; // Hangup Event Style, others too maybe
    }
    else if (isset($event['UniqueId'])) {
        return $event['UniqueId']; // As far as I know this is never used in AMI added just in case
    }
    return NULL;
}

function AMI_getCallerIdFromEvent($event) {
    if( isset($event['CallerIDNum'] )) {
        return trim($event['CallerIDNum']);
    }
    else if( isset($event['CallerID'] ) ){
        return trim($event['CallerID']);
    }
    else if( isset($event['CallerId'] ) ){
        return trim($event['CallerId']);
    }
    else {
        logLine("__ ERROR: Unable to find caller id in the event! __");
    }
}

function was_call_answered($id) {
    $query = "SELECT callstate FROM asterisk_log WHERE asterisk_dest_id='{$id}'";
    $result = mysql_checked_query($query);
    $result = mysql_fetch_array($result);
    $callstate = $result['callstate'];

    if($callstate == 'Ringing' || $callstate == 'Dial'){
        return 0;
    }else{
        return 1;
    }
}
?>

