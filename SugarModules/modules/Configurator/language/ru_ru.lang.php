<?php
/**
 * Asterisk SugarCRM Integration
 * (c) KINAMU Business Solutions AG 2009
 * (c) abcona e. K. 2009
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

if (!isset($mod_strings)) { $mod_strings = array(); }

$mod_strings['LBL_MANAGE_ASTERISK'] = 'Конфигурация ASTERISK';
$mod_strings['LBL_ASTERISK_HOST'] = 'Хост';
$mod_strings['LBL_ASTERISK_PORT'] = 'Порт';
$mod_strings['LBL_ASTERISK_USER'] = 'Пользователь';
$mod_strings['LBL_ASTERISK_SECRET'] = 'Пароль';
$mod_strings['LBL_ASTERISK_PREFIX'] = 'Dialout префикс';
$mod_strings['LBL_ASTERISK_DIALINPREFIX'] = 'Dialin префикс';
$mod_strings['LBL_ASTERISK_CONTEXT'] = 'Контекст';
$mod_strings['LBL_ASTERISK_EXPR'] = 'Регексп для входящих/исходящих звонков';
$mod_strings['LBL_ASTERISK_EXPR_DESC'] = 'Regular expression to match incoming calls';
$mod_strings['LBL_ASTERISK_SOAPUSER'] = 'Asterisk Soap-Юзер';

$mod_strings['LBL_ASTERISK_LOG_FILE'] = 'Путь к лог-файлу';
$mod_strings['LBL_ASTERISK_DIALOUT_CHANNEL'] = 'Канал Dialout';
$mod_strings['LBL_ASTERISK_DIALOUT_CHANNEL_DESC'] = 'Регулярное выражение, ### будет заменено на расширение текущего пользователя';
$mod_strings['LBL_ASTERISK_DIALIN_EXT_MATCH'] = 'Дополнительный регексп для расширений пользователей';
$mod_strings['LBL_ASTERISK_CALL_SUBJECT_INBOUND_ABBR'] = 'Префикс темы для входящих звонков';
$mod_strings['LBL_ASTERISK_CALL_SUBJECT_OUTBOUND_ABBR'] = 'Префикс темы для исходящих звонков';
$mod_strings['LBL_ASTERISK_CALL_SUBJECT_MAX_LENGTH'] = 'Макс. длина темы';
$mod_strings['LBL_ASTERISK_LISTENER_POLL_RATE'] = 'Частота опроса AJAX в миллисекундах';

?>
