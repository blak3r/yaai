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

   	'ASTERISKLBL_COMING_IN' 		=>	'Входящий звонок',
	'ASTERISKLBL_GOING_OUT' 		=>	'Исходящий звонок',

	'ASTERISKLBL_DENY' 			=>	'Отклонить',
	'ASTERISKLBL_OPEN_CONTACT' 	=>	'Открыть контакт',
	'ASTERISKLBL_OPEN_MEMO' 	=>	'Открыть памятку',

	'ASTERISKLBL_PHONE' 			=>	'Телефон',
	'ASTERISKLBL_NAME' 			=>	'Имя',
	'ASTERISKLBL_COMPANY' 		=>	'Аккаунт',


	'REQUESTED' 			=>	'Набор номера',
	'PROCEEDING' 			=>	'Набор номера',
	'RINGBACK' 				=>	'Ожидание ответа',
	'INCOMING' 				=>	'Звонок',
	'RINGING' 				=>	'Звонок',
	'CONNECTED' 			=>	'Соединено',
	'DIAL'					=>  'Звонок',
	'HANGUP'				=>	'Завершено',

	// Added in v2.2
	'ASTERISKLBL_DURATION'         => 'Продолжительность',
	'ASTERISKLBL_SELECTCONTACT'    => 'Выберите контакт',
	'ASTERISKLBL_MULTIPLE_MATCHES' => "Несколько совпадений",
	'ASTERISKLBL_CALLERID'         => 'CallerID',

    // For asteriskLogger.php SOAP call entries
    'CALL_AUTOMATIC_RECORD'         => '** Авто-запись **',
	'CALL_IN_LIMBO'                 => 'В процессе',
	'CALL_STATUS_HELD'              => 'Принят',
	'CALL_STATUS_MISSED'            => 'Пропущен',
	'CALL_NAME_CALL'                => 'Звонок',
	'CALL_NAME_MISSED'              => 'Пропущенный звонок',
	'CALL_DESCRIPTION_CALLER_ID'    => 'Номер звонившего',
	'CALL_DESCRIPTION_MISSED'       => 'Пропущенный/неудачный звонок',
	'CALL_DESCRIPTION_PHONE_NUMBER' => 'Номер телефона'

   );

?>
