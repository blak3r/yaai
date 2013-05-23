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
<img src="https://gitimg.com/blak3r/yaai/config-v3/track" alt="Config Logo"><br>

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
    {include file='custom/modules/Configurator/asterisk_configurator_table.tpl'}
    <!-- ------------------- [ PASTE OUTPUT OF CONFIGURATORGENERATORUTIL ABOVE THIS LINE ]----------------------------- -->

</table>
</td></tr>
</table>
<td>
<br>
</table>


<BR/>

<h3 style="color:red">You must restart asteriskLogger after making changes to the config in order for them to take effect</h3><br/>

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

