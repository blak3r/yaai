<?php

/*********************************************************************************
 * 
 * STARFACE SugarCRM Connector is a computer telephony integration module for the
 * SugarCRM customer relationship managment program by SugarCRM, Inc.
 *
 * Copyright (C) 2008 vertico software GmbH 
 * Parts of this code are (c) 2009 abcona e. K. Angelo Malaguarnera E-Mail admin@abcona.de
 * http://www.sugarforge.org/projects/yaai/
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
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
 * You can contact vertico software GmbH at Amalienstr. 81-87, 76133 Karlsruhe,
 * GERMANY or at the e-mail address info@vertico-software.com
 * 
 ********************************************************************************/

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$listViewDefs['Contacts'] = array(
	'NAME' => array(
		'width' => '20%', 		
		'label' => 'LBL_LIST_NAME', 
		'link' => true,
        'contextMenu' => array('objectType' => 'sugarPerson', 
                               'metaData' => array('contact_id' => '{$ID}', 
                                                   'module' => 'Contacts',
                                                   'return_action' => 'ListView', 
                                                   'contact_name' => '{$FULL_NAME}', 
                                                   'parent_id' => '{$ACCOUNT_ID}',
                                                   'parent_name' => '{$ACCOUNT_NAME}',
                                                   'return_module' => 'Contacts', 
                                                   'return_action' => 'ListView', 
                                                   'parent_type' => 'Account', 
                                                   'notes_parent_type' => 'Account')
                              ),
		'orderBy' => 'last_name',
        'default' => true,
        'related_fields' => array('first_name', 'last_name', 'salutation', 'account_name', 'account_id'),
		), 
	'TITLE' => array(
		'width' => '15%', 
		'label' => 'LBL_LIST_TITLE',
        'default' => true), 
	'ACCOUNT_NAME' => array(
		'width' => '29%', 
		'label' => 'LBL_LIST_ACCOUNT_NAME', 
		'module' => 'Accounts',
		'id' => 'ACCOUNT_ID',
		'link' => true,
        'contextMenu' => array('objectType' => 'sugarAccount', 
                               'metaData' => array('return_module' => 'Contacts', 
                                                   'return_action' => 'ListView', 
                                                   'module' => 'Accounts',
                                                   'return_action' => 'ListView', 
                                                   'parent_id' => '{$ACCOUNT_ID}', 
                                                   'parent_name' => '{$ACCOUNT_NAME}', 
                                                   'account_id' => '{$ACCOUNT_ID}', 
                                                   'account_name' => '{$ACCOUNT_NAME}'),
                              ),
        'default' => true,
        'sortable'=> true,
        'ACLTag' => 'ACCOUNT',
        'related_fields' => array('account_id')),
	'EMAIL1' => array(
		'width' => '15%', 
		'label' => 'LBL_LIST_EMAIL_ADDRESS',
		'sortable' => false,
		'link' => true,
		'customCode' => '{$EMAIL1_LINK}{$EMAIL1}</a>',
        'default' => true
		),  
	'PHONE_WORK' => array(
		'width' => '20%', 
		'label' => 'LBL_OFFICE_PHONE',
            'default' => true,
		'customCode' => '<span class="asterisk_phoneNumber" id="">{$PHONE_WORK}</span>&nbsp;&nbsp;',),
    'DEPARTMENT' => array(
        'width' => '10', 
        'label' => 'LBL_DEPARTMENT'),
    'DO_NOT_CALL' => array(
        'width' => '10', 
        'label' => 'LBL_DO_NOT_CALL'),
    'PHONE_HOME' => array(
        'width' => '10', 
        'label' => 'LBL_HOME_PHONE',
	  'customCode' => '<span class="asterisk_phoneNumber" id="">{$PHONE_HOME}</span>&nbsp;&nbsp;',),
    'PHONE_MOBILE' => array(
        'width' => '10', 
        'label' => 'LBL_MOBILE_PHONE',
	  'customCode' => '<span class="asterisk_phoneNumber" id="">{$PHONE_MOBILE}</span>&nbsp;&nbsp;',),
    'PHONE_OTHER' => array(
        'width' => '10', 
        'label' => 'LBL_OTHER_PHONE',
 	  'customCode' => '<span class="asterisk_phoneNumber" id="">{$PHONE_OTHER}</span>&nbsp;&nbsp;',),
    'PHONE_FAX' => array(
        'width' => '10', 
        'label' => 'LBL_FAX_PHONE'),
    'EMAIL2' => array(
        'width' => '15', 
        'label' => 'LBL_LIST_EMAIL_ADDRESS',
        'sortable' => false,
        'customCode' => '{$EMAIL2_LINK}{$EMAIL2}</a>'),  
    'EMAIL_OPT_OUT' => array(
        'width' => '10', 
        
        'label' => 'LBL_EMAIL_OPT_OUT'),
    'PRIMARY_ADDRESS_STREET' => array(
        'width' => '10', 
        'label' => 'LBL_PRIMARY_ADDRESS_STREET'),
    'PRIMARY_ADDRESS_CITY' => array(
        'width' => '10', 
        'label' => 'LBL_PRIMARY_ADDRESS_CITY'),
    'PRIMARY_ADDRESS_STATE' => array(
        'width' => '10', 
        'label' => 'LBL_PRIMARY_ADDRESS_STATE'),
    'PRIMARY_ADDRESS_POSTALCODE' => array(
        'width' => '10', 
        'label' => 'LBL_PRIMARY_ADDRESS_POSTALCODE'),
    'ALT_ADDRESS_COUNTRY' => array(
        'width' => '10', 
        'label' => 'LBL_ALT_ADDRESS_COUNTRY'),
    'ALT_ADDRESS_STREET' => array(
        'width' => '10', 
        'label' => 'LBL_ALT_ADDRESS_STREET'),
    'ALT_ADDRESS_CITY' => array(
        'width' => '10', 
        'label' => 'LBL_ALT_ADDRESS_CITY'),
    'ALT_ADDRESS_STATE' => array(
        'width' => '10', 
        'label' => 'LBL_ALT_ADDRESS_STATE'),
    'ALT_ADDRESS_POSTALCODE' => array(
        'width' => '10', 
        'label' => 'LBL_ALT_ADDRESS_POSTALCODE'),
    'ALT_ADDRESS_COUNTRY' => array(
        'width' => '10', 
        'label' => 'LBL_ALT_ADDRESS_COUNTRY'),
    'DATE_ENTERED' => array(
        'width' => '10', 
        'label' => 'LBL_DATE_ENTERED'),
    'CREATED_BY_NAME' => array(
        'width' => '10', 
        'label' => 'LBL_CREATED'),






    'ASSIGNED_USER_NAME' => array(
        'width' => '10', 
        'label' => 'LBL_LIST_ASSIGNED_USER',
        'default' => true),
    'MODIFIED_BY_NAME' => array(
        'width' => '10', 
        'label' => 'LBL_MODIFIED')
);
?>
