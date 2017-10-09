'use strict';

$(document).ready(function() {
    function enableOrDisableAttributeField() {
        var rows = $(this)
            .parents('.attribute-row-wrapper')
            .find('.form-row');

        if ($(this).is(':checked')) {
            rows.removeClass('disabled');

            var motivation = rows.find('input.motivation');
            motivation.removeAttr('disabled');
            if (motivation.data('old-value')) {
                motivation.val(motivation.data('old-value'));
            }
        } else {
            rows.addClass('disabled');
            rows.find('input.motivation').attr('disabled', 'disabled');

            var motivation = rows.find('input.motivation');
            motivation.data('old-value', motivation.val());
            motivation.val('');
        }
    }

    $('input.requested').each(enableOrDisableAttributeField);
    $('input.requested').on('change', enableOrDisableAttributeField);
});
