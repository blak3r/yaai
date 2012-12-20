<?php
/**
 * User: blake
 * Date: 12/19/12
 * Time: 11:58 PM
 * Creates a windows service for asteriskLogger.php, would be fairly trivial to add remove service support also.
 * see win32_remove_service.
 *
 * Instructions:
 * 1) copy the php_win32service.dll to your /ext directory.
 * 2) update your php.ini file to include extension=php_win32service.dll
 * 3) Run the script.  If it failed, you need to find the correct php_win32service.dll for your particular php version / compiled settings
 * 4) Once script runs successfully, you'll see it in the services control panel.
 * 5) The script wouldn't start for me (Blake Robertson)... but I'm committing it anyway as maybe someone can take it from here.
 *
 * READ #5 above.
 */

if( $argc <= 1 ) {
    print "USAGE: php create_win_service.php <path to asteriskLogger.php>\n";
    print "       <path to asteriskLogger.php> is generally <SugarRoot>/custom/modules/Asterisk/asteriskLogger.php\n";
    exit;
}

if( !file_exists($argv[1]) ) {
    print "ERROR: asteriskLogger.php not found at: $argv[1] set the path properly.\n";
}
else {
    $x = win32_create_service(array(
        'service'     => 'asteriskLogger.php',                                 # the name of your service
        'display'     => 'Asterisk Integration for SugarCRM',                  # short description
        'description' => 'Maintains an AMI connection with your pbx and posts events to your sugarcrm server', # long description
        'params'      => '"' . $argv[1] . '"',                            # path to the script and parameters
    ));
debug_zval_dump($x);
}
?>