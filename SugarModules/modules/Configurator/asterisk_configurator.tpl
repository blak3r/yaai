{*

 **
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
 *
*}
<script type='text/javascript' src='include/javascript/overlibmws.js'></script>
<BR>
<form name="ConfigureSettings" enctype='multipart/form-data' method="POST" action="index.php" onSubmit="return (add_checks(document.ConfigureSettings) && check_form('ConfigureSettings'));">
<input type='hidden' name='action' value='asterisk_configurator'/>
<input type='hidden' name='module' value='Configurator'/>
<span class='error'>{$error.main}</span>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
			
	<td style="padding-bottom: 2px;">
		<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="button"  type="submit"  name="save" value="  {$APP.LBL_SAVE_BUTTON_LABEL}  " >
		&nbsp;<input title="{$MOD.LBL_SAVE_BUTTON_TITLE}"  class="button"  type="submit" name="restore" value="  {$MOD.LBL_RESTORE_BUTTON_LABEL}  " > 
		&nbsp;<input title="{$MOD.LBL_CANCEL_BUTTON_TITLE}"  onclick="document.location.href='index.php?module=Administration&action=index'" class="button"  type="button" name="cancel" value="  {$APP.LBL_CANCEL_BUTTON_LABEL}  " > 
	</td>	
	</tr>
<tr><td>
<br>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
	<tr><th align="left" class="dataLabel" colspan="4"><h4 class="dataLabel">{$MOD.LBL_MANAGE_ASTERISK}</h4></th>
	</tr><tr>
<td>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_HOST}: </td>
		<td width="25%" class="dataField">
		{if empty($config.asterisk_host )}
			{assign var='asterisk_host' value=$asterisk_config.asterisk_host}
		{else}
			{assign var='asterisk_host' value=$config.asterisk_host}
		{/if}
			<input type='text' name='asterisk_host' size="45" value='{$asterisk_host}'>
		</td>
		<td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_PORT}: </td>
		<td width="25%" class="dataField">
		{if empty($config.asterisk_port )}
			{assign var='asterisk_port' value=$asterisk_config.asterisk_port}
		{else}
			{assign var='asterisk_port' value=$config.asterisk_port}
		{/if}
			<input type='text' name='asterisk_port' size="45" value='{$asterisk_port}'>
		</td>
	</tr><tr>
		<td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_USER}: </td>
		<td width="25%" class="dataField">
		{if empty($config.asterisk_user )}
			{assign var='asterisk_user' value=$asterisk_config.asterisk_user}
		{else}
			{assign var='asterisk_user' value=$config.asterisk_user}
		{/if}
			<input type='text' name='asterisk_user' size="45" value='{$asterisk_user}'>
		</td>
		<td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_SECRET}: </td>
		<td width="25%" class="dataField">
		{if empty($config.asterisk_secret )}
			{assign var='asterisk_secret' value=$asterisk_config.asterisk_secret}
		{else}
			{assign var='asterisk_secret' value=$config.asterisk_secret}
		{/if}
			<input type='text' name='asterisk_secret' size="45" value='{$asterisk_secret}'>
		</td>
	</tr><tr>
		<td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_CONTEXT}: </td>
		<td width="25%" class="dataField">
		{if empty($config.asterisk_context )}
			{assign var='asterisk_context' value=$asterisk_config.asterisk_context}
		{else}
			{assign var='asterisk_context' value=$config.asterisk_context}
		{/if}
			<input type='text' name='asterisk_context' size="45" value='{$asterisk_context}'>
		</td>
	
	</tr><tr>
		<td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_PREFIX}: </td>
		<td width="25%" class="dataField">
		{if empty($config.asterisk_prefix )}
			{assign var='asterisk_prefix' value=$asterisk_config.asterisk_prefix}
		{else}
			{assign var='asterisk_prefix' value=$config.asterisk_prefix}
		{/if}
			<input type='text' name='asterisk_prefix' size="45" value='{$asterisk_prefix}'>
		</td>
		<td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_DIALINPREFIX}: </td>
		<td width="25%" class="dataField">
		{if empty($config.asterisk_dialinPrefix )}
			{assign var='asterisk_dialinPrefix' value=$asterisk_config.asterisk_dialinPrefix}
		{else}
			{assign var='asterisk_dialinPrefix' value=$config.asterisk_dialinPrefix}
		{/if}
			<input type='text' name='asterisk_dialinPrefix' size="45" value='{$asterisk_dialinPrefix}'>
		</td>		
	</tr><tr>
		<td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_SOAPUSER}: </td>
		<td width="25%" class="dataField">
		{if empty($config.asterisk_soapuser )}
			{assign var='asterisk_soapuser' value=$asterisk_config.asterisk_soapuser}
		{else}
			{assign var='asterisk_soapuser' value=$config.asterisk_soapuser}
		{/if}
			<input type='text' name='asterisk_soapuser' size="45" value='{$asterisk_soapuser}'>
            <br/><span style="font-size:0.7em;color: gray;">*Note this is a SugarCRM user (Not an Asterisk User) that will be used by asteriskLogger when making SOAP calls.</span>
		</td>
        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_SOAPPASS}: </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_soappass )}
            {assign var='asterisk_soapppass' value=$asterisk_config.asterisk_soappass}
            {else}
            {assign var='asterisk_soapppass' value=$config.asterisk_soappass}
        {/if}
            <input type='text' name='asterisk_soappass' size="45" value='{$asterisk_soappass}'>
            <br/><span style="font-size:0.9em;color: gray;">*Password may not be displayed here after reloading page.  I'm not sure why but saving still works.  I thought this behavior was actually ideal for security purposes!  When in doubt, look in config.override.ini for asterisk_soappass to confirm..</span>
        </td>
	</tr>

    <tr>
        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_EXPR}: </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_expr )}
            {assign var='asterisk_expr' value=$asterisk_config.asterisk_expr}
            {else}
            {assign var='asterisk_expr' value=$config.asterisk_expr}
        {/if}
            <input type='text' name='asterisk_expr' size="45" value='{$asterisk_expr}'>
        </td>
        <TD>&nbsp;</TD>
        <td>&nbsp;</td>
    </tr>

	
	<!-- Added in yaii 2.0 -->

	</tr><tr>
		<td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_DIALOUT_CHANNEL}: </td>
		<td width="25%" class="dataField">
		{if empty($config.asterisk_dialout_channel )}
			{assign var='asterisk_dialout_channel' value=$asterisk_config.asterisk_dialout_channel}
		{else}
			{assign var='asterisk_dialout_channel' value=$config.asterisk_dialout_channel}
		{/if}
			<input type='text' name='asterisk_dialout_channel' size="45" value='{$asterisk_dialout_channel}'>
		</td>
		<td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_DIALIN_EXT_MATCH}: </td>
		<td width="25%" class="dataField">
		{if empty($config.asterisk_dialin_ext_match )}
			{assign var='asterisk_dialin_ext_match' value=$asterisk_config.asterisk_dialin_ext_match}
		{else}
			{assign var='asterisk_dialin_ext_match' value=$config.asterisk_dialin_ext_match}
		{/if}
			<input type='text' name='asterisk_dialin_ext_match' size="45" value='{$asterisk_dialin_ext_match}'>
		</td>
	</tr>
	
	<tr>
		<td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_CALL_SUBJECT_INBOUND_ABBR}: </td>
		<td width="25%" class="dataField">
		{if empty($config.asterisk_call_subject_inbound_abbr )}
			{assign var='asterisk_call_subject_inbound_abbr' value=$asterisk_config.asterisk_call_subject_inbound_abbr}
		{else}
			{assign var='asterisk_call_subject_inbound_abbr' value=$config.asterisk_call_subject_inbound_abbr}
		{/if}
			<input type='text' name='asterisk_call_subject_inbound_abbr' size="45" value='{$asterisk_call_subject_inbound_abbr}'>
		</td>
		<td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_CALL_SUBJECT_OUTBOUND_ABBR}: </td>
		<td width="25%" class="dataField">
		{if empty($config.asterisk_call_subject_outbound_abbr )}
			{assign var='asterisk_call_subject_outbound_abbr' value=$asterisk_config.asterisk_call_subject_outbound_abbr}
		{else}
			{assign var='asterisk_call_subject_outbound_abbr' value=$config.asterisk_call_subject_outbound_abbr}
		{/if}
			<input type='text' name='asterisk_call_subject_outbound_abbr' size="45" value='{$asterisk_call_subject_outbound_abbr}'>
		</td>
	</tr>

	<tr>
		<td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_CALL_SUBJECT_MAX_LENGTH}: </td>
		<td width="25%" class="dataField">
		{if empty($config.asterisk_call_subject_max_length )}
			{assign var='asterisk_call_subject_max_length' value=$asterisk_config.asterisk_call_subject_max_length}
		{else}
			{assign var='asterisk_call_subject_max_length' value=$config.asterisk_call_subject_max_length}
		{/if}
			<input type='text' name='asterisk_call_subject_max_length' size="45" value='{$asterisk_call_subject_max_length}'>
		</td>
		<td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_LISTENER_POLL_RATE}: </td>
		<td width="25%" class="dataField">
		{if empty($config.asterisk_listener_poll_rate )}
			{assign var='asterisk_listener_poll_rate' value=$asterisk_config.asterisk_listener_poll_rate}
		{else}
			{assign var='asterisk_listener_poll_rate' value=$config.asterisk_listener_poll_rate}
		{/if}
			<input type='text' name='asterisk_listener_poll_rate' size="45" value='{$asterisk_listener_poll_rate}'>
		</td>
	</tr>

	<tr>
		<td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_LOG_FILE}: </td>
		<td width="25%" class="dataField">
		{if empty($config.asterisk_log_file )}
			{assign var='asterisk_log_file' value=$asterisk_config.asterisk_log_file}
		{else}
			{assign var='asterisk_log_file' value=$config.asterisk_log_file}
		{/if}
			<input type='text' name='asterisk_log_file' size="45" value='{$asterisk_log_file}'>
		</td>
		<TD>&nbsp;</TD>
		<td>&nbsp;</td>
	</tr>
	
<!--

	<tr>
		<td nowrap width="10%" class="dataLabel">{$MOD.LBL_LEFT_FIELD}: </td>
		<td width="25%" class="dataField">
		{if empty($config.left_field )}
			{assign var='left_field' value=$asterisk_config.left_field}
		{else}
			{assign var='left_field' value=$config.left_field}
		{/if}
			<input type='text' name='left_field' size="45" value='{$left_field}'>
		</td>
		<TD>&nbsp;</TD>
	</tr>
	
	-->
	
</table>
</td></tr>
</table>	
<td>
<br>
</table>
<div style="padding-top: 2px;">
<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" class="button"  type="submit" name="save" value="  {$APP.LBL_SAVE_BUTTON_LABEL}  " />
		&nbsp;<input title="{$MOD.LBL_SAVE_BUTTON_TITLE}"  class="button"  type="submit" name="restore" value="  {$MOD.LBL_RESTORE_BUTTON_LABEL}  " /> 
		&nbsp;<input title="{$MOD.LBL_CANCEL_BUTTON_TITLE}"  onclick="document.location.href='index.php?module=Administration&action=index'" class="button"  type="button" name="cancel" value="  {$APP.LBL_CANCEL_BUTTON_LABEL}  " />
</div>
{$JAVASCRIPT}

