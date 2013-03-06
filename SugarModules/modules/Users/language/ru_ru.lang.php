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
//$mod_strings = array (
//    'LBL_ASTERISK_OPTIONS_TITLE' => 'Данные пользовател�? ASTERISK',
//	'LBL_ASTERISK_EXT'	=> '�?омер телефона',
//	'LBL_ASTERISK_EXT_DESC' => 'Rights look in EditView :_checked_:=on :_ _:=off',
//	'LBL_ASTERISK_INBOUND' => 'Asterisk Вход�?щие 1=Включить 0=Выключить',
//	'LBL_ASTERISK_OUTBOUND' => 'Asterisk И�?ход�?щие 1=Включить 0=Выключить',
//	'LBL_ASTERISK_INBOUND_DESC' => 'Е�?ли включено, то пользователь имеет возможно�?ть видеть вход�?щие и и�?ход�?щие звонки',
//	'LBL_ASTERISK_OUTBOUND_DESC' => 'Е�?ли включено, то пользователь имеет возможно�?ть делать и�?ход�?щие звонки нажав на иконку возле номера телефона',
//);

if (!isset($mod_strings)) { $mod_strings = array(); }

$mod_strings['LBL_ASTERISK_OPTIONS_TITLE'] = 'Данные пользователя ASTERISK';
$mod_strings['LBL_ASTERISK_EXT'] = 'Номер телефона';
$mod_strings['LBL_ASTERISK_EXT_DESC'] = 'Asterisk екстеншен принадлежащий этому пользователю (обычно две\три цифры)';
$mod_strings['LBL_ASTERISK_INBOUND'] = 'Уведомления о звонках';
$mod_strings['LBL_ASTERISK_OUTBOUND'] = 'Изображение телефончиков возле номеров телефонов';
$mod_strings['LBL_ASTERISK_INBOUND_DESC'] = 'Если включено, то пользователь имеет возможность видеть уведомление о входящем\исходящем звонке.';
$mod_strings['LBL_ASTERISK_OUTBOUND_DESC'] = 'Если включено, то пользоваетль имеет возможность делать исходящие звонки кликом по номеру телефона.';



?>
