jQuery(document).ready(function($){
    "use strict";

    var inst = {
        $el: $('#bbt_demo_content_list'),

        getInstallConfirmation : function($thisBtn){
            return confirm($($thisBtn).data('confirm'));
        },

        showTimer : function(){
            var sec = 0;
            function bbt_timer( val ) { return val > 9 ? val : "0" + val; }

            setInterval( function(){
                $("#bbt_popup_action .bbt_time_text span").html( bbt_timer(parseInt( sec/60,10 )) + ':' + bbt_timer(++sec % 60));
            }, 1000);
        },

        showPopup : function(){
            //$('#bbt_popup .bbt_popup_title .dashicons').hide();
            $('#bbt_popup, #bbt_cover_popup, #bbt_popup .bbt_popup_title .spinner').show();
        },

        hidePopup : function(){
            $('#bbt_popup .bbt_close_icon').on('click', function(){
                $(this).hide();
                $('#bbt_popup .bbt_popup_title .dashicons').hide();
                $('#bbt_popup, #bbt_cover_popup').hide();
                $('#bbt_popup_action').empty().text($('#bbt_popup_action').data('begin'));
            });

        },

        makeInstall : function($mainThis, $thisInstall){
            var installID = $($thisInstall).data('install');

            //make install :)
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data:{"action" : "bbt_make_import", install_id : installID},
                cache: false,
                beforeSend: function(){
                    $('#bbt_popup_action').empty().html('<div>' + $('#bbt_popup_action').data('begin') + '<div>');
                    $('#bbt_popup_action').append('<div class="bbt_time_text">' + $('#bbt_popup_action').data('time') + '<div>');
                    $('#bbt_popup_action').append('<div class="bbt_time_text">' + $('#bbt_popup_action').data('estimated-time') + '<div>');
                    $('#bbt_popup_action').append('<div class="bbt_time_text">' + $('#bbt_popup_action').data('timer') + '<span></span><div>');
                    $mainThis.showTimer();
                },
                success: function(rsp) {
                    console.log(rsp);

                    var obj = {};

                    try {
                        obj = jQuery.parseJSON(rsp);
                    } catch (e) {
                        obj['notices'] = '';
                        obj['install'] = 'no';
                        obj['message'] = rsp;
                    }

                    //if install failed abort
                    if(obj['install'] == 'no'){
                        //show import status messages
                        jQuery('#bbt_popup_action').empty().html('<span style="color:red;">' + obj['message'] + '</span>');
                        $('#bbt_popup .bbt_close_icon , #bbt_popup .bbt_popup_title .dashicons.dashicons-no').show();
                        $('#bbt_popup .bbt_popup_title .spinner').hide();

                        //hide popup on click
                        $mainThis.hidePopup();
                    }
                    else if(obj['install'] == 'yes'){

                        //show import status messages
                        jQuery('#bbt_popup_action').empty().html('<span style="color:green;">' + obj['message'] + '</span>');
                        $('#bbt_popup .bbt_close_icon , #bbt_popup .bbt_popup_title .dashicons.dashicons-yes').show();
                        $('#bbt_popup .bbt_popup_title .spinner').hide();

                        //hide popup on click
                        $mainThis.hidePopup();

                        //redirect to homepage
                        window.location = obj['home_url'];
                    }
                    else{
                       // setTimeout(function(){ $mainThis.hidePopup(); }, 5000);
                    }
                }
            });
        },

        onInstall : function($mainThis){
            "use strict";

            $(this.$el).find('.bbt-demo-item').find('a.button').on('click', function(){
                var getConfirm = $mainThis.getInstallConfirmation(this);

                //if install confirmed, make the install
                if(getConfirm)
                {
                    //show popup with progress
                    $mainThis.showPopup();
                    //make install
                    $mainThis.makeInstall($mainThis, this);
                }

                return false;
            });
        },

        init: function(){
            "use strict";

            //save main this
            var $mainThis = this;

            this.onInstall($mainThis);

        }
    };

    inst.init();
});