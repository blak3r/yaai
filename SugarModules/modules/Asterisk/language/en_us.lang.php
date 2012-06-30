<?php

/**
 * Asterisk SugarCRM Integration
 * (c) KINAMU Business Solutions AG 2009
 *
 * Parts of this code are (c) 2006. RustyBrick, Inc.  http://www.rustybrick.com/
 * Parts of this code are (c) 2008 vertico software GmbH
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
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$mod_strings = array (

   	'ASTERISKLBL_COMING_IN' 		=>	'Incoming call',
	'ASTERISKLBL_GOING_OUT' 		=>	'Outgoing call',

	'ASTERISKLBL_DENY' 			=>	'Decline',
	'ASTERISKLBL_OPEN_CONTACT' 	=>	'Open Contact',
	'ASTERISKLBL_OPEN_MEMO' 	=>	'Open Memo',

    'ASTERISKLBL_PHONE' 			=>	'Phone',
	'ASTERISKLBL_NAME' 			=>	'Name',
	'ASTERISKLBL_COMPANY' 		=>	'Account',

	'REQUESTED' 			=>	'Proceeding',
	'PROCEEDING' 			=>	'Proceeding',
	'RINGBACK' 				=>	'Waiting for Answer',
	'INCOMING' 				=>	'Ringing',
	'RINGING' 				=>	'Ringing',
	'CONNECTED' 			=>	'Connected',
	'DIAL'					=>  'Ringing',
	'HANGUP'                =>  'Finished',

    // Added in v2.2
	'ASTERISKLBL_DURATION'         => 'Duration',
	'ASTERISKLBL_SELECTCONTACT'    => 'Select Contact',
	'ASTERISKLBL_MULTIPLE_MATCHES' => "Multiple Matches",
	'ASTERISKLBL_CALLERID'         => 'CallerID',

    // For asteriskLogger.php SOAP call entries
	'CALL_AUTOMATIC_RECORD'         => '** Automatic record **',
	'CALL_IN_LIMBO'                 => 'In Limbo',
	'CALL_STATUS_HELD'              => 'Held',
	'CALL_STATUS_MISSED'            => 'Missed',
	'CALL_NAME_CALL'                => 'CALL',
	'CALL_NAME_MISSED'              => 'Missed Call',
	'CALL_DESCRIPTION_CALLER_ID'    => 'Caller ID',
	'CALL_DESCRIPTION_MISSED'       => 'Missed/failed call',
	'CALL_DESCRIPTION_PHONE_NUMBER' => 'Phone Number'

   );

?>
