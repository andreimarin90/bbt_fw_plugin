(function($){
    'use strict';

    if(jQuery('.bbt_toggle_option_input').length !== 0){
        (function($) {
            $.fn.bbtToggleVcOption = function() {
                this.each(function() {
                    var toggle = $(this),
                        input = toggle.find('input'),
                        input_value = 'false';

                    if(input.val() === 'true') {
                        toggle.addClass('toggle_on');
                        input_value = 'true';
                    } else {
                        toggle.addClass('toggle_off');
                        input_value = 'false';
                    }

                    toggle.on('click', function() {
                        if(toggle.hasClass('toggle_on')) {
                            toggle.removeClass('toggle_on').addClass('toggle_off');
                            input.val('false');
                        } else {
                            toggle.removeClass('toggle_off').addClass('toggle_on');
                            input.val('true');
                        }
                    });
                });

                return this;
            };
        }(jQuery));
    }

    if($('.btn-facebook-login').length !== 0) {
        (function () {
            $.ajaxSetup({cache: false});
            $.getScript('//connect.facebook.net/en_US/sdk.js', function () {
                FB.init({
                    appId: $('.btn-facebook-login').attr('data-app-id'),
                    version: 'v2.5'
                });
                $('.btn-facebook-login').on('click', function (e) {
                    FB.getLoginStatus(function (response) {
                        //console.log(response)
                        if (response.status === 'connected') {

                            bbt_fbApiCall();
                            return;
                        }
                    });
                    //calling FB.login outside FB.getLoginStatus because we get the popup blocked otherwise (must be directlly inside click event function)
                    FB.login(function (response) {
                        //console.log(response)
                        if (response.status === 'connected') {
                            // Logged into your app and Facebook.
                            bbt_fbApiCall()
                        } else if (response.status === 'not_authorized') {
                            // The person is logged into Facebook, but not your app.
                        } else {
                            // The person is not logged into Facebook, so we're not sure if
                            // they are logged into this app or not.
                            alert('please login to Facebook');
                        }
                    }, {scope: 'public_profile,email'});
                })

            });
        })();

        var bbt_fbApiCall = function () {
            FB.api('/me?fields=id,name,first_name,last_name,email', function (res) {
                if (res) {
                    console.log(res);
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'bbt_fb_login',
                            fbuser: res
                        }
                    })
                        .done(function (response) {
                            console.log(response);
                            if (typeof response.user_id !== 'undefined') {
                                bbt_afterLoginAction();
                            }

                        })
                        .fail(function (error, message) {
                            console.error(error, message);
                        })

                }
            });
        };
    }

    window.BBT_TwLoginCallback = function(result){	//callback called from twitter Auth popup from twitterOauth
        console.log(result);
        if(typeof result.user_id !== 'undefined'){
            bbt_afterLoginAction();
        }
    };

    function bbt_afterLoginAction(){
        location.reload();
    }

})(jQuery);
