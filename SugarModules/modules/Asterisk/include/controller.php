<?php

/* * *
* Author: Blake Robertson
* Author: Patrick Hogan
*
* Controller class for various AJAX things such as saving the UI state and saving the call details.
*
* TODO: createCall should be refactored into this php file and then called by specifying an appropriate action for them.
*/



if (!defined('sugarEntry') || !sugarEntry)
    die('Not A Valid Entry Point');

require_once('include/utils.php');
require_once('include/export_utils.php');
require_once('modules/Calls/Call.php');
require_once('modules/Users/User.php');
//sets $mod_strings variable

require_once('custom/modules/Asterisk/include/callinize_db.php');

// Check to see if the language file exists, if not we load the US English one.
// TODO should we use include_once instead of require_once?
// Settled on using include as it's faster... then include_once
if(is_file("custom/modules/Asterisk/language/" . $GLOBALS['sugar_config']['default_language'] . ".lang.php")) {
    include("custom/modules/Asterisk/language/" . $GLOBALS['sugar_config']['default_language'] . ".lang.php");
}
else {
    include("custom/modules/Asterisk/language/en_us.lang.php");
}

//logLine("At Top\n", "c:/controller.log");

//ACTIONS

switch ($_REQUEST['action']) {
    case "memoSave" :
        if ($_REQUEST['call_record']) {
             memoSave($_REQUEST['call_record'], $_REQUEST['sugar_user_id'], $_REQUEST['phone_number'], $_REQUEST['description'], $_REQUEST['direction']);
        }
        break;
    case "updateUIState" :
        updateUIState($_REQUEST['ui_state'], $_REQUEST['call_record'], $_REQUEST['id']);
        break;
    case "setBeanID" :
        setBeanID($_REQUEST['call_record'], $_REQUEST['bean_module'], $_REQUEST['bean_id']);
        break;
    case "call" :
        callCreate();
        break;
    case "transfer" :
        transferCall($_REQUEST["extension"], $_REQUEST['call_record']);
        break;
    case "block" :
        blockNumber($_REQUEST['number'], $_REQUEST['description']);
        break;
    case "get_calls" :
        getCalls($mod_strings, $GLOBALS['current_user']);
        break;
    default :
        echo "undefined action";
        break;
}

sugar_cleanup(); // Formerly was in getCalls()

?>