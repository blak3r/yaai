//**
// * Asterisk SugarCRM Integration 
// * (c) KINAMU Business Solutions AG 2009
// * 
// * Parts of this code are (c) 2006. RustyBrick, Inc.  http://www.rustybrick.com/
// * Parts of this code are (c) 2008 vertico software GmbH  
// * Parts of this code are (c) 2009 Copyright (c) 2009 Anant Garg (anantgarg.com | inscripts.com)
// * Parts of this code are (c) 2009 abcona e. K. Angelo Malaguarnera E-Mail admin@abcona.de
// * Parts of this code are (c) 2011 Blake Robertson http://www.blakerobertson.com
// * Parts of this code are (c) 2012 Patrick Hogan askhogan@gmail.com
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

var YAAI = {
    nextHeight : '0',
    callboxFocus : [],
    newMessages : [],
    callBoxes : [],
    sugarUserID : window.current_user_id,
    phoneExtension : window.callinize_user_extension,
    pollRate: window.callinize_poll_rate,
    fop2 : window.callinize_fop_enabled,
    fop2URL : window.callinize_fop_url,
    fop2UserID : window.callinize_fop_user,
    fop2Password : window.callinize_fop_pass,
    showTransferButton : window.callinize_show_transfer_button,
    filteredCallStates : [''], //['Ringing'], // TODO make this configurable

    checkForNewStates : function(loop){
        // Note: once the user gets logged out, the ajax requests will get redirected to the login page.
        // Originally, the setTimeout method was in this method.  But, no way to detect the redirect without server side
        // changes.  See: http://stackoverflow.com/questions/199099/how-to-manage-a-redirect-request-after-a-jquery-ajax-call
        // So, now I only schedule a setTimeout upon a successful AJAX call.  The only downside of this is if there is a legit reason
        // the call does fail it'll never try again..
        $.ajax({
            url:"index.php?entryPoint=AsteriskController&action=get_calls",
            cache: false,
            type: "GET",
            success: function(data){
                YAAI.log(data);
                data = $.parseJSON(data);
                var callboxids = [];

                //if the loop variable is true then setup the loop, if it is false then don't, because a one-time refresh was called'
                if(loop){
                    setTimeout('YAAI.checkForNewStates(true)', YAAI.pollRate);
                }
                YAAI.log(data);
                YAAI.log('start render' + Date(Date.now() * 1000));
                if( data != ".") {
                    $.each(data, function(entryIndex, entry){
                        if(YAAI.callStateIsNotFiltered(entry)){
                            var callboxid = YAAI.getAsteriskID(entry['asterisk_id']);
                            callboxids.push(callboxid);

                            if(YAAI.callBoxHasNotAlreadyBeenCreated(callboxid)) {
                                YAAI.createCallBox(callboxid, entry);
                                YAAI.log('create');
                            }
                            else {
                                YAAI.updateCallBox(callboxid, entry);
                                YAAI.log('update');
                            }
                        }
                    });
                }

                YAAI.wasCallBoxClosedInAnotherBrowserWindow(callboxids);
            },
            error: function (jqXHR, textStatus, thrownError){
                YAAI.log('There is a problem with getJSON in checkForNewStates()');
            }
        });
    },

    // CREATE
    
    createCallBox : function (callboxid, entry, modstrings) {
       if($('#callbox_'+callboxid).attr('id') == undefined){
           var html;
           var template = '';

           if( window.callinize_dev ) {
               YAAI.log("WARNING: YAAI Developer Mode is enabled, this should not be used in production!");
               var source   = $("#handlebars-dev-template").html();
               template = Handlebars.compile(source);
           }
           else {
               template = Handlebars.templates['call-template.html'];
           }

            // Creates the modstrings needed by the template
            var context = {
                callbox_id : 'callbox_' + callboxid,
                title : entry['title'],
                asterisk_state : entry['state'],
                call_type : entry['call_type'],
                duration : entry['duration'] + ' mins',
                phone_number: entry['phone_number'],
                caller_id: entry['caller_id'],
                call_record_id: entry['call_record_id'],
                select_contact_label: entry['mod_strings']['ASTERISKLBL_SELECTCONTACT'],
                //select_account_label: entry['mod_strings']['ASTERISKLBL_SELECTACCOUNT'], // Removed
                name_label: entry['mod_strings']['ASTERISKLBL_NAME'],
                company_label: entry['mod_strings']['ASTERISKLBL_COMPANY'],
                create_label: entry['mod_strings']['CREATE'],
                relate_to_label: entry['mod_strings']['RELATE_TO'],
                caller_id_label: entry['mod_strings']['ASTERISKLBL_CALLERID'],
                phone_number_label: entry['mod_strings']['CALL_DESCRIPTION_PHONE_NUMBER'],
                duration_label: entry['mod_strings']['ASTERISKLBL_DURATION'],
                save_label: entry['mod_strings']['SAVE'],
                create_new_contact_label : entry['mod_strings']['CREATE_NEW_CONTACT'],
                create_new_account_label : entry['mod_strings']['CREATE_NEW_ACCOUNT'],
                relate_to_contact_label : entry['mod_strings']['RELATE_TO_CONTACT'],
                relate_to_account_label : entry['mod_strings']['RELATE_TO_ACCOUNT'],

                block_label: entry['mod_strings']['BLOCK'],
                block_number_label : entry['mod_strings']['BLOCK_NUMBER'],
                create_new_lead_label : entry['mod_strings']['CREATE_NEW_LEAD'],
                relate_to_lead_label : entry['mod_strings']['RELATE_TO_LEAD']
            };

            var numMatches = 0;
            if( entry['beans'] != null ) {
                numMatches = entry['beans'].length;
            }
            YAAI.log("Matches: " + numMatches);

            switch(numMatches){
                case 0 :
                    html = template(context);
                    $('body').append(html);
                    YAAI.setupHandlebarsContextNoMatchingCase(callboxid, context, entry);
                    $('#callbox_'+callboxid).find('.nomatchingcontact').show();
                    break;

                case 1 :
                    context = YAAI.setupHandlebarsContextForSingleMatchingCase(callboxid, context, entry);
                    html = template(context);
                    $('body').append(html);
                    YAAI.bindOpenPopupSingleMatchingContact(callboxid, entry);
                    $('#callbox_'+callboxid).find('.singlematchingcontact').show();
                    // TODO this should set the account display right away if it's available instead of waiting till 2nd poll refresh
                    break;

                default : // Matches > 1
                    context = YAAI.setupHandlebarsContextForMultipleMatchingCase(callboxid, context, entry);
                    html = template(context);
                    $('body').append(html);
                    YAAI.bindSetBeanID(callboxid, entry);
                    $('#callbox_'+callboxid).find('.multiplematchingcontacts').show();
                    break;
            }


            YAAI.bindActionDropdown(callboxid,entry);

           if( this.showTransferButton ) {
               this.bindTransferButton(callboxid,entry);
           }

            //bind user actions
            YAAI.bindCheckCallBoxInputKey(callboxid, entry['call_record_id'], entry['phone_number'], entry['direction']);
            YAAI.bindCloseCallBox(callboxid, entry['call_record_id']);
            YAAI.bindToggleCallBoxGrowth(callboxid);
            YAAI.bindSaveMemo(callboxid, entry['call_record_id'], entry['phone_number'], entry['direction']);

            //draw
            YAAI.showCallerIDWhenAvailable(entry);
            YAAI.minimizeExistingCallboxesWhenNewCallComesIn();
            YAAI.startVerticalEndVertical(callboxid);  //procedurally this must go after minimizeExistingCallboxesWhenNewCallComesIn
            YAAI.checkMinimizeCookie(callboxid);
            YAAI.setupCallBoxFocusAndBlurSettings(callboxid);
            YAAI.setCallBoxHeadColor(callboxid, entry);

            YAAI.checkForErrors(entry);

            $('.callbox').show();
            $("#callbox_"+callboxid).show();
       }
    },
    
    // UPDATE
    
    updateCallBox : function (callboxid, entry){
        $("#callbox_"+callboxid).find('.callboxtitle').text(entry['title']);
        $("#callbox_"+callboxid).find('.phone_number').text(entry['phone_number']); // Needed for AMI v1.0, outbound calls.

        YAAI.setCallBoxHeadColor(callboxid, entry);
        YAAI.setTransferButton(callboxid,entry);
				
        $(".call_duration", "#callbox_"+callboxid+" .callboxcontent").text( entry['duration'] ); // Updates duration

        YAAI.refreshSingleMatchView(callboxid, entry);
        
    },
    
    // CLEANUP
    
    wasCallBoxClosedInAnotherBrowserWindow : function  (callboxids){
        for(var i=0; i < YAAI.callBoxes.length; i++ ) {
            if( -1 == $.inArray(YAAI.callBoxes[i], callboxids) ) {
                if( YAAI.callboxFocus[i]) {
                // Don't auto close the callbox b/c there is something entered or it has focus.
                }
                else {
                    YAAI.closeCallBox( YAAI.callBoxes[i] );
                    YAAI.restructureCallBoxes();
                    YAAI.callBoxes.splice(i,1); // todo is callBoxes.length above evaluated dynamically?
                }
            }
        }
    },

    bindToggleCallBoxGrowth : function (callboxid){
        $('#callbox_'+callboxid).find('.callboxhead').on("click",  function(){
            YAAI.toggleCallBoxGrowth(callboxid);
        });
    },
    
    bindCloseCallBox : function(callboxid, call_record_id){
        $('#callbox_'+callboxid).find('.callboxoptions a').on("click", function(){
            YAAI.closeCallBox(callboxid, call_record_id);
        });  
    },
    
    bindSaveMemo : function(callboxid, call_record_id, phone_number, direction){
        $('#callbox_'+callboxid).find('.save_memo').button().on("click", function(){
            YAAI.saveMemo(callboxid, call_record_id, phone_number, direction);  
        });
    },

    bindTransferButton : function(callboxid, entry){
        $('#callbox_'+callboxid).find('.transfer_panel').button( {
            icons: {
                primary: 'ui-icon-transfer',
                secondary: null
            }
        }).on("click", function() {
                YAAI.log("Binding Transfer Button action");
                YAAI.showTransferMenu(entry);
        });
    },

    showTransferButton : function(callboxid,entry) {
        $('#callbox_'+callboxid).find('.transfer_panel').show();
    },
    hideTransferButton : function(callboxid, entry) {
        $('#callbox_'+callboxid).find('.transfer_panel').hide();
    },
    bindCheckCallBoxInputKey : function(callboxid, entry){
        $('#callbox_'+callboxid).find('.transfer_button').keydown(function(event){
            YAAI.checkCallBoxInputKey(event, callboxid, entry);
        });

    },

    bindActionDropdown : function(callboxid,entry){
        YAAI.log("Binding Action Dropdown for "+ callboxid);

        var dropdownDiv = "#dropdown-1_callbox_"+callboxid;

         $('#callbox_'+callboxid).find('.callbox_action').button({
                icons: {
                    primary: "ui-icon-flag",
                    secondary: "ui-icon-triangle-1-s"
                },
                text: false
         })
         .show()
         .on("click",function() {
             $(dropdownDiv).slideDown("fast");
             $(dropdownDiv).css( "margin-left", "50px");
         })
         .on("mouseenter",function() {
             $(dropdownDiv).css( "margin-left", "50px"); // Needed in ie8 only...
             clearTimeout($(dropdownDiv).data('timeoutId1'));
             clearTimeout($(dropdownDiv).data('timeoutId2'));
             //YAAI.log("clearing timeouts... button");
         })
         .on("mouseleave", function () {
             var timeoutId1 = setTimeout(hideDropDown,600);
             $(dropdownDiv).data('timeoutId1', timeoutId1);
             //YAAI.log("set timeouts... button");
         });

        // This is for mouse events over the actual dropdowns...
        $(dropdownDiv).mouseleave(function() {
            var timeoutId2 = setTimeout(hideDropDown, 600);
            $(dropdownDiv).data('timeoutId2', timeoutId2);
            //YAAI.log("set timeouts... div");
        });
        $(dropdownDiv).mouseenter(function() {
            clearTimeout($(dropdownDiv).data('timeoutId1'));
            clearTimeout($(dropdownDiv).data('timeoutId2'));
            //YAAI.log("clearing timeouts... div");
        });

        function hideDropDown() {
            //YAAI.log("firing hideDropDown");
            $(dropdownDiv).slideUp("fast");
        }
        // Here we show them all...

        if( window.callinize_relate_to_contact_enabled ) {
            YAAI.log("  Adding Relate to Contact");
            $(dropdownDiv+" ul li.ul_relate_to_contact").show();
            $(dropdownDiv+" ul li a.relate_to_contact").on("click", entry, function() {
                YAAI.openContactRelatePopup(entry);
            });
        }

        if( window.callinize_relate_to_account_enabled ) {
            YAAI.log("  Adding Relate to Account");
            $(dropdownDiv+" ul li.ul_relate_to_account").show();
            $(dropdownDiv+" ul li a.relate_to_account").on("click", entry, function() {
                YAAI.openAccountRelatePopup(entry);
            });
        }

        if( window.callinize_create_new_contact_enabled ) {
            YAAI.log("  Adding Create New Contact " + dropdownDiv+" ul li.li_create_new_contact");
            $(dropdownDiv+" ul li.ul_create_contact").show();
            $(dropdownDiv+" ul li a.create_contact").on("click", entry, function() {
                YAAI.createContact(entry);
            });
        }

        /*
         if( window.callinize_relate_to_contact_enabled ) {
         YAAI.log("  Adding Relate to Contact");
         // TODO Remove line below... for debugging
         YAAI.log( $(dropdownDiv+" ul").length + " was found?");

         $(dropdownDiv+" ul").append("<li><a href='#' class='relate_to_contact'>"+entry['mod_strings']['RELATE_TO_CONTACT']+"</a></li>");
         $(dropdownDiv+" ul a.relate_to_contact").on("click", entry, function() {
         YAAI.openContactRelatePopup(entry)
         });
         }
         // TODO create
         if( window.callinize_relate_to_account_enabled ) {
         YAAI.log("  Adding Relate to Account");
         $(dropdownDiv+" ul").append("<li><a href='#' class='relate_to_account'>"+entry['mod_strings']['RELATE_TO_ACCOUNT']+"</a></li>");
         $(dropdownDiv+" ul a.relate_to_account").on("click", entry, function() {
         YAAI.openAccountRelatePopup(entry);
         });
         }

         if( window.callinize_create_new_contact_enabled ) {
         YAAI.log("  Adding Create New Contact");
         $(dropdownDiv+" ul").append("<li><a href='#' class='create_contact'>"+entry['mod_strings']['CREATE_NEW_CONTACT']+"</a></li>");
         $(dropdownDiv+" ul a.create_contact").on("click", entry, function() {
         YAAI.createContact(entry)
         });
         }

         if( window.callinize_block_button_enabled ) {
         YAAI.log("  Adding Block Button Enabled");
         $(dropdownDiv+" ul").append("<li><a href='#' class='block_number'>"+entry['mod_strings']['BLOCK_NUMBER']+"</a></li>");
         $(dropdownDiv+" ul a.block_number").on("click", {
         entry: entry,
         callboxid: callboxid
         }, function() {
         YAAI.showBlockNumberDialog(callboxid, entry)
         });
         }
         */
    },

    // This is the icon next to the name.
    bindOpenPopupSingleMatchingContact : function(callboxid, entry){
        $('#callbox_'+callboxid).find('.singlematchingcontact .unrelate_contact').button({
            icons: {
                primary: 'ui-icon-custom-unrelate',
		        secondary: null
            },
            text: false
        }).on("click", function(){
            YAAI.openPopup(entry);
        });  
    },

    // Not going to have this...
    bindOpenPopupSingleMatchingAccount : function(callboxid, entry){
        $('#callbox_'+callboxid).find('.singlematchingcontact .unrelate_contact').button({
            icons: {
                primary: 'ui-icon-custom-unrelate',
                secondary: null
            },
            text: false
        }).on("click", function(){
                YAAI.openPopup(entry);
            });
    },
    
    bindSetBeanID : function(callboxid, entry){
        //console.log("in bind "+ bean_module + " is what beanmodule is");
        $('#callbox_'+callboxid).find('.multiplematchingcontacts td p').on("click", "input",  function(){
            YAAI.setBeanID(entry['call_record_id'], this.className, this.value);
        })
    },
    
    /// USER ACTIONS
    closeCallBox : function(callboxid, call_record_id) {
        if( !YAAI.isCallBoxClosed(callboxid) ) {
            $('#callbox_'+callboxid).remove();
            $('#block-number-callbox_'+callboxid).remove();
            $('#dropdown-1_callbox_'+callboxid).remove();
            
            YAAI.restructureCallBoxes();  
            
            if(call_record_id){
                // Tells asterisk_log table that user has closed this entry.
                $.post("index.php?entryPoint=AsteriskController&action=updateUIState", {
                    id: callboxid, 
                    ui_state: "Closed", 
                    call_record: call_record_id
                } );
            }

        }
    },
    toggleCallBoxGrowth : function(callboxid) {
        if (YAAI.isCallBoxMinimized(callboxid) ) {  
            YAAI.maximizeCallBox(callboxid);
        } 
        else {	
            YAAI.minimizeCallBox(callboxid);
        }
        YAAI.restructureCallBoxes(); // BR added... only needed for vertical stack method.
    },
    
    setBeanID : function( callRecordId, beanModule, beanId) {
        $.post("index.php?entryPoint=AsteriskController&action=setBeanID", {
            call_record: callRecordId, 
            bean_module: beanModule,
            bean_id: beanId
        } );
     
        //force an out of loop request to refresh contact view
        var loop = false;
        YAAI.checkForNewStates(loop);
        
    },
    
    saveMemo : function(callboxid, call_record_id, phone_number, direction) {
        var message = YAAI.getMemoText(callboxid);
    
        if (message != '') {
            $.post("index.php?entryPoint=AsteriskController&action=memoSave", {
                id: callboxid, 
                call_record: call_record_id, 
                description: message, 
                direction: direction,
                sugar_user_id: YAAI.sugarUserID,
                phone_number: phone_number
            })
            .success(function() {
                // If you don't want SAVE button to also close then comment out line below
                YAAI.closeCallBox(callboxid, call_record_id);
            })
            .error(function(){
                alert("Problem Saving Notes")
            });
        }
    },

    openContactRelatePopup : function (entry){
        open_popup( "Contacts", 600, 400, "", true, true, {
            "call_back_function":"YAAI.relate_contact_popup_callback",
            "form_name": entry['call_record_id'],
            "field_to_name_array":{
                "id":"relateContactId",
                "first_name":"relateContactFirstName",
                "last_name":"relateContactLastName"
            }
        },"single",true);   
    },

    openAccountRelatePopup : function (entry){
        open_popup( "Accounts", 600, 400, "", true, true, {
            "call_back_function":"YAAI.relate_account_popup_callback",
            "form_name": entry['call_record_id'],
            "field_to_name_array":{
                "id":"relateAccountId",
                "name":"relateAccountName"
            }
        },"single",true);
    },

    showTransferMenu : function(entry, callboxid, exten ) {
        if( callboxid != '' ) {
            exten = prompt("Please enter the extension number you'd like to transfer to:\n(Leave Blank to cancel)","");
		
            if( exten != null && exten != '') {
                $.post("index.php?entryPoint=AsteriskController&action=transfer", {
                    id: callboxid, 
                    call_record: entry['call_record_id'], 
                    extension: exten
                });
            }
        }
    }, 
    
    /*
 * Relate Contact Callback method.
 * This is called by the open_popup sugar call when a contact is selected.
 *
 * I basically copied the set_return method and added some stuff onto the bottom.  I couldn't figure out how to add
 * change events to my form elements.  This method wouldn't be needed if I figured that out.
 */
    relate_contact_popup_callback : function(popup_reply_data){
        var from_popup_return2 = true;
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
                var displayValue=name_to_value_array[the_key].replace(/&amp;/gi,'&').replace(/&lt;/gi,'<').replace(/&gt;/gi,'>').replace(/&#039;/gi,'\'').replace(/&quot;/gi,'"');
                ;
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
            YAAI.setBeanID(form_name,'contacts',contactId);
        }
        else {
            alert("Error updating related Contact");
        }
    },

    /*
     * Relate Account Callback method.
     * This is called by the open_popup sugar call when a contact is selected.
     *
     * I basically copied the set_return method and added some stuff onto the bottom.  I couldn't figure out how to add
     * change events to my form elements.  This method wouldn't be needed if I figured that out.
     */
    relate_account_popup_callback : function(popup_reply_data){
        var from_popup_return2 = true;
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
                var displayValue=name_to_value_array[the_key].replace(/&amp;/gi,'&').replace(/&lt;/gi,'<').replace(/&gt;/gi,'>').replace(/&#039;/gi,'\'').replace(/&quot;/gi,'"');
                ;
                if(window.document.forms[form_name] && window.document.forms[form_name].elements[the_key])
                {
                    window.document.forms[form_name].elements[the_key].value = displayValue;
                    SUGAR.util.callOnChangeListers(window.document.forms[form_name].elements[the_key]);
                }
            }
        }

        // Everything above is from the default set_return method in parent_popup_helper.

        var accountId = window.document.forms[form_name].elements['relateAccountId'].value;
        if( accountId != null ) {
            YAAI.setBeanID(form_name,'accounts',accountId);
        }
        else {
            alert("Error updating related Account");
        }

    },


    // -=-=-=-= DRAWING/UI FUNCTIONS =-=-=-=-=-=-=-=-=- //

    restructureCallBoxes : function(callboxid) {
        var currHeight = 0;
        for(var i=0; i < YAAI.callBoxes.length; i++ ) {
            var callboxid = YAAI.callBoxes[i];
            
            if( !YAAI.isCallBoxClosed( callboxid ) ) {   
                //put first box at 0 height - bottom of page
                $("#callbox_"+callboxid).css('bottom', currHeight+'px');       
                //then grab the height of the box - this will tell if it is open or not
                currHeight += $("#callbox_"+callboxid).height();  
            }
        }
        YAAI.nextHeight = currHeight;
	
    },
    
    minimizeExistingCallboxesWhenNewCallComesIn : function(){
        for(var x=0; x < YAAI.callBoxes.length; x++ ) {
            YAAI.minimizeCallBox( YAAI.callBoxes[x] ); // updates a cookie each time... perhaps check first.
        }
    },
    
    startVerticalEndVertical : function(callboxid){
        // START VERTICAL
        YAAI.restructureCallBoxes();
        $("#callbox_"+callboxid).css('right', '20px');
        $("#callbox_"+callboxid).css('bottom', YAAI.nextHeight+'px');
        // END VERTICAL
        YAAI.callBoxes.push(callboxid);
    },
    
    setupCallBoxFocusAndBlurSettings : function(callboxid){
        YAAI.callboxFocus[callboxid] = false;
        $("#callbox_"+callboxid+" .callboxtextarea").blur(function(){
            YAAI.callboxFocus[callboxid] = false;
            $("#callbox_"+callboxid+" .callboxtextarea").removeClass('callboxtextareaselected');
        }).focus(function(){
            YAAI.callboxFocus[callboxid] = true;
            YAAI.newMessages[callboxid] = false;
            $('#callbox_'+callboxid+' .callboxhead').removeClass('callboxblink');
            $("#callbox_"+callboxid+" .callboxtextarea").addClass('callboxtextareaselected');
        });
    },

    maximizeCallBox : function(callboxid) {
        $('#callbox_'+callboxid+' .control_panel').css('display', 'block');
        $('#callbox_'+callboxid+' .callboxcontent').css('display','block');
        $('#callbox_'+callboxid+' .callboxinput').css('display','block');
        //$("#callbox_"+callboxid+" .callboxcontent").scrollTop($("#callbox_"+callboxid+" .callboxcontent")[0].scrollHeight);
				
        if( YAAI.isCallBoxMinimized( callboxid ) ) {
            YAAI.log( callboxid + " minimize state cookie fail (should be maximized)");
        }
		
        YAAI.updateMinimizeCookie();
    },

    minimizeCallBox : function(callboxid) {
        $('#callbox_'+callboxid+' .control_panel').css('display', 'none');
        $('#callbox_'+callboxid+' .callboxcontent').css('display','none');
        $('#callbox_'+callboxid+' .callboxinput').css('display','none');
		
        if( !YAAI.isCallBoxMinimized( callboxid ) ) {
            YAAI.log( callboxid + " minimize state cookie fail");
        }
		
        YAAI.updateMinimizeCookie();
    },
    
    showCallerIDWhenAvailable : function(entry){
        if(entry['caller_id']){
            $('#caller_id').show();
        }
    },
    
    refreshSingleMatchView : function (callboxid, entry){

        console.log("Refreshing single match");
        var singlematching = $('#callbox_'+callboxid).find('.singlematchingcontact');

        // 1 Match --> 1 Match Different Contact or Account
        //check if a single contacts match has had changes - must do this here because using SugarCRMs function we lose control of the callboxid that initated the callback
        if( entry['beans'].length == 1 && singlematching.is(':visible')){

            // TODO REFACTOR
            if( entry['beans'].length == 1 ) {
                   //check on id, because name could be duplicate
               var old_contact_id = $('#callbox_'+callboxid).find('.contact_id').attr('href').substr(-36);
               var new_contact_id = entry['beans'][0]['bean_id'];
               var old_company_id = $('#callbox_'+callboxid).find('.company_id').attr('href') == undefined ? null : $('#callbox_'+callboxid).find('.company_id').attr('href').substr(-36)
               var new_company_id = entry['beans'][0]['parent_id'];
               if(old_contact_id != new_contact_id || old_company_id != new_company_id){
                   YAAI.refreshSingleMatchingContact(callboxid, entry);
                   YAAI.log('Refreshing ' + callboxid);
               }
            }
        }
        
        // MULTIPLE OR NO MATCH --> Single case
        if( entry['beans'].length == 1 && singlematching.is(':hidden') ){
            //bind back the unrelate button
            YAAI.bindOpenPopupSingleMatchingContact(callboxid, entry);
            
            $('#callbox_'+callboxid).find('.nomatchingcontact').hide();
            $('#callbox_'+callboxid).find('.multiplematchingcontacts').hide();
            YAAI.refreshSingleMatchingContact(callboxid, entry);
        }
    },
    
    refreshSingleMatchingContact : function(callboxid, entry){
        var bean = entry['beans'][0];

        $('#callbox_'+callboxid).find('.singlematchingcontact').show();
        $('#callbox_'+callboxid).find('.singlematchingcontact td a.contact_id').attr('href', bean['bean_link']);
        $('#callbox_'+callboxid).find('.singlematchingcontact td span.call_contacts').text(bean['bean_name']);
        
        //check if new contact has an account
        if(bean['parent_name'] == null || bean['parent_name'].length <= 0 ) {
            $('#callbox_'+callboxid).find('.parent_name_box').hide();
        }else{
            if( bean['parent_link'] != null ) {
                $('#callbox_'+callboxid).find('.parent_name_box td a.company').attr('href', bean['parent_link']);
            }
            else {
                $('#callbox_'+callboxid).find('.parent_name_box td a.company').attr('href', '#');
            }
            $('#callbox_'+callboxid).find('.parent_name_box td a.company').text(bean['parent_name']);
            $('#callbox_'+callboxid).find('.parent_name_box').show();
        }
    },

    // Saves what is placed in the input box whenever call is saved.
    checkCallBoxInputKey : function(event, callboxid, call_record_id, phone_number, direction) {
	 
        // 13 == Enter
        if(event.keyCode == 13)  {
            // CTRL + ENTER == quick save + close shortcut
            if( event.ctrlKey == 1 ) {
                YAAI.saveMemo(call_record_id, phone_number, direction);
                YAAI.closeCallBox(callboxid, call_record_id);
                return false;
            }
            else if( event.shiftKey != 0 ) {
                YAAI.saveMemo(call_record_id, phone_number, direction);
                //return false; // Returning false prevents return from adding a break.
            }
        }

    },

    setupHandlebarsContextNoMatchingCase : function(callboxid, context, entry){

    },

    /**
     * Sets up the handlebars context for the single matching Account or Contact Case.
     *
     * @param callboxid
     * @param context
     * @param entry
     * @return {*}
     */
    setupHandlebarsContextForSingleMatchingCase : function(callboxid, context, entry){
        var bean = entry['beans'][0];
        console.log(bean);
        context['bean_id'] = bean['bean_id'];
        context['bean_module'] = bean['bean_module'];
        context['bean_name'] = bean['bean_name'];
        context['bean_link'] = bean['bean_link'];
        context['parent_name'] = bean['parent_name'];
        context['parent_id'] = bean['parent_id'];
        context['parent_link'] = bean['parent_link'];

        return context;
    },
    setupHandlebarsContextForMultipleMatchingCase : function(callboxid, context, entry){
        context['beans'] = entry['beans'];

        Handlebars.registerHelper('each', function(context, options) {
            if(typeof context != "undefined"){
                var ret = "";
                for(var i=0, j=context.length; i<j; i++) {
                    ret = ret + options.fn(context[i]);
                }
                return ret;
            }
        });
    
        return context;
    },
    
    setCallBoxHeadColor : function (callboxid, entry){
        if( entry['is_hangup']  ) {
            $("#callbox_"+callboxid+" .callboxhead").css("background-color", "#f99d39"); // an orange color
        }
        else {
            $("#callbox_"+callboxid+" .callboxhead").css("background-color", "#0D5995"); // a blue color
        }
    },

    setTransferButton : function(callboxid, entry ) {
        if( entry['is_hangup'] ) {
            this.hideTransferButton(callboxid,entry);
        }
        else {
            if( this.showTransferButton ) {
                this.showTransferButton(callboxid,entry);
            }
        }
    },

    //UTILITY FUNCTIONS
    createContact : function (entry) {
        var phone_number = entry['phone_number'];
        window.location = "index.php?module=Contacts&action=EditView&phone_work="+phone_number;
    },    
    // Updates the cookie which stores the state of all the callboxes (whether minimized or maximized)
    // Only problem with this approach is on second browser window you might have them open differently... and this would save the state as such.
    updateMinimizeCookie : function() {
        var cookieVal="";
        for( var i=0; i< YAAI.callBoxes.length; i++ ) {
		
            if( YAAI.isCallBoxMinimized( YAAI.callBoxes[i] ) ) {
                cookieVal = YAAI.callBoxes[i] + "|";
            }
        }
	
        cookieVal = cookieVal.substr(0, cookieVal.length - 1 ); // remove trailing "|"
	
        $.cookie('callbox_minimized', cookieVal);
    },
    checkMinimizeCookie : function (callboxid){
        // Check by looking at the cookie to see if it should be minimized or not.
        var minimizedCallBoxes = new Array();

        if ($.cookie('callbox_minimized')) {
            minimizedCallBoxes = $.cookie('callbox_minimized').split(/\|/);
        }
        var minimize = 0;
        for (var j=0;j < minimizedCallBoxes.length;j++) {
            if (minimizedCallBoxes[j] == callboxid) {
                minimize = 1;
            }
        }

        if (minimize == 1) {
            $('#callbox_'+callboxid+' .control_panel').css('display', 'none');
            $('#callbox_'+callboxid+' .callboxcontent').css('display','none');
            $('#callbox_'+callboxid+' .callboxinput').css('display','none');
        }
    },
    
    getAsteriskID : function(astId){
    
        var asterisk_id = astId.replace(/\./g,'-'); // ran into issues with jquery not liking '.' chars in id's so converted . -> -BR //this should be handled in PHP
    
        return asterisk_id;
    }, 

    isCallBoxClosed : function(callboxid) {
        return $('#callbox_'+callboxid).length == 0;
    },
    
    isCallBoxMinimized : function( callboxid ) {

        return $('#callbox_'+callboxid+' .callboxcontent').css('display') == 'none';

    },
    
    callBoxHasNotAlreadyBeenCreated : function(callboxid){
        var open = (-1 == $.inArray(callboxid, YAAI.callBoxes));
        
        if ($("#callbox_"+callboxid).length > 0) {
            if ($("#callbox_"+callboxid).css('display') == 'none') {
                $("#callbox_"+callboxid).css('display','block');
                YAAI.restructureCallBoxes(callboxid);
            }
        }
        
        return open;
    },
    
    checkForErrors : function(entry){
        if( entry['call_record_id'] == "-1" ) {
            YAAI.log( "Call Record ID returned from server is -1, unable to save call notes for " + entry['title'] ); // TODO: disable the input box instead of this alert.
        }  
    },
 
    getMemoText : function( callboxid ) {
        var message = "";
        message = $('#callbox_'+callboxid+' .callboxinput .callboxtextarea').val();
        message = message.replace(/^\s+|\s+$/g,""); // Trims message
	
        return message;
    },
 
    getCookies : function(){
        var pairs = document.cookie.split(";");
        var cookies = {};
        for (var i=0; i<pairs.length; i++){
            var pair = pairs[i].split("=");
            cookies[pair[0]] = unescape(pair[1]);
        }
        return cookies;
    },
    
    log : function(message) {
        if (window.callinize_debug == 1) {
            console.log(message);
        }
    },
    callStateIsNotFiltered : function(entry){
      //this is required to filter call states that would change to Hangup but have an answered state of 0
      
      if(YAAI.filteredCallStates == 'Ringing' || YAAI.filteredCallStates == 'Dial'){
          if(entry.answered == '0'){
              return false;
          }
      }
      
      return ($.inArray(entry.state, YAAI.filteredCallStates) == -1);
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

$(document).ready(function(){

    var histLoaded = true;
    if( typeof(SUGAR.ajaxUI) !== "undefined") {
        histLoaded = SUGAR.ajaxUI.hist_loaded;
    }
    var isAjaxUiEnabled=/ajaxUI/gi.test(window.location.search.substring(1));
    //YAAI.log('ready() hist_loaded: ' + SUGAR.ajaxUI.hist_loaded + " ajaxUIEnabled = " + isAjaxUiEnabled);

    // if ajaxui in url... and SUGAR.ajaxUI.hist_loaded is true. -- include
    // or if ajax isn't in url --- include
    if( !isAjaxUiEnabled || histLoaded ) {
        YAAI.log('loading yaai...');
        var loop = true;
        YAAI.checkForNewStates(loop);

        }
});
