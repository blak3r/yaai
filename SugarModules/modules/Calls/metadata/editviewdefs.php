<?php
/*********************************************************************************
 * SugarCRM is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2011 SugarCRM Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/

$viewdefs ['Calls'] = 
array (
  'EditView' => 
  array (
    'templateMeta' => 
    array (
      'maxColumns' => '2',
      'form' => 
      array (
        'hidden' => 
        array (
          0 => '<input type="hidden" name="isSaveAndNew" value="false">',
          1 => '<input type="hidden" name="send_invites">',
          2 => '<input type="hidden" name="user_invitees">',
          3 => '<input type="hidden" name="lead_invitees">',
          4 => '<input type="hidden" name="contact_invitees">',
        ),
        'buttons' => 
        array (
          0 => 
          array (
			'customCode' => '<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="button primary" onclick="fill_invitees();document.forms[\'EditView\'].action.value=\'Save\'; document.forms[\'EditView\'].return_action.value=\'DetailView\'; {if isset($smarty.request.isDuplicate) && $smarty.request.isDuplicate eq "true"}document.forms[\'EditView\'].return_id.value=\'\'; {/if}formSubmitCheck();;" type="button" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">',            
          ),
          1 => 'CANCEL',
          2 => 
          array (
			'customCode' => '<input title="{$MOD.LBL_SEND_BUTTON_TITLE}" class="button" onclick="document.forms[\'EditView\'].send_invites.value=\'1\';fill_invitees();document.forms[\'EditView\'].action.value=\'Save\';document.forms[\'EditView\'].return_action.value=\'EditView\';document.forms[\'EditView\'].return_module.value=\'{$smarty.request.return_module}\';formSubmitCheck();;" type="button" name="button" value="{$MOD.LBL_SEND_BUTTON_LABEL}">',            
          ),
          3 => 
          array (
			'customCode' => '{if $fields.status.value != "Held"}<input title="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}" accessKey="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_KEY}" class="button" onclick="fill_invitees(); document.forms[\'EditView\'].status.value=\'Held\'; document.forms[\'EditView\'].action.value=\'Save\'; document.forms[\'EditView\'].return_module.value=\'Calls\'; document.forms[\'EditView\'].isDuplicate.value=true; document.forms[\'EditView\'].isSaveAndNew.value=true; document.forms[\'EditView\'].return_action.value=\'EditView\'; document.forms[\'EditView\'].return_id.value=\'{$fields.id.value}\'; formSubmitCheck();" type="button" name="button" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_LABEL}">{/if}',
          ),
        ),
        'footerTpl' => 'modules/Calls/tpls/footer.tpl',
      ),
      'widths' => 
      array (
        0 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
        1 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
      ),
      'javascript' => '<script type="text/javascript" src="include/JSON.js?s=94932f0dc915603816562a2cc59dbcd0&c=1"></script>
<script type="text/javascript" src="include/jsolait/init.js?s=94932f0dc915603816562a2cc59dbcd0&c=1"></script>
<script type="text/javascript" src="include/jsolait/lib/urllib.js?s=94932f0dc915603816562a2cc59dbcd0&c=1"></script>
<script type="text/javascript">{$JSON_CONFIG_JAVASCRIPT}</script>
<script type="text/javascript" src="include/javascript/jsclass_base.js?s=94932f0dc915603816562a2cc59dbcd0&c=1"></script>
<script type="text/javascript" src="include/javascript/jsclass_async.js?s=94932f0dc915603816562a2cc59dbcd0&c=1"></script>
<script type="text/javascript" src="modules/Meetings/jsclass_scheduler.js?s=94932f0dc915603816562a2cc59dbcd0&c=1"></script>
<script>toggle_portal_flag();function toggle_portal_flag()  {ldelim} {$TOGGLE_JS} {rdelim} 
function formSubmitCheck(){ldelim}if(check_form(\'EditView\') && isValidDuration()){ldelim}document.forms[\'EditView\'].submit();{rdelim}{rdelim}</script>',
       'useTabs' => false,
    ),
    'panels' => 
    array (
      'lbl_call_information' => 
      array (
        array (
          'name' => 'name',
          array (
            'name' => 'status',
            'fields' => 
            array (
              array (
                'name' => 'direction',
              ),
              array (
                'name' => 'status',
              ),
            ),
          ),
        ),
        array (
          array (
            'name' => 'date_start',
            'displayParams' => 
            array (
              'updateCallback' => 'SugarWidgetScheduler.update_time();',
            ),
            'label' => 'LBL_DATE_TIME',
          ),
          array (
            'name' => 'parent_name',
            'label' => 'LBL_LIST_RELATED_TO',
          ),
        ),
        array (
          array (
            'name' => 'duration_hours',
            'label' => 'LBL_DURATION',
            'customCode' => '{literal}<script type="text/javascript">function isValidDuration() { form = document.getElementById(\'EditView\'); if ( form.duration_hours.value + form.duration_minutes.value <= 0 ) { alert(\'{/literal}{$MOD.NOTICE_DURATION_TIME}{literal}\'); return false; } return true; }</script>{/literal}<input id="duration_hours" name="duration_hours" tabindex="1" size="2" maxlength="2" type="text" value="{$fields.duration_hours.value}" onkeyup="SugarWidgetScheduler.update_time();"/>{$fields.duration_minutes.value}&nbsp;<span class="dateFormat">{$MOD.LBL_HOURS_MINUTES}</span>',
            
          ),
          array (
            'name' => 'reminder_time',
            'customCode' => '{if $fields.reminder_checked.value == "1"}{assign var="REMINDER_TIME_DISPLAY" value="inline"}{assign var="REMINDER_CHECKED" value="checked"}{else}{assign var="REMINDER_TIME_DISPLAY" value="none"}{assign var="REMINDER_CHECKED" value=""}{/if}<input name="reminder_checked" type="hidden" value="0"><input name="reminder_checked" onclick=\'toggleDisplay("should_remind_list");\' type="checkbox" class="checkbox" value="1" {$REMINDER_CHECKED}><div id="should_remind_list" style="display:{$REMINDER_TIME_DISPLAY}">{$fields.reminder_time.value}</div>',
            'label' => 'LBL_REMINDER',
          ),
        ),
        array (
          array (
            'name' => 'description',
            'comment' => 'Full text of the note',
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
      ),
      'LBL_PANEL_ASSIGNMENT' => 
      array (
        array (
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
        ),
      ),
    ),
  ),
);
?>
