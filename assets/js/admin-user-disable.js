 jQuery(document).ready(function() {

    jQuery("#disableuser").click(function() {

        jQuery('.error-message').html();
        let useremail = jQuery("#useremail").val();
        
        if(useremail == null || useremail == '' ){
            jQuery('.error-message').html("Please select a user.").fadeIn().delay(2000).fadeOut('slow');
            return false;
        }
        
        
        let url = ajaxurl;
        jQuery.ajax({
            type: 'POST',
            url: url,
            data: {
                action: 'dwul_action_callback',
                useremail: useremail,
                nonce_data : jQuery( this ).data( 'nonce' )
            },
            beforeSend: function() {
                jQuery("#processimage").show();
            
            },
            success: function(response) {

                if( response == 'success' ) {
                    location.reload();
                    jQuery('.error-message').html();
                    jQuery("#useremail").val('');
                    jQuery("#processimage").hide();
                     return true;
                }

                if( response != 'success' ) {
                    jQuery('.error-message').html(response).fadeIn().delay(2000).fadeOut('slow');    
                    jQuery("#processimage").hide();
                    return false;  
                }                 
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
                return false;
            }
        });
    });
    
    jQuery(".customdisableemail td a").click(function() {

        let acivateid = jQuery(this).attr('id');
     
        let url = ajaxurl;
        jQuery.ajax({
            type: 'POST',
            url: url,
            data: {
                action: 'dwul_enable_user_email',
                nonce_data: jQuery(this).data('enb-nonce'),
                activateuserid: acivateid
            },
            beforeSend: function() {
                

            },
            success: function(userresponse) {
                
                if(userresponse == 1){
                    
                    jQuery("#userid"+acivateid ).fadeOut();
                }
                
            },
            error: function(jqXHR, textStatus, errorThrown) {

                console.log(textStatus, errorThrown);
            }
        });
        return false;
    });

    function formatState (state) {

        if (!state.id) {
            return state.text;
        }
        
        let $state = jQuery(
            '<span>' + state.text + '</span>'
        );
        return $state;
    };


    jQuery("#useremail").select2({
        placeholder: 'Select an option',
        allowClear: true,
        templateResult: formatState
    });  

});
