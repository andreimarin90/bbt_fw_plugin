(function($){
    'use strict';

    if(jQuery('.bbt_toggle_option_input').length !== 0){
        jQuery('.bbt_toggle_option_input').each(function(){
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
    }

})(jQuery);