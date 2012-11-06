//**
// * Asterisk SugarCRM Integration 
// * (c) KINAMU Business Solutions AG 2009
// * 
// * Parts of this code are (c) 2006. RustyBrick, Inc.  http://www.rustybrick.com/
// * Parts of this code are (c) 2008 vertico software GmbH  
// * Parts of this code are (c) 2009 abcona e. K. Angelo Malaguarnera E-Mail admin@abcona.de
// * Parts of this code are (c) 2012 Blake Robertson http://www.blakerobertson.com
// * http://www.sugarforge.org/projects/yaai/
// * 
// * This program is free software; you can redistribute it and/or modify it under
// * the terms of the GNU General Public License version 3 as published by the
// * Free Software Foundation with the addition of the following permission added
// * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
// * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
// * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
// * 
// * This program is distributed in the hope that it will be useful, but WITHOUT
// * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
// * details.
// * 
// * You should have received a copy of the GNU General Public License along with
// * this program; if not, see http://www.gnu.org/licenses or write to the Free
// * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
// * 02110-1301 USA.
// * 
// * You can contact KINAMU Business Solutions AG at office@kinamu.com
// * 
// * The interactive user interfaces in modified source and object code versions
// * of this program must display Appropriate Legal Notices, as required under
// * Section 5 of the GNU General Public License version 3.
// * 
// */



$(document).ready(function()
{
    //.asterisk_phoneNumber is the deprecated v1.x class
	$('.phone,#phone_work,#phone_other,#phone_mobile,.asterisk_phoneNumber,#phone_mobile_span').each(function()
	{
		var phoneNr = $.trim($(this).text());

        // Regex searches the inner html to see if a child element has phone class,
        // this prevents a given number having more then one click to dial icon.
        // ? after the " is required for IE compatibility.  IE strips the " around the class names apparently.
        // The /EDV.show_edit/ regex allows it to work with Letrium's Edit Detail View module.
        if(phoneNr.length > 1  && ( !/(class="?phone"?|id="?#phone|class="?asterisk_placeCall"?)/.test($(this).html()) || /EDV.show_edit/.test($(this).html()) ) )
        {
           	var contactId = $('input[name="record"]', document.forms['DetailView']).attr('value');
			if (!contactId)
			{
				contactId = $('input[name="mass[]"]', $(this).parents('tr:first')).attr('value');
			}

			if( AST_UserExtention ) {
				$(this).append('&nbsp;&nbsp;<img title="Extension Configured for Click To Dial is: ' + AST_UserExtention + '" src="custom/modules/Asterisk/include/call_active.gif" class="asterisk_placeCall" value="anrufen" style="cursor: pointer;"/>&nbsp;');
			}
			else {
				$(this).append('&nbsp;&nbsp;<img title="No extension configured!  Go to user preferences to set your extension" src="custom/modules/Asterisk/include/call_noextset.gif" class="asterisk_placeCall" value="anrufen" style="cursor: pointer;"/>&nbsp;');
			}

			$('.asterisk_placeCall', this).click(function()
			{
				// alert("phoneNr : "+phoneNr+" , contactId: "+contactId);
				var call = $.get('index.php?entryPoint=AsteriskCallCreate',
					{phoneNr : phoneNr, contactId: contactId},
					function(data){
					  call = null;
					});
				// Wait for 5 seconds
				setTimeout(function()
				{
				    // If the request is still running, abort it.
				    if ( call )
				    {
				  	    call.abort();
				    };
				}, 10000);
			});
		}
	});
});