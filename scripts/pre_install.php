<?php

/**
 * Asterisk SugarCRM Integration
 * (c) KINAMU Business Solutions AG 2009
 *
 * Parts of this code are (c) 2006. RustyBrick, Inc.  http://www.rustybrick.com/
 * Parts of this code are (c) 2008 vertico software GmbH
 * Parts of this code are (c) 2009 abcona e. K. Angelo Malaguarnera E-Mail admin@abcona.de
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
 */
if(!defined('sugarEntry'))define('sugarEntry', true);

function pre_install() {

   require_once('include/utils.php');

   // creates logic hook that inserts Asterisk JavaScript into every UI frame
   // This could be moved to manifest! See 
   
//   
//   array(
//'module' => '',
//'hook' => 'after_ui_frame',
//'order' => 67,
//'description' => 'Add Tab to User Editor',
//'file' => 'custom/modules/Users/Users_Enhanced.php',
//'class' => 'defaultHomepage',
//'function' => 'addTab',
//),
//   
   
 //  check_logic_hook_file("", "after_ui_frame",
 //  		array(1, 'Asterisk', 'custom/modules/Asterisk/include/AsteriskJS.php','AsteriskJS', 'echoJavaScript'));

   // creates Asterisk logging table
   // TODO detect if it's MSSQL and raise error
    if (empty($db)) {
        if (!class_exists('DBManagerFactory')) {
            die("no db available");
        }
        $db = & DBManagerFactory::getInstance();
    }

    //$query = "DROP TABLE IF EXISTS asterisk_log";
    //$db->query($query, false, "Error dropping asterisk_log table: " . $query);

    if( !$db->tableExists("asterisk_log") ) {
        $query = "CREATE TABLE asterisk_log (";
        $query .= "id int(10) unsigned NOT NULL auto_increment,";
        $query .= "call_record_id char(36) default NULL,";
        $query .= "asterisk_id varchar(45) default NULL,";
        $query .= "callstate varchar(10) default NULL,";
        $query .= "uistate varchar(10) default NULL,";  // added in v2.0 to keep track of which chat windows were minimized.
        $query .= "callerID varchar(45) default NULL,";
        $query .= "callerName varchar(45) default NULL,";
        $query .= "channel varchar(30) default NULL,";
        $query .= "remote_channel varchar(30) default NULL,"; // added in v2.0, it's used for transferring.
        // $query .= "timestampCall varchar(30) default NULL,";
        // $query .= "timestampLink varchar(30) default NULL,";
        // $query .= "timestampHangup varchar(30) default NULL,";
        $query .= "timestampCall datetime default NULL,";
        $query .= "timestampLink datetime default NULL,";
        $query .= "timestampHangup datetime default NULL,";
        $query .= "direction varchar(1) default NULL,";
        $query .= "hangup_cause integer default NULL,";
        $query .= "hangup_cause_txt varchar(45) default NULL,";
        $query .= "asterisk_dest_id varchar(45) default NULL,";
        $query .= "contact_id VARCHAR(36) DEFAULT NULL,"; // added in v2.0 to keep track of contact.  Helps when it matches multiple ones.
        $query .= "opencnam VARCHAR(16) DEFAULT NULL,"; // added in v2.2 to keep track of whether number had been looked up in opencnam yet.
        $query .= "PRIMARY KEY (id)";
        $query .= ")";
        $db->query($query, false, "Error creating call table: " . $query);
    }

    // Columns Added in v2.0
    add_column_if_not_exist($db,"asterisk_log","uistate", "VARCHAR(10) DEFAULT NULL");
    add_column_if_not_exist($db,"asterisk_log","remote_channel", "VARCHAR(30) DEFAULT NULL");
    add_column_if_not_exist($db,"asterisk_log","contact_id", "VARCHAR(36) DEFAULT NULL");
    // Columns Added in v2.3
    add_column_if_not_exist($db,"asterisk_log","opencnam", "VARCHAR(16) DEFAULT NULL");
}

// http://www.edmondscommerce.co.uk/mysql/mysql-add-column-if-not-exists-php-function/
function add_column_if_not_exist($db, $table, $column, $column_attr = "VARCHAR( 255 ) NULL" ){
    $exists = false;
    $cols = $db->get_columns($table);
    if( !array_key_exists($column, $cols) ) {
        $db->query("ALTER TABLE `$table` ADD `$column`  $column_attr");
    }
}

?>