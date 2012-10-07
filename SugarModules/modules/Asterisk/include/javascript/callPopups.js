//**
// * Asterisk SugarCRM Integration 
// * (c) KINAMU Business Solutions AG 2009
// * 
// * Parts of this code are (c) 2006. RustyBrick, Inc.  http://www.rustybrick.com/
// * Parts of this code are (c) 2008 vertico software GmbH  
// * Parts of this code are (c) 2009 abcona e. K. Angelo Malaguarnera E-Mail admin@abcona.de
// * Parts of this code are (c) 2011 Blake Robertson http://www.blakerobertson.com
// * http://www.sugarforge.org/projects/yaai/
// * Contribute To Project: http://www.github.com/blak3r/yaai
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


// look for new events logged from asterisk
function checkForNewStates(){
		// Note: once the user gets logged out, the ajax requests will get redirected to the login page.
		// Originally, the setTimeout method was in this method.  But, no way to detect the redirect without server side
		// changes.  See: http://stackoverflow.com/questions/199099/how-to-manage-a-redirect-request-after-a-jquery-ajax-call
		// So, now I only schedule a setTimeout upon a successful AJAX call.  The only downside of this is if there is a legit reason
		// the call does fail it'll never try again..
		$.getJSON('index.php?entryPoint=AsteriskCallListener', function(data){checkData(data);});	
}

function checkData(data){
	var tmpList = new Array();
	
	// Note: AST_PollRate is set in AsteriskJS.php
	setTimeout('checkForNewStates()', AST_PollRate); // Only when the previous request was successful do we try again.
	
	//----- ORIGINAL UI BLOCK ------//
	/*
	if(data == "."){
		$("#asterisk_ajaxContent").hide();  // TODO, this is left over from old UI... Might be possible to have a checkbox to switch back so i kept it.
		$("#asterisk_ajaxContent").empty();
		return;
	}
	*/
	//-----END OLD UI BLOCK------//


	if( data == "." ) {
		// do nothing
	}
	else {
		$.each(data, function(entryIndex, entry){
			var astId = entry['asterisk_id'];
			astId = astId.replace(/\./g,'-'); // ran into issues with jquery not liking '.' chars in id's so converted . -> -
		
			title = "" + entry['full_name'];
			if( title.length == 0 ) {
				title = entry['phone_number'];
			}
			title = title + " - " + entry['state'];
			
			tmpList.push(astId);
			
			if( -1 == $.inArray(astId, chatBoxes) ) {
				//alert(entry['call_record_id']);
				createChatBox(astId,true, title, entry['call_record_id'],entry['direction']);
				setChatContent(astId, entry['html'] );
                chatBoxContactNames[astId] = entry['full_name'];
				
				if( entry['call_record_id'] == "-1" ) {
					alert( "Call Record ID returned from server is -1, unable to save call notes for " + title ); // TODO: disable the input box instead of this alert.
				}
			}
			else {
				$(".asterisk_state", "#chatbox_"+astId+" .chatboxcontent").text(entry['state']);

                // TODO this isn't going to work on other languages... Need to pass the language equivalent Hangup label
				if( entry['is_hangup']  ) {
					$("#chatbox_"+astId+" .chatboxhead").css("background-color", "#f99d39");
					$("#transferImg_"+astId).hide(); // hide transfer icon once call is over.
				}
				else {
					$("#chatbox_"+astId+" .chatboxhead").css("background-color", "#0D5995"); // a blue color
					$("#transferImg_"+astId).show();	
				}
				
				title = "" + entry['full_name'];
				if( title.length == 0 ) {
					title = entry['phone_number'];
				}
				title = title + " - " + entry['state'];
				// entry['direction'] has Inbound vs Outbound
				setChatTitle(astId, title);
				
				$(".call_duration", "#chatbox_"+astId+" .chatboxcontent").text( entry['duration'] ); // Updates duration

                // Full name changes when, initially full name was blank or if user manually picks contact associated with call.
                if( entry['full_name'] != chatBoxContactNames[astId] ) {
                    setChatContent(astId,entry['html']);
                }

                // GITHUB issue #3...
                // I don't remember why I stopped setting the entire chat
			}
			
			// response is not empty, lets walk through the json array
			
			/*  Classic UI Style... Not tested
			var sfDiv = $("div[@id='" + entry['asterisk_id'] + "']");
			if(!sfDiv.is("div")){
				$("#asterisk_ajaxContent").show();
				$("#asterisk_ajaxContent").append(entry['html']);
				sfDiv = $("div[@id='" + entry['asterisk_id'] + "']");
				$('.asterisk_open_memo', sfDiv).click(function(){
					var newHREF = "index.php?module=Calls&action=DetailView&return_module=Calls&return_action=DetailView&parent_type=Contacts";
					newHREF += "&record=" + entry['call_record_id'];
					//newHREF += "&direction=" + entry['direction'];
					//newHREF += "&status=Held";
					//newHREF += "&parent_id=" + entry['contact_id'];
					//newHREF += "&parent_name=" + entry['full_name'];
					location.href = newHREF;
					$("#asterisk_ajaxContent").hide();
					
				});
				 
				$('.asterisk_close', sfDiv).click(function(){
					$("#asterisk_ajaxContent").hide();
					});
			}else{
				$(".asterisk_state", sfDiv).text(entry['state']);	
				$(".call_duration", sfDiv).text( entry['duration'] ); // Updates duration (not tested with old UI code)
			}
			*/
			
			
		});
	}
	
	//alert( tmpList.length );
	// Go through any checkboxes and see if any were closed in another browser window.
	for(var i=0; i<chatBoxes.length; i++ ) {
		if( -1 == $.inArray(chatBoxes[i], tmpList) ) {
			if( chatboxFocus[i] || getMemoText(chatBoxes[i]).length > 0 ) {
				// Don't auto close the chatbox b/c there is something entered or it has focus.
			}
			else {
				// Prompt if something is in the input box maybe? or append it?
				closeChatBox( chatBoxes[i] );
				// Pop it from the array?
				
				//alert("didn't find " + chatBoxes[i] + " in tmpList");
				$('#chatbox_'+chatBoxes[i]).css('display','none');
				restructureChatBoxes();
				chatBoxes.splice(i,1); // todo is chatBoxes.lenght above evaluated dynamically?
			}
		}
	}

	
}

$(document).ready(function(){
	// no checking for the login page
	if(location.href.indexOf('action=Login') == -1){
		$('<div id="asterisk_ajaxContent" style="display:none;"></div>').prependTo('#main');
		checkForNewStates();
	}
});





/*************************************************/
/*

Copyright (c) 2009 Anant Garg (anantgarg.com | inscripts.com)

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

*/

var windowFocus = true;
var username;
var chatHeartbeatCount = 0;
var minChatHeartbeat = 1000;
var maxChatHeartbeat = 33000;
var chatHeartbeatTime = minChatHeartbeat;
var originalTitle;
var blinkOrder = 0;

var nextHeight = 0; // BR added

var chatboxFocus = new Array();
var newMessages = new Array();
var newMessagesWin = new Array();
var chatBoxes = new Array();
var chatBoxCallRecordIds = new Array();
var chatBoxCallDirections = new Array();
var chatBoxContactNames = new Array();

/*
$(document).ready(function(){
	originalTitle = document.title;
	startChatSession();

	$([window, document]).blur(function(){
		windowFocus = false;
	}).focus(function(){
		windowFocus = true;
		document.title = originalTitle;
	});
});
*/

function isChatBoxClosed(chatboxid) {
	return $("#chatbox_"+chatboxid).css('display') == 'none';
}

function restructureChatBoxes() {
	/*  CODE USED FOR HORIZONTAL CHATS	
	align = 0;
	for (x in chatBoxes) {
		chatboxid = chatBoxes[x];

		if ($("#chatbox_"+chatboxid).css('display') != 'none') {
			if (align == 0) {
				$("#chatbox_"+chatboxid).css('right', '200px');
			} else {
				width = (align)*(225+7)+20;
				$("#chatbox_"+chatboxid).css('right', width+'px');
			}
			align++;
		}
	}*/
	
	//  -----[ VERTICAL CHAT STACKING ]------------- //
	var HEIGHT_MINIMIZED = 32;
	var HEIGHT_NORMAL = 293;
	var currHeight = 0;
	for(var i=0; i<chatBoxes.length; i++ ) {
		chatboxid = chatBoxes[i];
		
		if( !isChatBoxClosed( chatboxid ) ) {
			$("#chatbox_"+chatboxid).css('bottom', currHeight+'px');
			
			if( isChatBoxMinimized(chatboxid) ) {
				currHeight += HEIGHT_MINIMIZED;
			}
			else {
				currHeight += HEIGHT_NORMAL;
			}
		}
	}
	nextHeight = currHeight;
	// ^^^^^^^^^[ END VERTICAL CHAT STACKING ]^^^^^^^^^^^^^//
	
}

function createChatBox(chatboxid, checkMinimizeCookie, chatboxtitle, chatboxcallrecordid,direction) {
	if ($("#chatbox_"+chatboxid).length > 0) {
		if ($("#chatbox_"+chatboxid).css('display') == 'none') {
			$("#chatbox_"+chatboxid).css('display','block');
    restructureChatBoxes();
}
$("#chatbox_"+chatboxid+" .chatboxtextarea").focus();
return;
}


var theHtml = 	'<div class="chatboxhead" onclick="javascript:toggleChatBoxGrowth(\''+chatboxid+'\')" ><div class="chatboxtitle" >'	+
    chatboxtitle+'</div><div class="chatboxoptions"><a href="javascript:void(0)" onclick="javascript:toggleChatBoxGrowth(\'' +
    chatboxid+'\')">-</a> <a href="javascript:void(0)" style="font-size:110%;" onclick="javascript:closeChatBox(\''+
    chatboxid+'\')">X</a></div><br clear="all"/></div><div class="chatboxcontent"></div><div class="chatboxinput"><textarea id="chatboxtextarea_'+
    chatboxid+'" class="chatboxtextarea" onkeydown="javascript:return checkChatBoxInputKey(event,this,\''+chatboxid+'\');"></textarea>' +
    '<div class="chatboxbuttons"><table width="100%"><tr><td valign="bottom"><span style="width=150px;" class="asterisk_save_status">&nbsp;</span>'+
    '<img id="transferImg_'+
    chatboxid + '" src="custom/modules/Asterisk/include/call_transfer-blue.png" height=19 title="Transfer Call" onclick="javascript:showTransferMenu(\'' + chatboxid + '\');"><TD align="right">'+
    '<input style="" type="button" name="saveMemo" value="Save" onclick="javascript:saveMemo(\''+chatboxid+'\');"></table></div></div>';

$(" <div />" ).attr("id","chatbox_"+chatboxid)
    .addClass("chatbox")
    .html(theHtml)
    .appendTo($( "body" ));


setChatTitle(chatboxid,chatboxtitle);

	chatBoxCallRecordIds[chatboxid]=chatboxcallrecordid;
    //alert( getChatCallRecordId(chatboxid) + " is == " + chatboxcallrecordid);

	chatBoxCallDirections[chatboxid]=direction;


	/*  CODE USED FOR HORIZONTAL CHATS	
	chatBoxeslength = 0;

	for (x in chatBoxes) {
		if ($("#chatbox_"+chatBoxes[x]).css('display') != 'none') {
			chatBoxeslength++;
		}
	}

	$("#chatbox_"+chatboxid).css('bottom', '0px');
	if (chatBoxeslength == 0) {
		$("#chatbox_"+chatboxid).css('right', '20px');
	} else {
		width = (chatBoxeslength)*(225+7)+20;
		$("#chatbox_"+chatboxid).css('right', width+'px');
	}
	*/
	

	// Minimize each of the existing chatboxes, when new call comes in.
	for( x=0; x<chatBoxes.length; x++ ) {
		minimizeChatBox( chatBoxes[x] ); // updates a cookie each time... perhaps check first.
	}
	
	// START VERTICAL
	restructureChatBoxes();
	$("#chatbox_"+chatboxid).css('right', '20px');
	$("#chatbox_"+chatboxid).css('bottom', nextHeight+'px');
	// END VERTICAL
	
	chatBoxes.push(chatboxid);

	if (checkMinimizeCookie == 1) {
		
		// Check by looking at the cookie to see if it should be minimized or not.
		minimizedChatBoxes = new Array();

		if ($.cookie('chatbox_minimized')) {
			minimizedChatBoxes = $.cookie('chatbox_minimized').split(/\|/);
		}
		minimize = 0;
		for (j=0;j<minimizedChatBoxes.length;j++) {
			if (minimizedChatBoxes[j] == chatboxid) {
				minimize = 1;
			}
		}

		if (minimize == 1) {
			$('#chatbox_'+chatboxid+' .chatboxcontent').css('display','none');
			$('#chatbox_'+chatboxid+' .chatboxinput').css('display','none');
		}
	}

	chatboxFocus[chatboxid] = false;

	$("#chatbox_"+chatboxid+" .chatboxtextarea").blur(function(){
		chatboxFocus[chatboxid] = false;
		$("#chatbox_"+chatboxid+" .chatboxtextarea").removeClass('chatboxtextareaselected');
	}).focus(function(){
		chatboxFocus[chatboxid] = true;
		newMessages[chatboxid] = false;
		$('#chatbox_'+chatboxid+' .chatboxhead').removeClass('chatboxblink');
		$("#chatbox_"+chatboxid+" .chatboxtextarea").addClass('chatboxtextareaselected');
	});

	$("#chatbox_"+chatboxid).click(function() {
		if ($('#chatbox_'+chatboxid+' .chatboxcontent').css('display') != 'none')
        {
            // TODO Investigate... this sets focus to textarea whenever anywhere is clicked in chatbox, needs to be tweaked if I add other inputboxes and could be cause of focus stealing problems which happen occasionally.
            $("#chatbox_"+chatboxid+" .chatboxtextarea").focus();
		}
	});

	$("#chatbox_"+chatboxid).show();
}

function setChatContent( chatboxid, chatboxcontent ) {
  $("#chatbox_"+chatboxid+" .chatboxcontent").html( chatboxcontent );
}

function setChatTitle( chatboxid, chatboxtitle ) {
  if( chatboxtitle.length > 30 ) {
	chatboxtitle = chatboxtitle.substr(0,27) + "...";
  }
  
  $("#chatbox_"+chatboxid+" .chatboxtitle").html( chatboxtitle );
}

function getChatCallRecordId( chatboxid ) {
	return chatBoxCallRecordIds[chatboxid];
}

function closeChatBox(chatboxid) {
	if( !isChatBoxClosed(chatboxid) ) {
		$('#chatbox_'+chatboxid).css('display','none');
		restructureChatBoxes();
		
		callRecordId = getChatCallRecordId( chatboxid );

        //if( callRecordId == null ) {
        //    alert("Call popup notification logic error.  Please refresh page to close boxes. Length =" + chatBoxCallRecordIds )
        //}

        // NOTE: For some unknown reason, on pages with AJAX the array that getChatCallRecordId uses just returns null for known valid records...
        //       Controller.php now looks at the id field as well for the callRecordId.  This is okay since the current implementation uses callRecordId for the chatboxid.

        // Tells asterisk_log table that user has closed this entry.
		$.post("index.php?entryPoint=AsteriskController&action=updateUIState", {id: chatboxid, ui_state: "Closed", call_record: callRecordId} );

	}
}

// Called when clicking on radio buttons when multiple contacts exist.
function setContactId( callRecordId, contactId) {
  //alert("invoking setContactId");
    $.post("index.php?entryPoint=AsteriskController&action=setContactId", {call_record: callRecordId, contact_id: contactId} );
}

// Updates the cookie which stores the state of all the chatboxes (whether minimized or maximized)
// Only problem with this approach is on second browser window you might have them open differently... and this would save the state as such.
function updateMinimizeCookie() {
	var cookieVal="";
	
	for( var i=0; i<chatBoxes.length; i++ ) {
		
		if( isChatBoxMinimized( chatBoxes[i] ) ) {
			cookieVal = chatBoxes[i] + "|";
		}
	}
	
	cookieVal = cookieVal.substr(0, cookieVal.length - 1 ); // remove trailing "|"
	
	//alert(cookieVal);
	$.cookie('chatbox_minimized', cookieVal);
}

// Method which minimizes and maximizes chat windows
// Writes the state to a cookie 
function toggleChatBoxGrowth(chatboxid) {
	if (isChatBoxMinimized(chatboxid) ) {  
		maximizeChatBox(chatboxid);
	} 
	else {	
		minimizeChatBox(chatboxid);
	}
	restructureChatBoxes(); // BR added... only needed for vertical stack method.
}


function maximizeChatBox(chatboxid) {
		$('#chatbox_'+chatboxid+' .chatboxcontent').css('display','block');
		$('#chatbox_'+chatboxid+' .chatboxinput').css('display','block');
		//$("#chatbox_"+chatboxid+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxid+" .chatboxcontent")[0].scrollHeight);
				
		if( isChatBoxMinimized( chatboxid ) ) {
			alert( chatboxid + " minimize state cookie fail (should be maximized)");
		}
		
		updateMinimizeCookie();
}


function minimizeChatBox(chatboxid) {
		$('#chatbox_'+chatboxid+' .chatboxcontent').css('display','none');
		$('#chatbox_'+chatboxid+' .chatboxinput').css('display','none');
		
		if( !isChatBoxMinimized( chatboxid ) ) {
			alert( chatboxid + " minimize state cookie fail");
		}
		
		updateMinimizeCookie();
}



// I don't think this is used.
function isChatBoxMinimized( chatboxid ) {

	return $('#chatbox_'+chatboxid+' .chatboxcontent').css('display') == 'none';

	// Relying on the cookie wasn't working reliably enough.
/*
	if( $.cookie('chatbox_minimized') ) {
		minimizedChatBoxes = $.cookie('chatbox_minimized').split(/\|/);
		for ( var v=0;i<minimizedChatBoxes.length;i++) {
			if (minimizedChatBoxes[v] == chatboxid) {
				return true;
			}
		}
	}
	else {
		alert ("Cookie doesn't exist");
	}
	
	return false;
*/
}

// Saves what is placed in the input box whenever call is saved.
function checkChatBoxInputKey(event,chatboxtextarea,chatboxid) {
	 
	 // 13 == Enter
	if(event.keyCode == 13)  {
		// CTRL + ENTER == quick save + close shortcut
		if( event.ctrlKey == 1 ) {
			saveMemo( chatboxid );
			closeChatBox(chatboxid);
			return false;
		}
		else if( event.shiftKey != 0 ) {
			saveMemo( chatboxid );
			//return false; // Returning false prevents return from adding a break.
		}
	}

}

function getMemoText( chatboxid ) {
	var message = "";
	chatboxtextarea = '#chatbox_'+chatboxid+' .chatboxinput .chatboxtextarea';
	message = $(chatboxtextarea).val();
	message = message.replace(/^\s+|\s+$/g,""); // Trims message
	
	return message;
}

function saveMemo( chatboxid ) {
		message = getMemoText(chatboxid);
		
		//$(chatboxtextarea).val('');
		$(chatboxtextarea).focus();
		$(chatboxtextarea).css('height','44px');
		if (message != '') {
		
			callRecordId = getChatCallRecordId( chatboxid );
			var theDirection = chatBoxCallDirections[chatboxid];
			//alert( chatboxid + "callid: " + callRecordId + "   " + chatBoxCallRecordIds[chatboxid]);

			$.post("index.php?entryPoint=AsteriskController&action=memoSave", {id: chatboxid, call_record: callRecordId, description: message, direction: theDirection} , function(data){
				//message = message.replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;");
				$("#chatbox_"+chatboxid+" .asterisk_save_status").html('Call Details Saved').css("display","block").fadeOut(5000); 
				
			});
		}
		
		// If you don't want SAVE button to also close then comment out line below
		closeChatBox(chatboxid);
}

function showTransferMenu( chatboxid, exten ) {
	if( chatboxid != '' ) {
		exten = prompt("Please enter the extension number you'd like to transfer to:\n(Leave Blank to cancel)","");
		
		if( exten != null && exten != '') {
	//alert(exten);	
		callRecordId = getChatCallRecordId( chatboxid );
			$.post("index.php?entryPoint=AsteriskController&action=transfer", {id: chatboxid, call_record: callRecordId, extension: exten } , function(data){
				//alert(data);
			});
		}
	}
}


/**
 * Relate Contact Callback method.
 * This is called by the open_popup sugar call when a contact is selected.
 *
 * I basically copied the set_return method and added some stuff onto the bottom.  I couldn't figure out how to add
 * change events to my form elements.  This method wouldn't be needed if I figured that out.
 */
var from_popup_return2  = false;
function relate_popup_callback(popup_reply_data)
{
    from_popup_return2 = true;
    var form_name = popup_reply_data.form_name;
    var name_to_value_array = popup_reply_data.name_to_value_array;

    for (var the_key in name_to_value_array)
    {
        if(the_key == 'toJSON')
        {
            /* just ignore */
        }
        else
        {
            var displayValue=name_to_value_array[the_key].replace(/&amp;/gi,'&').replace(/&lt;/gi,'<').replace(/&gt;/gi,'>').replace(/&#039;/gi,'\'').replace(/&quot;/gi,'"');;
            if(window.document.forms[form_name] && window.document.forms[form_name].elements[the_key])
            {
                window.document.forms[form_name].elements[the_key].value = displayValue;
                SUGAR.util.callOnChangeListers(window.document.forms[form_name].elements[the_key]);
            }
        }
}

    // Everything above is from the default set_return method in parent_popup_helper.
    var contactId = window.document.forms[form_name].elements['relateContactId'].value;
    if( contactId != null ) {
        //alert("Setting Contact Id");
        setContactId(form_name,contactId);
    }
    else {
        alert("Error updating related Contact");
    }
}




/**
 * Cookie plugin
 *
 * Copyright (c) 2006 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

jQuery.cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // CAUTION: Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};



// --------------------- UNUSED CHAT METHODS ------------------------------------ //

/*
function startChatSession(){  
	$.ajax({
	  url: "chat.php?action=startchatsession",
	  cache: false,
	  dataType: "json",
	  success: function(data) {
 
		username = data.username;

		$.each(data.items, function(i,item){
			if (item)	{ // fix strange ie bug

				chatboxid = item.f;

				if ($("#chatbox_"+chatboxid).length <= 0) {
					createChatBox(chatboxid,1);
				}
				
				if (item.s == 1) {
					item.f = username;
				}

				if (item.s == 2) {
					$("#chatbox_"+chatboxid+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxinfo">'+item.m+'</span></div>');
				} else {
					$("#chatbox_"+chatboxid+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">'+item.f+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+item.m+'</span></div>');
				}
			}
		});
		
		for (i=0;i<chatBoxes.length;i++) {
			chatboxid = chatBoxes[i];
			$("#chatbox_"+chatboxid+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxid+" .chatboxcontent")[0].scrollHeight);
			setTimeout('$("#chatbox_"+chatboxid+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxid+" .chatboxcontent")[0].scrollHeight);', 100); // yet another strange ie bug
		}
	
	setTimeout('chatHeartbeat();',chatHeartbeatTime);
		
	}});
}
*/



/*
function chatHeartbeat(){

	var itemsfound = 0;
	
	if (windowFocus == false) {
 
		var blinkNumber = 0;
		var titleChanged = 0;
		for (x in newMessagesWin) {
			if (newMessagesWin[x] == true) {
				++blinkNumber;
				if (blinkNumber >= blinkOrder) {
					document.title = x+' says...';
					titleChanged = 1;
					break;	
				}
			}
		}
		
		if (titleChanged == 0) {
			document.title = originalTitle;
			blinkOrder = 0;
		} else {
			++blinkOrder;
		}

	} else {
		for (x in newMessagesWin) {
			newMessagesWin[x] = false;
		}
	}

	for (x in newMessages) {
		if (newMessages[x] == true) {
			if (chatboxFocus[x] == false) {
				//FIX add toggle all or none policy, otherwise it looks funny
				$('#chatbox_'+x+' .chatboxhead').toggleClass('chatboxblink');
			}
		}
	}
	
	$.ajax({
	  url: "chat.php?action=chatheartbeat",
	  cache: false,
	  dataType: "json",
	  success: function(data) {

		$.each(data.items, function(i,item){
			if (item)	{ // fix strange ie bug

				chatboxid = item.f;

				if ($("#chatbox_"+chatboxid).length <= 0) {
					createChatBox(chatboxid);
				}
				if ($("#chatbox_"+chatboxid).css('display') == 'none') {
					$("#chatbox_"+chatboxid).css('display','block');
					restructureChatBoxes();
				}
				
				if (item.s == 1) {
					item.f = username;
				}

				if (item.s == 2) {
					$("#chatbox_"+chatboxid+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxinfo">'+item.m+'</span></div>');
				} else {
					newMessages[chatboxid] = true;
					newMessagesWin[chatboxid] = true;
					$("#chatbox_"+chatboxid+" .chatboxcontent").append('<div class="chatboxmessage"><span class="chatboxmessagefrom">'+item.f+':&nbsp;&nbsp;</span><span class="chatboxmessagecontent">'+item.m+'</span></div>');
				}

				$("#chatbox_"+chatboxid+" .chatboxcontent").scrollTop($("#chatbox_"+chatboxid+" .chatboxcontent")[0].scrollHeight);
				itemsfound += 1;
			}
		});

		chatHeartbeatCount++;

		if (itemsfound > 0) {
			chatHeartbeatTime = minChatHeartbeat;
			chatHeartbeatCount = 1;
		} else if (chatHeartbeatCount >= 10) {
			chatHeartbeatTime *= 2;
			chatHeartbeatCount = 1;
			if (chatHeartbeatTime > maxChatHeartbeat) {
				chatHeartbeatTime = maxChatHeartbeat;
			}
		}
		
		setTimeout('chatHeartbeat();',chatHeartbeatTime);
	}});
}
*/
