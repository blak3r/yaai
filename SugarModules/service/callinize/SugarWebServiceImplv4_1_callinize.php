<?php

    if(!defined('sugarEntry'))define('sugarEntry', true);

    require_once('service/v4_1/SugarWebServiceImplv4_1.php');
    require_once('custom/modules/Asterisk/include/callinize_db.php');

    class SugarWebServiceImplv4_1_callinize extends SugarWebServiceImplv4_1
    {
        /*
         * Returns the session id if authenticated
         *
         * @param string $session
         * @return string $session - false if invalid.
         *    'bean_module' = "contacts", "leads" or "accounts"
         *    'bean_id' =
         *    'bean_name' = name
         *    'bean_description',
         *    'bean_email',
         *    'parent_module' = "accounts"...
         *    'parent_id'
         *    'parent_name' == Leads can put company name here... note: parent_module should be set to leads, and parent_id is also leads.
         *
         */
        function find_beans_with_phone_number($session, $phone_number, $module_order, $stop_on_find)
        {
            $GLOBALS['log']->info('Begin: SugarWebServiceImplv4_1_custom->example_method');
            $error = new SoapError();
			$retVal = array();
			

            //authenticate
            if (!self::$helperObject->checkSessionAndModuleAccess($session, 'invalid_session', '', '', '',  $error))
            {
                $GLOBALS['log']->info('End: SugarWebServiceImplv4_1_custom->example_method.');
				//$retVal['status'] = "ERROR: Invalid Session ID";
				//$retVal['data'] = '';
			}
			else {
                $retVal =  find_beans($phone_number,$module_order, $stop_on_find,$GLOBALS['current_user'] );
          	}

			
            return $retVal;//$session . " oid: " . $oid;
        }

        function originate_call($session, $from_extension, $toNumber) {
            $GLOBALS['log']->info('Begin: SugarWebServiceImplv4_1_custom->example_method');
            $error = new SoapError();
            $retVal = array();


            //authenticate
            if (!self::$helperObject->checkSessionAndModuleAccess($session, 'invalid_session', '', '', '',  $error))
            {
                $GLOBALS['log']->info('End: SugarWebServiceImplv4_1_custom->example_method.');
                //$retVal['status'] = "ERROR: Invalid Session ID";
                //$retVal['data'] = '';
            }
            else {
                require_once 'custom/modules/Asterisk/include/callinize_db.php"';
                originate($from_extension, $toNumber);
            }


            return $retVal;//$session . " oid: " . $oid;
        }
    }

?>