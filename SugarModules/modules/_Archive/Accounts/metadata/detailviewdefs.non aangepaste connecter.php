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

$viewdefs['Accounts']['DetailView'] = array(
    'templateMeta' => array('form' => array('buttons'=>array('EDIT', 'DUPLICATE', 'DELETE', 'FIND_DUPLICATES')),
                            'maxColumns' => '2', 
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'), 
                                            array('label' => '10', 'field' => '30')
                                            ),
                            'includes'=> array(
                                            array('file'=>'modules/Accounts/Account.js'),
                                         ),
                           ),
    'panels' => array(
	  array('name',
        	array (
	      	'name' => 'phone_office',
	      	'customCode' => '<span class="asterisk_phoneNumber" id="asterisk_phoneOffice">{$fields.phone_office.value} </span>&nbsp;&nbsp;' 
	  	)
	  ),  
        array(array('name'=>'website', 'type'=>'link'), 'phone_fax'),
        array('ticker_symbol',
		array('
			name'=>'phone_alternate',
			'label'=>'LBL_OTHER_PHONE',
			'customCode' => '<span class="asterisk_phoneNumber" id="asterisk_phoneAlternate">{$fields.phone_alternate.value} </span>&nbsp;&nbsp;'
		)
	  ),
        array('parent_name', 'employees'),
        array('ownership', 'rating'),
        array('industry', 'sic_code'),
        array('account_type', 'annual_revenue'),
		array(
		      array('name'=>'date_modified', 'label'=>'LBL_DATE_MODIFIED', 'customCode'=>'{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}')),
		array(array('name'=>'assigned_user_name', 'label'=>'LBL_ASSIGNED_TO'),
              array('name'=>'date_entered', 'customCode'=>'{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}')),
		array (
		      array (
			  'name' => 'billing_address_street',
		      'label'=> 'LBL_BILLING_ADDRESS',
		      'type' => 'address',
		      'displayParams'=>array('key'=>'billing'),
		      ),
		array (
		      'name' => 'shipping_address_street',
		      'label'=> 'LBL_SHIPPING_ADDRESS',
		      'type' => 'address',
		      'displayParams'=>array('key'=>'shipping'),      
		      ),
		),

	    array('description'),
	    array('email1'),		      
     ),
    
    
);
?>
