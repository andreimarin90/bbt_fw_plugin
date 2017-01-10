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

});