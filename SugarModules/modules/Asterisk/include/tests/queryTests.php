<?php
/**
 * File: queryTests.php
 * Project: yaai
 * User: blake, http://www.blakerobertson.com
 * Date: 5/13/13
 * Time: 3:58 PM
 */

require_once("custom/modules/Asterisk/include/callinize_db.php");

//$result = find_beans_db_query("contacts", "phone_work,phone_home,phone_mobile,phone_other,assistant_phone", "4102152497", null, $GLOBALS['current_user'] );
//$beans = convert_bean_to_simple_array("contacts", $result,  $GLOBALS['current_user']);
//
//echo "BEANS:\n" . print_r($beans,true);
//


//echo "CONTACTS:\n" . print_r($contacts,true);

$beans = find_beans("4102152497", "accounts,contacts,leads", $GLOBALS['current_user']);
echo "BEANS:\n" . print_r($beans,true);


$beans = find_beans("4102152497", "accounts,contacts,leads", $GLOBALS['current_user']);
echo "BEANS:\n" . print_r($beans,true);

echo "ALL DONE";
