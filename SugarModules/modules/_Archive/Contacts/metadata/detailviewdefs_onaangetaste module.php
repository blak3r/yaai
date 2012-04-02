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

$viewdefs['Contacts']['DetailView'] = array(
'templateMeta' => array('preForm' => '<form name="vcard" action="vCard.php">' .
		                             '<input type="hidden" name="contact_id" value="{$fields.id.value}">' .
		                             '<input type="hidden" name="module" value="Contact">' .
		                             '</form>',
                        'form' => array('buttons'=>array('EDIT', 'DUPLICATE', 'DELETE', 'FIND_DUPLICATES', 
                                                         array('customCode'=>'<input title="{$APP.LBL_MANAGE_SUBSCRIPTIONS}" class="button" onclick="this.form.return_module.value=\'Contacts\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'Subscriptions\'; this.form.module.value=\'Campaigns\';" type="submit" name="Manage Subscriptions" value="{$APP.LBL_MANAGE_SUBSCRIPTIONS}">')),
                                       ),
                        'maxColumns' => '2', 
                        'widths' => array(
                                        array('label' => '10', 'field' => '30'), 
                                        array('label' => '10', 'field' => '30')
                                        ),
                        'includes'=> array(
                            			array('file'=>'modules/Leads/Lead.js'),
//                            			array('file'=>'include/javascript/jquery/jquery-1.2.3.min.js'),
//                            			array('file'=>'modules/Asterisk/include/javascript/functions.js'),
                         				),		                
                        ),
'panels' =>array (

      array (
	    array (
	      'name' => 'full_name',
	      'customCode' => '{$fields.full_name.value}&nbsp;&nbsp;<input type="button" class="button" name="vCardButton" value="{$MOD.LBL_VCARD}" onClick="document.vcard.submit();">',
	      'label' => 'LBL_NAME',
	    ),
	
	    array (
	      'name' => 'phone_work',
	      'label' => 'LBL_OFFICE_PHONE',
	      'customCode' => '<span class="asterisk_phoneNumber" id="asterisk_phoneWork">{$fields.phone_work.value}</span>&nbsp;&nbsp;',
	    ),
	  ),
	  
	  array (
	    'account_name',
	    
	    array (
	      'name' => 'phone_mobile',
	      'label' => 'LBL_MOBILE_PHONE',
	      'customCode' => '<span class="asterisk_phoneNumber" id="asterisk_phoneMobile">{$fields.phone_mobile.value}</span>&nbsp;&nbsp;', 
	    ),
	  ),
	  
	  array (
	    'lead_source',
	    
	    array (
	      'name' => 'phone_home',
	      'label' => 'LBL_HOME_PHONE',
	      'customCode' => '<span class="asterisk_phoneNumber" id="asterisk_phoneHome">{$fields.phone_home.value}</span>&nbsp;&nbsp;',
	    ),
	  ),
	  
	  array (
	    
	    array (
	      'name' => 'campaign_name',
	      'label' => 'LBL_CAMPAIGN',
	    ),
	    
	    array (
	      'name' => 'phone_other',
	      'label' => 'LBL_OTHER_PHONE',
	      'customCode' => '<span class="asterisk_phoneNumber" id="asterisk_phoneOther">{$fields.phone_other.value}</span>&nbsp;&nbsp;',
	    ),
	  ),
	  
	  array (
	    'title',
	    array (
	      'name' => 'phone_fax',
	      'label' => 'LBL_FAX_PHONE',
	    ),
	  ),
	  
	  array (
	    'department',
	    'birthdate',
	  ),
	  
	  array (
	    'report_to_name',
	    'assistant',
	  ),
	  
	  array (
	    'sync_contact',
	    array (
	      'name' => 'assistant_phone',
	      'customCode' => '<span class="asterisk_phoneNumber" id="asterisk_assistantPhone">{$fields.assistant_phone.value}</span>&nbsp;&nbsp;',
	    ),
	  ),
	  
	  array (
	    'do_not_call',
	    '',
	  ),
	  
	  array (



	    
	    array (
	      'name' => 'date_modified',
	      'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
	      'label' => 'LBL_DATE_MODIFIED',
	    ),
	  ),
	  
	  array (
	    'assigned_user_name',
	    
	    array (
	      'name' => 'date_entered',
	      'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
	      'label' => 'LBL_DATE_ENTERED',
	    ),
	  ),
	  
	  array (
	      array (
		      'name' => 'primary_address_street',
		      'label'=> 'LBL_PRIMARY_ADDRESS',
		      'type' => 'address',
		      'displayParams'=>array('key'=>'primary'),
	      ),
	      
	      array (
		      'name' => 'alt_address_street',
		      'label'=> 'LBL_ALTERNATE_ADDRESS',
		      'type' => 'address',
		      'displayParams'=>array('key'=>'alt'),      
	      ),
	  ),  
	  array (
	    array('name'=>'portal_name',
	          'customCode'=>'{if $PORTAL_ENABLED}{$fields.portal_name.value}{/if}',
	          'customLabel'=>'{if $PORTAL_ENABLED}{sugar_translate label="LBL_PORTAL_NAME" module="Contacts"}{/if}'),
	    array('name'=>'portal_active',
	          'customCode'=>'{if $PORTAL_ENABLED}
	          		         {if strval($fields.portal_active.value) == "1" || strval($fields.portal_active.value) == "yes" || strval($fields.portal_active.value) == "on"}
	          		         {assign var="checked" value="CHECKED"}
                             {else}
                             {assign var="checked" value=""}
                             {/if}
                             <input type="checkbox" class="checkbox" name="{$fields.portal_active.name}" size="{$displayParams.size}" disabled="true" {$checked}>
                             {/if}',
              'customLabel'=>'{if $PORTAL_ENABLED}{sugar_translate label="LBL_PORTAL_ACTIVE" module="Contacts"}{/if}'),
	  ),
	  array (
	    'description',
	  ),
	  
	  array (
	    'email1',
	  ),

)


   
);
?>
