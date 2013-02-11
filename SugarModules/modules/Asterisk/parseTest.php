<?php
/**
 * Created by JetBrains PhpStorm.
 * User: blake
 * Date: 2/10/13
 * Time: 6:13 PM
 * To change this template use File | Settings | File Templates.
 */


require_once "parse.php";

$arr = array();
$arr['message'] = "this is a test";
$arr['call_record_id'] = "Some ID";
$p = new ParseBackendWrapper();
$response = $p->customCodeMethod("send_push", $arr);
print $response;
exit;