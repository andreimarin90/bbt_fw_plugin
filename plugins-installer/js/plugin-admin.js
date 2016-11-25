jQuery(function($) {

    "use strict";
    $('.plugin-tab-switch').click(function () {
        $('.plugin-tab-switch').removeClass('active');
        $(this).addClass('active');
        $('.bbt-plugin-browser').hide();

        if ($(this).hasClass('required')) {
            $('.bbt-plugin-browser.required').show();
        }

        if ($(this).hasClass('recommended')) {
            $('.bbt-plugin-browser.recommended').show();
        }
    });

    function setCookie(key, value) {
        var expires = new Date();
        expires.setTime(expires.getTime() + 604800); //1 week
        document.cookie = key + '=' + value + ';expires=' + expires.toUTCString();
    }

    $('.notice_update_theme button.notice-dismiss').live('click', function(e) {
        setCookie('notice_update_theme', '1');
    });

    $('.notice_product_key button.notice-dismiss').live('click', function(e) {
        setCookie('notice_product_key', '1');
    });

});