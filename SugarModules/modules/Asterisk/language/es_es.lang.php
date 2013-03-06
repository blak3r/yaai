<?php
/**
 * Callinize
 *
 * Parts of this code are (c) 2013, Callinize, INC
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
 * You can contact Callinize at callinize@gmail.com
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 */

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$mod_strings = array ( 'YAAI' => array(

    'ASTERISKLBL_COMING_IN'                 =>      'Llamada entrante',
    'ASTERISKLBL_GOING_OUT'                 =>      'Llamada saliente',

    'ASTERISKLBL_DENY'                      =>      'Declinar',
    'ASTERISKLBL_OPEN_CONTACT'      =>      'Abrir contacto',
    'ASTERISKLBL_OPEN_MEMO'         =>      'Abrir nota',

    'ASTERISKLBL_PHONE'                         =>      'Teléfono',
    'ASTERISKLBL_NAME'                      =>      'Nombre',
    'ASTERISKLBL_COMPANY'           =>      'Cuenta',

    'REQUESTED'                     =>      'Solicitado',
    'PROCEEDING'                    =>      'Procediendo',
    'RINGBACK'                              =>      'Esperando respuesta',
    'INCOMING'                              =>      'Entrante',
    'RINGING'                               =>      'Sonando',
    'CONNECTED'                     =>      'Conectado',
    'DIAL'                                  =>  'Llamar',
    'HANGUP'                =>  'Terminar',

    // Added in v2.2
    'ASTERISKLBL_DURATION'         => 'Duracion',
    'ASTERISKLBL_SELECTCONTACT'    => 'Seleccionar Contacto',
    'ASTERISKLBL_SELECTACCOUNT'    => 'Seleccionar Cuenta',
    'ASTERISKLBL_MULTIPLE_MATCHES' => 'Multiples coincidencias',
    'ASTERISKLBL_CALLERID'         => 'CallerID',

    // For asteriskLogger.php SOAP call entries
    'CALL_AUTOMATIC_RECORD'         => '** Registro automatico **',
    'CALL_IN_LIMBO'                 => 'Incompleta',
    'CALL_STATUS_HELD'              => 'Realizada',
    'CALL_STATUS_MISSED'            => 'Perdida',
    'CALL_NAME_CALL'                => 'Llamada',
    'CALL_NAME_MISSED'              => 'Llamada perdida',
    'CALL_DESCRIPTION_CALLER_ID'    => 'Caller ID',
    'CALL_DESCRIPTION_MISSED'       => 'Perdida',

    // V3 Additions
    'CALL_DESCRIPTION_PHONE_NUMBER' => 'Número de telefono',
    'CREATE'                        => 'Crear',
    'RELATE_TO'                     => 'Relacionar',
    'BLOCK'                         => 'Bloquear',
    'SAVE'                          => 'Guardar',
    'ASTERISKLBL_USER_EXT'         => 'Ext de usuario',
    'ASTERISKLBL_INBOUND_EXT'      => 'Ext entrante',
    'RELATE_TO_CONTACT'            => 'Relacionar Contacto',
    'RELATE_TO_ACCOUNT'            => 'Relacionar Cuenta',
    'CREATE_NEW_ACCOUNT'           => 'Crear Cuenta',
    'CREATE_NEW_CONTACT'           => 'Crear Contacto',
    'BLOCK_NUMBER'                 => 'Bloquear Número',
)
);


?>
