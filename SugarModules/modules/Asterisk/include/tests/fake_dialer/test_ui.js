
    
var FakeDialer = {
    
    call_counter: 1,
    call_setup_data: {},
 
    action : function (id, extension, phone_number){
    
        FakeDialer.determine_action(id, extension, phone_number);
    },

    determine_action : function(id, extension, phone_number){
        var call_number = id.charAt(id.length-1);

        if(id.search("ringing") >= 0){
            FakeDialer.action_ringing(call_number, extension, phone_number);
        }
        
        if(id.search("connected") >= 0){
            FakeDialer.action_connected(call_number, extension);
        }
        
        if(id.search("hangup") >= 0){
            FakeDialer.action_hangup(call_number, extension);
        }
        
        if(id.search("closed") >= 0){
            FakeDialer.action_closed(call_number, extension);
        }

    },

    action_ringing : function(call_number, extension, phone_number){   
        $.ajax({
            url:"index.php?entryPoint=AsteriskFakeDialerActions",
            data: {
                action: 'ringing',
                extension: extension,
                phone_number: phone_number
            }, 
            type: "POST",			
            success: function(transport){      
                var call_data = $.parseJSON(transport);
                console.log(phone_number);
                //create contacts
                if($('#radio :radio:checked').val() >= 1){
                    var contacts = FakeDialer.action_create_contacts(phone_number, $('#radio :radio:checked').val() );
                    if(contacts['contact_1']){call_data['contact_1'] = contacts['contact_1']}
                    if(contacts['contact_2']){call_data['contact_2'] = contacts['contact_2']}
                }
                
                FakeDialer.call_setup_data[call_number] = call_data;
            },
            error: function (jqXHR, textStatus, thrownError){
                console.log(jqXHR.status);
                console.log(textStatus);
                console.log(thrownError);
            }
        });
    },

    action_connected : function(call_number, extension){
        $.ajax({
            url:"index.php?entryPoint=AsteriskFakeDialerActions",
            data: {
                action: 'connected',
                call_record_id: FakeDialer.call_setup_data[call_number]['call_record_id']     
            }, 
            type: "POST",			
            success: function(){
                $('#ringing_'+call_number).removeClass( "ui-state-highlight" );
            },
            error: function (jqXHR, textStatus, thrownError){
                console.log(jqXHR.status);
                console.log(textStatus);
                console.log(thrownError);
            }
        });
    },

    action_hangup : function(call_number, extension){   
        $.ajax({
            url:"index.php?entryPoint=AsteriskFakeDialerActions",
            data: {
                action: 'hangup',
                call_record_id: FakeDialer.call_setup_data[call_number]['call_record_id']     
            }, 
            type: "POST",			
            success: function(){
                $('#connected_'+call_number).removeClass( "ui-state-highlight" );
            },
            error: function (jqXHR, textStatus, thrownError){
                console.log(jqXHR.status);
                console.log(textStatus);
                console.log(thrownError);
            }
        });
    },

    action_closed : function(call_number, extension){
        var data = { action: 'closed',
                     call_record_id: FakeDialer.call_setup_data[call_number]['call_record_id']  
                    }
        
        if(FakeDialer.call_setup_data[call_number]['contact_1']){data['contact_1'] = FakeDialer.call_setup_data[call_number]['contact_1']}
        if(FakeDialer.call_setup_data[call_number]['contact_2']){data['contact_2'] = FakeDialer.call_setup_data[call_number]['contact_2']}
        
        console.log(data);
        
        $.ajax({
            url:"index.php?entryPoint=AsteriskFakeDialerActions",
            data: data,
            type: "POST",			
            success: function(){
                $('#hangup_'+call_number).removeClass( "ui-state-highlight" );
            },
            error: function (jqXHR, textStatus, thrownError){
                console.log(jqXHR.status);
                console.log(textStatus);
                console.log(thrownError);
            }
        });
    },
    
    action_create_contacts : function(phone_number){
        var contacts = {}
                        
            $.ajax({
                url:"index.php?entryPoint=AsteriskFakeDialerActions",
                async:false,
                data: {
                    action: 'create_contacts',
                    contacts: $('input[name=radio]:checked').val(),
                    phone_number: phone_number
                }, 
                type: "POST",			
                success: function(transport){
                    contacts = $.parseJSON(transport);          
                },
                error: function (jqXHR, textStatus, thrownError){
                    console.log(jqXHR.status);
                    console.log(textStatus);
                    console.log(thrownError);
                }
            }); 
            
        return contacts;
    },
    extension_input_dialog : function(){
        $( "#radio" ).buttonset();
        
        $( "#extension-input" ).dialog({
            autoOpen: false,
            height: 400,
            width: 400,
            modal: true,
            buttons: {
                Ok: function() {                
                    $('#call_setup').clone().appendTo('#main').show().attr('id', 'call_setup_' + FakeDialer.call_counter).find('div').attr('id', function(i, id){
                        return id +'_'+ FakeDialer.call_counter
                    });
                    $('.draggable').draggable();
                    $( ".droppable" ).droppable({
                        drop: function( event, ui ) {
                            
                            $( this )
                            .addClass( "ui-state-highlight" )
                                
                            FakeDialer.action( $(this).attr('id'), $('#extension').val(), $('#phone_number').val()  )      
                                
                            if($(this).attr('id').indexOf("closed") >= 0){
                                $(this).parent('div').hide();
                                    
                            }
                        }
                    });                     
                    FakeDialer.call_counter++;
                    $( this ).dialog( "close" );
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            }
        });
    }
}

$(function() {     
    FakeDialer.extension_input_dialog();
    
    $("#add_call").button().click(function( ) {
        $( "#extension-input" ).dialog( "open" );
    });
    
    $("#clone_elements").hide();
});