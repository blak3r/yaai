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

<BR/>
<img src="http://www.blakerobertson.com/ext/yaai-config-logo-26.png" alt="YAAI Logo"><br>

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

<!--
	<tr><th align="left" class="dataLabel" colspan="4"><h4 class="dataLabel">{$MOD.LBL_MANAGE_ASTERISK}</h4></th>
	</tr><tr>
-->
<td>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">

    <!-- ------------------- [ PASTE OUTPUT OF CONFIGURATORGENERATORUTIL BELOW THIS LINE ]----------------------------- -->


    <TR><td colspan="4">&nbsp;&nbsp;<TR><TD colspan="4"><h3>Asterisk Server Settings</h3></TD></tr>



    <TR>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_HOST}
        {if !empty($MOD.LBL_ASTERISK_HOST_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_HOST_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_host )}
            {assign var='asterisk_host' value=$asterisk_config.asterisk_host}
            {else}
            {assign var='asterisk_host' value=$config.asterisk_host}
        {/if}
            <input type='textbox' name='asterisk_host' size="45" value='{$asterisk_host}'>
        </td>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_PORT}
        {if !empty($MOD.LBL_ASTERISK_PORT_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_PORT_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_port )}
            {assign var='asterisk_port' value=$asterisk_config.asterisk_port}
            {else}
            {assign var='asterisk_port' value=$config.asterisk_port}
        {/if}
            <input type='int' name='asterisk_port' size="45" value='{$asterisk_port}'>
        </td>

    <TR>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_USER}
        {if !empty($MOD.LBL_ASTERISK_USER_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_USER_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_user )}
            {assign var='asterisk_user' value=$asterisk_config.asterisk_user}
            {else}
            {assign var='asterisk_user' value=$config.asterisk_user}
        {/if}
            <input type='textbox' name='asterisk_user' size="45" value='{$asterisk_user}'>
        </td>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_SECRET}
        {if !empty($MOD.LBL_ASTERISK_SECRET_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_SECRET_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_secret )}
            {assign var='asterisk_secret' value=$asterisk_config.asterisk_secret}
            {else}
            {assign var='asterisk_secret' value=$config.asterisk_secret}
        {/if}
            <input type='textbox' name='asterisk_secret' size="45" value='{$asterisk_secret}'>
        </td>



    <TR><td colspan="4">&nbsp;&nbsp;<TR><TD colspan="4"><h3>SugarCRM SOAP Settings</h3></TD></tr>



    <TR>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_SOAPUSER}
        {if !empty($MOD.LBL_ASTERISK_SOAPUSER_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_SOAPUSER_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_soapuser )}
            {assign var='asterisk_soapuser' value=$asterisk_config.asterisk_soapuser}
            {else}
            {assign var='asterisk_soapuser' value=$config.asterisk_soapuser}
        {/if}
            <input type='textbox' name='asterisk_soapuser' size="45" value='{$asterisk_soapuser}'>
        </td>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_SOAPPASS}
        {if !empty($MOD.LBL_ASTERISK_SOAPPASS_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_SOAPPASS_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_soappass )}
            {assign var='asterisk_soappass' value=$asterisk_config.asterisk_soappass}
            {else}
            {assign var='asterisk_soappass' value=$config.asterisk_soappass}
        {/if}
            <input type='textbox' name='asterisk_soappass' size="45" value='{$asterisk_soappass}'>
        </td>



    <TR><td colspan="4">&nbsp;&nbsp;<TR><TD colspan="4"><h3>Call Configuration</h3></TD></tr>



    <TR>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_PREFIX}
        {if !empty($MOD.LBL_ASTERISK_PREFIX_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_PREFIX_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_prefix )}
            {assign var='asterisk_prefix' value=$asterisk_config.asterisk_prefix}
            {else}
            {assign var='asterisk_prefix' value=$config.asterisk_prefix}
        {/if}
            <input type='textbox' name='asterisk_prefix' size="45" value='{$asterisk_prefix}'>
        </td>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_DIALINPREFIX}
        {if !empty($MOD.LBL_ASTERISK_DIALINPREFIX_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_DIALINPREFIX_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_dialinPrefix )}
            {assign var='asterisk_dialinPrefix' value=$asterisk_config.asterisk_dialinPrefix}
            {else}
            {assign var='asterisk_dialinPrefix' value=$config.asterisk_dialinPrefix}
        {/if}
            <input type='textbox' name='asterisk_dialinPrefix' size="45" value='{$asterisk_dialinPrefix}'>
        </td>



    <TR>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_CONTEXT}
        {if !empty($MOD.LBL_ASTERISK_CONTEXT_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_CONTEXT_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_context )}
            {assign var='asterisk_context' value=$asterisk_config.asterisk_context}
            {else}
            {assign var='asterisk_context' value=$config.asterisk_context}
        {/if}
            <input type='textbox' name='asterisk_context' size="45" value='{$asterisk_context}'>
        </td>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_EXPR}
        {if !empty($MOD.LBL_ASTERISK_EXPR_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_EXPR_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_expr )}
            {assign var='asterisk_expr' value=$asterisk_config.asterisk_expr}
            {else}
            {assign var='asterisk_expr' value=$config.asterisk_expr}
        {/if}
            <input type='textbox' name='asterisk_expr' size="45" value='{$asterisk_expr}'>
        </td>



    <TR>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_DIALOUT_CHANNEL}
        {if !empty($MOD.LBL_ASTERISK_DIALOUT_CHANNEL_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_DIALOUT_CHANNEL_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_dialout_channel )}
            {assign var='asterisk_dialout_channel' value=$asterisk_config.asterisk_dialout_channel}
            {else}
            {assign var='asterisk_dialout_channel' value=$config.asterisk_dialout_channel}
        {/if}
            <input type='textbox' name='asterisk_dialout_channel' size="45" value='{$asterisk_dialout_channel}'>
        </td>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_DIALIN_EXT_MATCH}
        {if !empty($MOD.LBL_ASTERISK_DIALIN_EXT_MATCH_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_DIALIN_EXT_MATCH_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_dialin_ext_match )}
            {assign var='asterisk_dialin_ext_match' value=$asterisk_config.asterisk_dialin_ext_match}
            {else}
            {assign var='asterisk_dialin_ext_match' value=$config.asterisk_dialin_ext_match}
        {/if}
            <input type='textbox' name='asterisk_dialin_ext_match' size="45" value='{$asterisk_dialin_ext_match}'>
        </td>



    <TR>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_RG_DETECT_EXPR}
        {if !empty($MOD.LBL_ASTERISK_RG_DETECT_EXPR_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_RG_DETECT_EXPR_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_rg_detect_expr )}
            {assign var='asterisk_rg_detect_expr' value=$asterisk_config.asterisk_rg_detect_expr}
            {else}
            {assign var='asterisk_rg_detect_expr' value=$config.asterisk_rg_detect_expr}
        {/if}
            <input type='textbox' name='asterisk_rg_detect_expr' size="45" value='{$asterisk_rg_detect_expr}'>
        </td>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_RG_CELL_RING_EXPR}
        {if !empty($MOD.LBL_ASTERISK_RG_CELL_RING_EXPR_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_RG_CELL_RING_EXPR_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_rg_cell_ring_expr )}
            {assign var='asterisk_rg_cell_ring_expr' value=$asterisk_config.asterisk_rg_cell_ring_expr}
            {else}
            {assign var='asterisk_rg_cell_ring_expr' value=$config.asterisk_rg_cell_ring_expr}
        {/if}
            <input type='textbox' name='asterisk_rg_cell_ring_expr' size="45" value='{$asterisk_rg_cell_ring_expr}'>
        </td>



    <TR><td colspan="4">&nbsp;&nbsp;<TR><TD colspan="4"><h3>Misc</h3></TD></tr>



    <TR>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_CALL_SUBJECT_INBOUND_ABBR}
        {if !empty($MOD.LBL_ASTERISK_CALL_SUBJECT_INBOUND_ABBR_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_CALL_SUBJECT_INBOUND_ABBR_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_call_subject_inbound_abbr )}
            {assign var='asterisk_call_subject_inbound_abbr' value=$asterisk_config.asterisk_call_subject_inbound_abbr}
            {else}
            {assign var='asterisk_call_subject_inbound_abbr' value=$config.asterisk_call_subject_inbound_abbr}
        {/if}
            <input type='textbox' name='asterisk_call_subject_inbound_abbr' size="45" value='{$asterisk_call_subject_inbound_abbr}'>
        </td>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_CALL_SUBJECT_OUTBOUND_ABBR}
        {if !empty($MOD.LBL_ASTERISK_CALL_SUBJECT_OUTBOUND_ABBR_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_CALL_SUBJECT_OUTBOUND_ABBR_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_call_subject_outbound_abbr )}
            {assign var='asterisk_call_subject_outbound_abbr' value=$asterisk_config.asterisk_call_subject_outbound_abbr}
            {else}
            {assign var='asterisk_call_subject_outbound_abbr' value=$config.asterisk_call_subject_outbound_abbr}
        {/if}
            <input type='textbox' name='asterisk_call_subject_outbound_abbr' size="45" value='{$asterisk_call_subject_outbound_abbr}'>
        </td>



    <TR>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_CALL_SUBJECT_MAX_LENGTH}
        {if !empty($MOD.LBL_ASTERISK_CALL_SUBJECT_MAX_LENGTH_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_CALL_SUBJECT_MAX_LENGTH_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_call_subject_max_length )}
            {assign var='asterisk_call_subject_max_length' value=$asterisk_config.asterisk_call_subject_max_length}
            {else}
            {assign var='asterisk_call_subject_max_length' value=$config.asterisk_call_subject_max_length}
        {/if}
            <input type='textbox' name='asterisk_call_subject_max_length' size="45" value='{$asterisk_call_subject_max_length}'>
        </td>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_LISTENER_POLL_RATE}
        {if !empty($MOD.LBL_ASTERISK_LISTENER_POLL_RATE_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_LISTENER_POLL_RATE_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_listener_poll_rate )}
            {assign var='asterisk_listener_poll_rate' value=$asterisk_config.asterisk_listener_poll_rate}
            {else}
            {assign var='asterisk_listener_poll_rate' value=$config.asterisk_listener_poll_rate}
        {/if}
            <input type='textbox' name='asterisk_listener_poll_rate' size="45" value='{$asterisk_listener_poll_rate}'>
        </td>



    <TR>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_OPENCNAM_ENABLED}
        {if !empty($MOD.LBL_ASTERISK_OPENCNAM_ENABLED_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_OPENCNAM_ENABLED_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_opencnam_enabled )}
            {assign var='asterisk_opencnam_enabled' value=$asterisk_config.asterisk_opencnam_enabled}
            {else}
            {assign var='asterisk_opencnam_enabled' value=$config.asterisk_opencnam_enabled}
        {/if}
            <input type='textbox' name='asterisk_opencnam_enabled' size="45" value='{$asterisk_opencnam_enabled}'>
        </td>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_OPENCNAM_USERNAME}
        {if !empty($MOD.LBL_ASTERISK_OPENCNAM_USERNAME_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_OPENCNAM_USERNAME_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_opencnam_username )}
            {assign var='asterisk_opencnam_username' value=$asterisk_config.asterisk_opencnam_username}
            {else}
            {assign var='asterisk_opencnam_username' value=$config.asterisk_opencnam_username}
        {/if}
            <input type='textbox' name='asterisk_opencnam_username' size="45" value='{$asterisk_opencnam_username}'>
        </td>



    <TR>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_OPENCNAM_APIKEY}
        {if !empty($MOD.LBL_ASTERISK_OPENCNAM_APIKEY_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_OPENCNAM_APIKEY_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_opencnam_apikey )}
            {assign var='asterisk_opencnam_apikey' value=$asterisk_config.asterisk_opencnam_apikey}
            {else}
            {assign var='asterisk_opencnam_apikey' value=$config.asterisk_opencnam_apikey}
        {/if}
            <input type='textbox' name='asterisk_opencnam_apikey' size="45" value='{$asterisk_opencnam_apikey}'>
        </td>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_OPENCNAM_RETRIES}
        {if !empty($MOD.LBL_ASTERISK_OPENCNAM_RETRIES_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_OPENCNAM_RETRIES_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_opencnam_retries )}
            {assign var='asterisk_opencnam_retries' value=$asterisk_config.asterisk_opencnam_retries}
            {else}
            {assign var='asterisk_opencnam_retries' value=$config.asterisk_opencnam_retries}
        {/if}
            <input type='textbox' name='asterisk_opencnam_retries' size="45" value='{$asterisk_opencnam_retries}'>
        </td>



    <TR>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_GRAVATAR_ENABLED}
        {if !empty($MOD.LBL_ASTERISK_GRAVATAR_ENABLED_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_GRAVATAR_ENABLED_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_gravatar_enabled )}
            {assign var='asterisk_gravatar_enabled' value=$asterisk_config.asterisk_gravatar_enabled}
            {else}
            {assign var='asterisk_gravatar_enabled' value=$config.asterisk_gravatar_enabled}
        {/if}
            <input type='textbox' name='asterisk_gravatar_enabled' size="45" value='{$asterisk_gravatar_enabled}'>
        </td>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_SHORT_CALL_STATUS}
        {if !empty($MOD.LBL_ASTERISK_SHORT_CALL_STATUS_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_SHORT_CALL_STATUS_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_short_call_status )}
            {assign var='asterisk_short_call_status' value=$asterisk_config.asterisk_short_call_status}
            {else}
            {assign var='asterisk_short_call_status' value=$config.asterisk_short_call_status}
        {/if}
            <input type='textbox' name='asterisk_short_call_status' size="45" value='{$asterisk_short_call_status}'>
        </td>



    <TR>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_HIDE_CALL_POPUPS_AFTER_MINS}
        {if !empty($MOD.LBL_ASTERISK_HIDE_CALL_POPUPS_AFTER_MINS_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_HIDE_CALL_POPUPS_AFTER_MINS_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_hide_call_popups_after_mins )}
            {assign var='asterisk_hide_call_popups_after_mins' value=$asterisk_config.asterisk_hide_call_popups_after_mins}
            {else}
            {assign var='asterisk_hide_call_popups_after_mins' value=$config.asterisk_hide_call_popups_after_mins}
        {/if}
            <input type='textbox' name='asterisk_hide_call_popups_after_mins' size="45" value='{$asterisk_hide_call_popups_after_mins}'>
        </td>

        <TD>&nbsp;</TD><TD>&nbsp;</TD> </tr>

    <TR><td colspan="4">&nbsp;&nbsp;<TR><TD colspan="4"><h3>Logging</h3></TD></tr>



    <TR>

        <td nowrap width="10%" class="dataLabel">{$MOD.LBL_ASTERISK_LOG_FILE}
        {if !empty($MOD.LBL_ASTERISK_LOG_FILE_DESC)}
            [<a href="#" title="{$MOD.LBL_ASTERISK_LOG_FILE_DESC}">?</a>]:
        {/if}
        </td>
        <td width="25%" class="dataField">
        {if empty($config.asterisk_log_file )}
            {assign var='asterisk_log_file' value=$asterisk_config.asterisk_log_file}
            {else}
            {assign var='asterisk_log_file' value=$config.asterisk_log_file}
        {/if}
            <input type='textbox' name='asterisk_log_file' size="45" value='{$asterisk_log_file}'>
        </td>

        <!-- ------------------- [ PASTE OUTPUT OF CONFIGURATORGENERATORUTIL ABOVE THIS LINE ]----------------------------- -->

</table>
</td></tr>
</table>
<td>
<br>
</table>


<BR/>

<h3 style="color:red">You must restart asteriskLogger after making changes to the config in order for them to take effect</h2><br/>

<div style="padding-top: 2px;">
<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" class="button"  type="submit" name="save" value="  {$APP.LBL_SAVE_BUTTON_LABEL}  " />
		&nbsp;<input title="{$MOD.LBL_SAVE_BUTTON_TITLE}"  class="button"  type="submit" name="restore" value="  {$MOD.LBL_RESTORE_BUTTON_LABEL}  " />
&nbsp;<input title="{$MOD.LBL_CANCEL_BUTTON_TITLE}"  onclick="document.location.href='index.php?module=Administration&action=index'" class="button"  type="button" name="cancel" value="  {$APP.LBL_CANCEL_BUTTON_LABEL}  " />
</div>
</form>


<BR/><br>


<Table width=100% align=center cellspacing="5">

    <tr valign=top>
        <td width="20%">
            <a href="https://github.com/blak3r/yaai/wiki/User-Manual"><img src="custom/modules/Asterisk/include/documentation.png"> Link to Online Documentation</a>

        <td width="40%" >
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                <input type="hidden" name="cmd" value="_s-xclick">
                <input type="hidden" name="hosted_button_id" value="FA36DGSYQ3NE6">
                <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal" style="display: block;margin-left:auto; margin-right: auto; margin-bottom: 0px; padding-bottom: 0px;">
                <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
            </form>
            <P align="center" style="padding-top:0px; margin-top: 0px;width=75%;">
                Hundreds of man hours have been invested in this project. Please help keep YAAI Free and allow us to keep making YAAI even better.<BR><BR>
                <b>So far, YAAI has automatically logged: {$callsLogged} calls for you!</b>
            </P>
        <td width="40%">

        {literal}
            <!-- Begin MailChimp Signup Form -->
            <link href="http://cdn-images.mailchimp.com/embedcode/slim-081711.css" rel="stylesheet" type="text/css">
            <style type="text/css">
                #mc_embed_signup {  background: transparent !important;}
                #mc_embed_signup .button:hover {background-color:#444;}
                #mc_embed_signup .button {clear:both; background-color: #777; border: 0 none; border-radius:4px; color: #FFFFFF;
                    cursor: pointer; display: inline-block; font-size:15px; font-weight: bold; height: 32px; line-height: 32px;
                    margin: 0 5px 10px 0; padding:0; text-align: center; text-decoration: none; vertical-align: top; white-space: nowrap; width: auto;}
                #mc_embed_signup{background:#fff; clear:left; font:14px Helvetica,Arial,sans-serif;  width:400px;}
                    /* Add your own MailChimp form style overrides in your site stylesheet or in this style block.
   We recommend moving this block and the preceding CSS link to the HEAD of your HTML file. */
            </style>
            <div id="mc_embed_signup">
                <form action="http://blakerobertson.us6.list-manage.com/subscribe/post?u=98fe542f94231e0168df8da2f&amp;id=c20ab319d3" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
                    <label for="mce-EMAIL">Subscribe to the news mailing list to get emails about the latest updates</label>
                    <input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="email address" required>
                    <input type="submit" value="Subscribe" name="subscribe">
                </form>
            </div>
            <!--End mc_embed_signup-->
        {/literal}
    </tr>
</table>





{$JAVASCRIPT}

