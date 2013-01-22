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

$mod_strings = array('YAAI' => array (

   	'ASTERISKLBL_COMING_IN' 		=>	'Eingehender Anruf',
	'ASTERISKLBL_GOING_OUT' 		=>	'Ausgehender Anruf',

	'ASTERISKLBL_DENY' 			=>	'Ablehnen',
	'ASTERISKLBL_OPEN_CONTACT' 	=>	'Kontakt &ouml;ffnen',
	'ASTERISKLBL_OPEN_MEMO' 	=>	'Gespr&auml;chsnotiz &ouml;ffnen',

	'ASTERISKLBL_PHONE' 			=>	'Telefon',
	'ASTERISKLBL_NAME' 			=>	'Name',
	'ASTERISKLBL_COMPANY' 		=>	'Firma',

	'REQUESTED' 			=>	'Rufaufbau',
	'PROCEEDING' 			=>	'Rufaufbau',
	'RINGBACK' 				=>	'Warten auf Antwort',
	'INCOMING' 				=>	'Klingelt',
	'RINGING' 				=>	'Klingelt',
	'CONNECTED' 			=>	'Verbunden',
	'DIAL' 					=>	'Klingelt',
    'HANGUP'                =>  'Hangup', // TODO Translate

    // Added in v2.2
	'ASTERISKLBL_DURATION'         => 'Duration',
	'ASTERISKLBL_SELECTCONTACT'    => 'Select Contact',
	'ASTERISKLBL_MULTIPLE_MATCHES' => 'Multiple Matches',
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

    // V3 Additions
    'CALL_DESCRIPTION_PHONE_NUMBER' => 'Phone Number',
    'CREATE'                        => 'Create',
    'RELATE_TO'                     => 'Relate',
    'BLOCK'                         => 'Block',
    'SAVE'                          => 'Save Memo',
    'ASTERISKLBL_USER_EXT'         => 'User Ext',
    'ASTERISKLBL_INBOUND_EXT'      => 'Inbound Ext',
  )
  );

