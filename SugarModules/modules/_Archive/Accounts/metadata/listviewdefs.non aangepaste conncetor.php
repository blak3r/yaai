<?php

/*********************************************************************************
 * 
 * STARFACE SugarCRM Connector is a computer telephony integration module for the
 * SugarCRM customer relationship managment program by SugarCRM, Inc.
 * Parts of this code are (c) 2009 abcona e. K. Angelo Malaguarnera E-Mail admin@abcona.de
 * http://www.sugarforge.org/projects/yaai/
 *
 * Copyright (C) 2008 vertico software GmbH
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

$listViewDefs['Accounts'] = array(
	'NAME' => array(
		'width' => '40', 
		'label' => 'LBL_LIST_ACCOUNT_NAME', 
		'link' => true,
        'default' => true), 
	'BILLING_ADDRESS_CITY' => array(
		'width' => '10', 
		'label' => 'LBL_LIST_CITY',
        'default' => true 
		),
	'PHONE_OFFICE' => array(
		'width' => '10', 
		'label' => 'LBL_LIST_PHONE',
        'default' => true,
		'customCode' => '<span class="asterisk_phoneNumber" id="">{$PHONE_OFFICE}</span>&nbsp;&nbsp;',
		),
    'ACCOUNT_TYPE' => array(
        'width' => '10', 
        'label' => 'LBL_TYPE'),
    'INDUSTRY' => array(
        'width' => '10', 
        'label' => 'LBL_INDUSTRY'),
    'ANNUAL_REVENUE' => array(
        'width' => '10', 
        'label' => 'LBL_ANNUAL_REVENUE'),
    'PHONE_FAX' => array(
        'width' => '10', 
        'label' => 'LBL_PHONE_FAX'),
    'BILLING_ADDRESS_STREET' => array(
        'width' => '15', 
        'label' => 'LBL_BILLING_ADDRESS_STREET'),
    'BILLING_ADDRESS_STATE' => array(
        'width' => '7', 
        'label' => 'LBL_BILLING_ADDRESS_STATE'),
    'BILLING_ADDRESS_POSTALCODE' => array(
        'width' => '10', 
        'label' => 'LBL_BILLING_ADDRESS_POSTALCODE'),
    'BILLING_ADDRESS_COUNTRY' => array(
        'width' => '10', 
        'label' => 'LBL_BILLING_ADDRESS_COUNTRY'),
    'SHIPPING_ADDRESS_STREET' => array(
        'width' => '15', 
        'label' => 'LBL_SHIPPING_ADDRESS_STREET'),
    'SHIPPING_ADDRESS_CITY' => array(
        'width' => '10', 
        'label' => 'LBL_SHIPPING_ADDRESS_CITY'),
    'SHIPPING_ADDRESS_STATE' => array(
        'width' => '7', 
        'label' => 'LBL_SHIPPING_ADDRESS_STATE'),
    'SHIPPING_ADDRESS_POSTALCODE' => array(
        'width' => '10', 
        'label' => 'LBL_SHIPPING_ADDRESS_POSTALCODE'),
    'SHIPPING_ADDRESS_COUNTRY' => array(
        'width' => '10', 
        'label' => 'LBL_SHIPPING_ADDRESS_COUNTRY'),
    'RATING' => array(
        'width' => '10', 
        'label' => 'LBL_RATING'),
    'PHONE_ALTERNATE' => array(
        'width' => '10', 
        'label' => 'LBL_PHONE_ALT',
 	  'customCode' => '<span class="asterisk_phoneNumber" id="">{$PHONE_ALTERNATE}</span>&nbsp;&nbsp;'),
    'WEBSITE' => array(
        'width' => '10', 
        'label' => 'LBL_WEBSITE'),
    'OWNERSHIP' => array(
        'width' => '10', 
        'label' => 'LBL_OWNERSHIP'),
    'EMPLOYEES' => array(
        'width' => '10', 
        'label' => 'LBL_EMPLOYEES'),
    'SIC_CODE' => array(
        'width' => '10', 
        'label' => 'LBL_SIC_CODE'),
    'TICKER_SYMBOL' => array(
        'width' => '10', 
        'label' => 'LBL_TICKER_SYMBOL'),
    'DATE_MODIFIED' => array(
        'width' => '5', 
        'label' => 'LBL_DATE_MODIFIED'),
    'DATE_ENTERED' => array(
        'width' => '5', 
        'label' => 'LBL_DATE_ENTERED'),
    'CREATED_BY_NAME' => array(
        'width' => '10', 
        'label' => 'LBL_CREATED'),






    'ASSIGNED_USER_NAME' => array(
        'width' => '2', 
        'label' => 'LBL_LIST_ASSIGNED_USER',
        'default' => true),
    'MODIFIED_BY_NAME' => array(
        'width' => '2', 
        'label' => 'LBL_MODIFIED')
);
?>
