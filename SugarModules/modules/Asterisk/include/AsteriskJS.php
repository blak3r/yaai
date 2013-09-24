<?php

/**
 * Asterisk SugarCRM Integration
 * (c) KINAMU Business Solutions AG 2009
 *
 * Parts of this code are (c) 2006. RustyBrick, Inc.  http://www.rustybrick.com/
 * Parts of this code are (c) 2008 vertico software GmbH
 * Parts of this code are (c) 2009 abcona e. K. Angelo Malaguarnera E-Mail admin@abcona.de
 * Parts of this code are (c) 2012 Blake Robertson. http://www.blakerobertson.com
 * http://www.sugarforge.org/projects/yaai/
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
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
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
 *
 * This file is added as an after_ui_frame logic hook by one of the manifest install scripts.  It calls:
 *    check_logic_hook_file("", "after_ui_frame",
 * 		array(1, 'Asterisk', 'custom/modules/Asterisk/include/AsteriskJS.php','AsteriskJS', 'echoJavaScript'));
 *
 */
//prevents directly accessing this file from a web browser
if (!defined('sugarEntry') || !sugarEntry)
    die('Not A Valid Entry Point');

class AsteriskJS {

    function echoJavaScript() {
        // asterisk hack: include ajax callbacks in every sugar page except ajax requests:

        if ((!isset($_REQUEST['sugar_body_only']) || $_REQUEST['sugar_body_only'] != true) && $_REQUEST['action'] != 'modulelistmenu' &&
             $_REQUEST['action'] != "favorites" && $_REQUEST['action'] != 'Popup' && empty($_REQUEST['to_pdf']) &&
            (!empty($_REQUEST['module']) && $_REQUEST['module'] != 'ModuleBuilder') && empty($_REQUEST['to_csv']) && $_REQUEST['action'] != 'Login' &&
            $_REQUEST['module'] != 'Timesheets') {

            if(!empty( $GLOBALS['current_user']->asterisk_ext_c) &&
               (($GLOBALS['current_user']->asterisk_inbound_c == '1') || ($GLOBALS['current_user']->asterisk_outbound_c == '1')))
            {
                $callinizeDevMode = $GLOBALS['sugar_config']['asterisk_callinize_dev'];

                $poll_rate = !empty($GLOBALS['sugar_config']['asterisk_listener_poll_rate']) ? $GLOBALS['sugar_config']['asterisk_listener_poll_rate'] : "10000";
                $user_extension = !empty($GLOBALS['current_user']->asterisk_ext_c) ? $GLOBALS['current_user']->asterisk_ext_c : "Not Configured!";
                $current_user_id = $GLOBALS['current_user']->id;
                $fop_user = $GLOBALS['current_user']->asterisk_fop_user_c;
                $fop_pass = $GLOBALS['current_user']->asterisk_fop_pass_c;
                $fop_url = "";
                if( !empty($GLOBALS['sugar_config']['asterisk_fop_url'])) {
                    $fop_url = $GLOBALS['sugar_config']['asterisk_fop_url'];
                }
                $fop_enabled = !empty($fop_url) ? 1 : 0;

                //JS Global Variables
                echo '<script type="text/javascript">window.callinize_dev = ' . $GLOBALS['sugar_config']['asterisk_callinize_dev'] . ';</script>';
                echo '<script type="text/javascript">window.callinize_debug = ' . $GLOBALS['sugar_config']['asterisk_callinize_debug'] . ';</script>';
                echo '<script type="text/javascript">window.callinize_poll_rate = ' . $poll_rate . ';</script>';
                echo '<script type="text/javascript">window.callinize_user_extension = ' . "'$user_extension'" . ';</script>';
                echo '<script type="text/javascript">window.callinize_current_user_id = ' . "'$current_user_id'" . ';</script>';
                echo '<script type="text/javascript">window.callinize_fop_user = "' . $fop_user . '";</script>';
                echo '<script type="text/javascript">window.callinize_fop_pass = "' . $fop_pass . '";</script>';
                echo '<script type="text/javascript">window.callinize_fop_url= "' . $fop_url . '";</script>';
                echo '<script type="text/javascript">window.callinize_fop_enabled= ' . $fop_enabled . ';</script>';
                echo '<script type="text/javascript">window.callinize_show_transfer_button= ' . getConfigBool('asterisk_transfer_button_enabled',0) . ';</script>';
                echo '<script type="text/javascript">window.callinize_relate_to_account_enabled = ' . getConfigBool('asterisk_relate_to_account_enabled',0) . ';</script>';
                echo '<script type="text/javascript">window.callinize_relate_to_contact_enabled = ' . getConfigBool('asterisk_relate_to_contact_enabled',0) . ';</script>';
                echo '<script type="text/javascript">window.callinize_relate_to_lead_enabled = ' . getConfigBool('asterisk_relate_to_lead_enabled',0) . ';</script>';
                echo '<script type="text/javascript">window.callinize_create_new_contact_enabled = ' . getConfigBool('asterisk_create_new_contact_enabled',0) . ';</script>';
                echo '<script type="text/javascript">window.callinize_create_new_lead_enabled = ' . getConfigBool('asterisk_create_new_lead_enabled',0) . ';</script>';
                echo '<script type="text/javascript">window.callinize_block_button_enabled = ' . getConfigBool('asterisk_block_button_enabled',0) . ';</script>';
                echo '<script type="text/javascript">window.callinize_recording_enabled = ' . getConfigBool('asterisk_recordings_enabled',0) . ';</script>';

                echo '<script type="text/javascript"> if (!window.console) console = {log: function() {}}; </script>'; // Prevents bug in IE (See Issue #108)


                //JS Third-Party Libraries
                if( preg_match("/^6\.[1-4]/",$GLOBALS['sugar_version'] && $GLOBALS['sugar_config']['asterisk_jquery_override'] == '0') ) {
                    echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>';
                    echo '<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.js" type="text/javascript"></script>';
                }

                if( $callinizeDevMode ) {
                    // This version includes the compiler and the non compiled template
                    echo '<script src="custom/modules/Asterisk/include/javascript/offlineMode/handlebars-1.0.rc.1.js"></script>';
                    $template = file_get_contents( "custom/modules/Asterisk/include/template/call-template.html" );
                    echo '<script id="handlebars-dev-template" type="text/x-handlebars-template">' . $template . '</script>';
                }else {
                    echo '<script src="custom/modules/Asterisk/include/javascript/handlebars.runtime.js"></script>';
                    echo '<script src="custom/modules/Asterisk/include/template/call-template.tmpl"></script>';

                }

                echo '<script src="custom/modules/Asterisk/include/javascript/jquery.fancybox.js" type="text/javascript" ></script>';
                //echo '<script src="custom/modules/Asterisk/include/javascript/jquery.dropdown.js" type="text/javascript" ></script>';

                //JS CALLINIZE
                if($GLOBALS['current_user']->asterisk_inbound_c == '1') {
                    echo '<script type="text/javascript" src="custom/modules/Asterisk/include/javascript/callPopups.js"></script>';
                }
                if($GLOBALS['current_user']->asterisk_outbound_c == '1') {
                    echo '<script type="text/javascript" src="custom/modules/Asterisk/include/javascript/dialout.js"></script>';
                }

                //CSS Third-Party Libraries
                if ($GLOBALS['sugar_config']['asterisk_jquery_override'] == '0') {
                    if ($callinizeDevMode) {
                        echo '<link rel="stylesheet" href="custom/modules/Asterisk/include/javascript/offlineMode/jquery-ui.css" />';
                    } else {

                        echo '<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/redmond/jquery-ui.css" />';
                    }
                }

                echo '<link rel="stylesheet" type="text/css" href="custom/modules/Asterisk/include/css/jquery.fancybox.css" media="screen" />';

                //CSS CALLINIZE

                echo '<link rel="stylesheet" type="text/css" media="all" href="custom/modules/Asterisk/include/css/asterisk.css" />';
                echo '<!--[if lte IE 7]>';
                echo '<link type="text/css" rel="stylesheet" media="all" href="custom/modules/Asterisk/include/css/screen_ie.css" />';
                echo '<![endif]-->';
            }
        }
    }
}

function getConfigBool($index,$defaultValue=0) {
    //return "1";
    if( !empty( $GLOBALS['sugar_config'][$index]) ) {
        return $GLOBALS['sugar_config'][$index];
    }
    else {
        return $defaultValue;
    }
}

?>
