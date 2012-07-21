<?php

  /**
   * Asterisk SugarCRM Integration
   * (c) KINAMU Business Solutions AG 2009
   *
   * Parts of this code are (c) 2006. RustyBrick, Inc.  http://www.rustybrick.com/
   * Parts of this code are (c) 2008 vertico software GmbH
   * Parts of this code are (c) 2009 abcona e. K. Angelo Malaguarnera E-Mail admin@abcona.de
   * http://www.sugarforge.org/projects/yaai/
   * Changes to make this package work with SugarCRM v6 and Asterisk 1.6 and 1.8 by Sebastiaan Tieland
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
   **/
$manifest = array (
                   'acceptable_sugar_versions' =>
                   array (
                          'exact_matches' =>
                          array (
								1 => '6.4.0',
                                 ),
                          'regex_matches' =>
                          array (
							1 => '6\.4\.\d',
							2 => '6\.[0-5]\.\d',  /** matches 6.1.x,6.2.x,6.3.x,6.4.x,6.5.x **/
                                 ),
                          ),
                   'acceptable_sugar_flavors' =>
                   array(
                         'CE'
                         ,'PRO'
                          ,'ENT'
                         ),
                   'readme'=>'README',
                   'key'=>'',
                   'author' => 'Blake Robertson / KINAMU Business Solutions AG / abcona active business consulting',
                   'description' => 'Integrates Asterisk telephony features into SugarCRM.',
                   'icon' => '',
                   'is_uninstallable' => true,
                   'name' => 'Asterisk SugarCRM Connector',
                   'published_date' => '2012-07-21',
                   'type' => 'module',
                   'version' => '2.4.0 for v6.x',
                   'remove_tables' => 'true',  /** This does absolutely nothing since our asterisk log table is created manually instead of as a bean **/
                   );

$installdefs = array (
                      'id' => 'Asterisk_SugarCRM_Connector',
                      'copy' =>
                      array (
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Asterisk',
                                    'to' => 'custom/modules/Asterisk',
                                    ),
									
							/** UNCOMMENT THIS SECTION IF YOU WANT TO OVERWRITE YOUR USER CUSTOMIZATIONS
					         array (
                                    'from' => '<basepath>/SugarModules/modules/Users/DetailView.tpl',
                                    'to' => 'custom/modules/Users/DetailView.tpl',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Users/EditView.tpl',
                                    'to' => 'custom/modules/Users/EditView.tpl',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Users/Save.php',
                                    'to' => 'custom/modules/Users/Save.php',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Users/DetailView.php',
                                    'to' => 'custom/modules/Users/DetailView.php',
                                    ),
							 array (
                                    'from' => '<basepath>/SugarModules/modules/Users/EditView.php',
                                    'to' => 'custom/modules/Users/EditView.php',
                                    ),
							 array (
									'from' => '<basepath>/SugarModules/modules/Users/studio.php',
									'to' => 'modules/Users/studio.php',
									),
							**/
							
							/** 
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Accounts/metadata/detailviewdefs.php',
                                    'to' => 'custom/modules/Accounts/metadata/detailviewdefs.php',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Accounts/metadata/detailviewdefs.php',
                                    'to' => 'custom/working/modules/Accounts/metadata/detailviewdefs.php',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Accounts/metadata/listviewdefs.php',
                                    'to' => 'custom/modules/Accounts/metadata/listviewdefs.php',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Accounts/metadata/listviewdefs.php',
                                    'to' => 'custom/working/modules/Accounts/metadata/listviewdefs.php',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Contacts/metadata/detailviewdefs.php',
                                    'to' => 'custom/modules/Contacts/metadata/detailviewdefs.php',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Contacts/metadata/detailviewdefs.php',
                                    'to' => 'custom/working/modules/Contacts/metadata/detailviewdefs.php',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Contacts/metadata/listviewdefs.php',
                                    'to' => 'custom/modules/Contacts/metadata/listviewdefs.php',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Contacts/metadata/listviewdefs.php',
                                    'to' => 'custom/working/modules/Contacts/metadata/listviewdefs.php',
                                    ),
							*/

                             array (
                                    'from' => '<basepath>/SugarModules/include/SugarFields/Fields/Phone/ListView.tpl',
                                    'to' => 'custom/include/SugarFields/Fields/Phone/ListView.tpl',
                             ),

                             array (
                                    'from' => '<basepath>/SugarModules/include/javascript/jquery/jquery.pack.js',
                                    'to' => 'custom/include/javascript/jquery/jquery.pack.js',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Configurator/asterisk_configurator.php',
                                    'to' => 'custom/modules/Configurator/asterisk_configurator.php',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Configurator/asterisk_configurator.tpl',
                                    'to' => 'custom/modules/Configurator/asterisk_configurator.tpl',
                                    ),
							
                           
							/*
							array (
                                    'from' => '<basepath>/SugarModules/modules/Calls/metadata/listviewdefs.php',
                                    'to' => 'custom/modules/Calls/metadata/listviewdefs.php',
                                    ),
							array (
                                    'from' => '<basepath>/SugarModules/modules/Calls/metadata/quickcreatedefs.php',
                                    'to' => 'custom/modules/Calls/metadata/quickcreatedefs.php',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Calls/metadata/quickcreatedefs.php',
                                    'to' => 'custom/working/modules/Calls/metadata/quickcreatedefs.php',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Calls/metadata/detailviewdefs.php',
                                    'to' => 'custom/modules/Calls/metadata/detailviewdefs.php',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Calls/metadata/detailviewdefs.php',
                                    'to' => 'custom/working/modules/Calls/metadata/detailviewdefs.php',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Calls/metadata/editviewdefs.php',
                                    'to' => 'custom/modules/Calls/metadata/editviewdefs.php',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Calls/metadata/editviewdefs.php',
                                    'to' => 'custom/working/modules/Calls/metadata/editviewdefs.php',
                                    ),
							array (
                                    'from' => '<basepath>/SugarModules/modules/Calls/views/view.list.php',
                                    'to' => 'custom/modules/Calls/views/view.list.php',
                                    ),
                             
							 */
							 ),
							 

                      'administration' =>
                      array (
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Administration/asterisk_configurator.php',
                                    ),
                             ),

                      'language' =>
                      array (
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Users/language/en_us.lang.php',
                                    'to_module' => 'Users',
                                    'language' => 'en_us',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Users/language/ge_ge.lang.php',
                                    'to_module' => 'Users',
                                    'language' => 'ge_ge',
                                    ),
							 array (
                                    'from' => '<basepath>/SugarModules/modules/Users/language/ru_ru.lang.php',
                                    'to_module' => 'Users',
                                    'language' => 'ru_ru',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Administration/language/en_us.lang.php',
                                    'to_module' => 'Administration',
                                    'language' => 'en_us',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Administration/language/ge_ge.lang.php',
                                    'to_module' => 'Administration',
                                    'language' => 'ge_ge',
                                    ),
							 array (
                                    'from' => '<basepath>/SugarModules/modules/Administration/language/ru_ru.lang.php',
                                    'to_module' => 'Administration',
                                    'language' => 'ru_ru',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Configurator/language/en_us.lang.php',
                                    'to_module' => 'Configurator',
                                    'language' => 'en_us',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Configurator/language/ge_ge.lang.php',
                                    'to_module' => 'Configurator',
                                    'language' => 'ge_ge',
                                    ),
							 array (
                                    'from' => '<basepath>/SugarModules/modules/Configurator/language/ru_ru.lang.php',
                                    'to_module' => 'Configurator',
                                    'language' => 'ru_ru',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Calls/language/en_us.lang.php',
                                    'to_module' => 'Calls',
                                    'language' => 'en_us',
                                    ),
                             array (
                                    'from' => '<basepath>/SugarModules/modules/Calls/language/ge_ge.lang.php',
                                    'to_module' => 'Calls',
                                    'language' => 'ge_ge',
                                    ),
							 array (
                                    'from' => '<basepath>/SugarModules/modules/Calls/language/ru_ru.lang.php',
                                    'to_module' => 'Calls',
                                    'language' => 'ru_ru',
                                    ),
							 array (
                                    'from' => '<basepath>/SugarModules/include/language/call_status_dom__en_us.lang.php',
                                    'to_module' => 'application',
                                    'language' => 'en_us',
                                    ),
							 array (
                                    'from' => '<basepath>/SugarModules/include/language/call_status_dom__ge_ge.lang.php',
                                    'to_module' => 'application',
                                    'language' => 'ge_ge',
                                    ),
							 array (
                                    'from' => '<basepath>/SugarModules/include/language/call_status_dom__ru_ru.lang.php',
                                    'to_module' => 'application',
                                    'language' => 'ru_ru',
                                    ),
                            ),

                      'custom_fields' =>
                      array (
                             array (
                                    'id' => 'Usersasterisk_outbound_c',
                                    'name' => 'asterisk_outbound_c',
                                    'label' => 'LBL_ASTERISK_OUTBOUND',
                                    'comments' => NULL,
                                    'help' => NULL,
                                    'module' => 'Users',
                                    'type' => 'bool',
                                    'max_size' => '45',
                                    'require_option' => '0',
                                    'default_value' => 0,
                                    'date_modified' => '2009-05-22 00:00:00',
                                    'deleted' => '0',
                                    'audited' => '0',
                                    'mass_update' => '0',
                                    'duplicate_merge' => '0',
                                    'reportable' => '1',
                                    'ext1' => NULL,
                                    'ext2' => NULL,
                                    'ext3' => NULL,
                                    'ext4' => NULL,
                                    ),

                             array (
                                    'id' => 'Usersasterisk_inbound_c',
                                    'name' => 'asterisk_inbound_c',
                                    'label' => 'LBL_ASTERISK_INBOUND',
                                    'comments' => NULL,
                                    'help' => NULL,
                                    'module' => 'Users',
                                    'type' => 'bool',
                                    'max_size' => '45',
                                    'require_option' => '0',
                                    'default_value' => 0,
                                    'date_modified' => '2009-05-22 00:00:00',
                                    'deleted' => '0',
                                    'audited' => '0',
                                    'mass_update' => '0',
                                    'duplicate_merge' => '0',
                                    'reportable' => '1',
                                    'ext1' => NULL,
                                    'ext2' => NULL,
                                    'ext3' => NULL,
                                    'ext4' => NULL,
                                    ),

                             array (
                                    'id' => 'Usersasterisk_ext_c',
                                    'name' => 'asterisk_ext_c',
                                    'label' => 'LBL_ASTERISK_EXT',
                                    'comments' => NULL,
                                    'help' => NULL,
                                    'module' => 'Users',
                                    'type' => 'varchar',
                                    'max_size' => '45',
                                    'require_option' => '0',
                                    'default_value' => NULL,
                                    'date_modified' => '2009-05-22 00:00:00',
                                    'deleted' => '0',
                                    'audited' => '0',
                                    'mass_update' => '0',
                                    'duplicate_merge' => '0',
                                    'reportable' => '1',
                                    'ext1' => NULL,
                                    'ext2' => NULL,
                                    'ext3' => NULL,
                                    'ext4' => NULL,
                                    ),
                             array (
                                    'id' => 'Callsasterisk_caller_id_c',
                                    'name' => 'asterisk_caller_id_c',
                                    'label' => 'LBL_ASTERISK_CALLER_ID',
                                    'comments' => NULL,
                                    'help' => 'trimmed callerId',
                                    'module' => 'Calls',
                                    'type' => 'varchar',
                                    'max_size' => '45',
                                    'require_option' => '0',
                                    'default_value' => NULL,
                                    'date_modified' => '2009-06-18 15:38:48',
                                    'deleted' => '0',
                                    'audited' => '0',
                                    'mass_update' => '0',
                                    'duplicate_merge' => '0',
                                    'reportable' => '0',
                                    'importable' => 'true',
                                    'ext1' => NULL,
                                    'ext2' => NULL,
                                    'ext3' => NULL,
                                    'ext4' => NULL,
                                    ),
                             ),
							 
						"logic_hooks" => 
							array (
								   array(
								  'module' => '',
								  'hook' => 'after_ui_frame',
								  'order' => 11,
								  'description' => 'Adds asterisk related javascript to page to enable Click To Dial/Logging',
								  'file' => 'custom/modules/Asterisk/include/AsteriskJS.php',
								  'class' => 'AsteriskJS',
								  'function' => 'echoJavaScript',
									),
							),
							
                      );

?>
