import 'parsleyjs';

// Configure Parsley
const parsleyConfig = {
    errorsWrapper: '<ul></ul>',
    errorTemplate: '<li class="error"></li>',
    errorClass: 'error',
    classHandler: function (parsleyField) {
        return parsleyField.$element.closest('.form-row');
    },
    errorsContainer: function (parsleyField) {
        return parsleyField.$element.closest('.form-row').find('.parsley-errors');
    }
};

const form = $('form[name="dashboard_bundle_entity_type"]');

if (form.length) {
    // Use parsley for url and email verification
    $(form).parsley(parsleyConfig);

    // Show a warning when less than 10 words are typed in attribute descriptions or description text areas.
    $('input.motivation').on('keyup blur', function () {
        const motivationInput = $(this);

        validateDescription(
            motivationInput,
            motivationInput.closest('.attribute-row-wrapper').parents('.form-row').find('.parsley-errors').first(),
            motivationInput.data('motivation-keep-talking')
        );
    });

    $('input.requested').on('change', function () {
        const checkboxInput = $(this);
        const wrapper = checkboxInput.closest('.attribute-row-wrapper').parents('.form-row');
        const motivationInput = wrapper.find('input.motivation');
        const errorContainer = wrapper.find('.parsley-errors').first();

        if (checkboxInput.is(':checked')) {
            validateDescription(
                motivationInput,
                motivationInput.closest('.attribute-row-wrapper').parents('.form-row').find('.parsley-errors').first(),
                motivationInput.data('motivation-keep-talking')

            );
        } else {
            hideWarning(errorContainer);
        }
    });

    $('textarea#dashboard_bundle_entity_type_metadata_descriptionNl,textarea#dashboard_bundle_entity_type_metadata_descriptionEn').on('keyup blur', function () {
        validateDescription(
            $(this),
            $(this).siblings('.parsley-errors'),
            'Please describe the entity in ten or more words.'
        );
    });

    function validateDescription(inputElement, errorContainer, warningText) {
        if (!descriptionHasEnoughWords(inputElement)) {
            showWarning(errorContainer, warningText);
        } else {
            hideWarning(errorContainer);
        }
    }

    function descriptionHasEnoughWords(input) {
        let val = input.val();
        let wordThreshold = 10;

        const configuredThreshold = input.data('word-threshold');

        if (configuredThreshold && Number.isInteger(configuredThreshold)) {
            wordThreshold = input.data('word-threshold');
        }

        // See service_edit_attribute.js, it stores the value of a
        // disabled attribute in 'data-old-value', and restoring that
        // value might happen after the description validation.
        if (val.length === 0 && input.data('old-value') !== undefined) {
            val = input.data('old-value');
        }

        const words = val.split(' ').filter(v => v !== "");

        return (val === '' || words.length >= wordThreshold);
    }

    function showWarning(errorContainer, warning) {
        errorContainer.parent('.form-row').addClass('warning');
        errorContainer.text(warning);
    }

    function hideWarning(errorContainer) {
        errorContainer.parent('.form-row').removeClass('warning');
        errorContainer.text('');
    }

    // When clicking import, save or cancel, do not validate the form using frontend validation, so disable parsley and
    // submit the form.
    $('#dashboard_bundle_entity_type_metadata_importButton, #dashboard_bundle_entity_type_save, #dashboard_bundle_entity_type_cancel').click(function(){
        $(form).parsley().destroy();
        $(form).submit();
    });
}

// Add a stricter url validator, fields validated with urlstrict are
// allowed to be empty, or a URL with protocol.
window.Parsley.addValidator('urlstrict', function (value, requirement) {
    return validateEmpty(value) || validateUrl(value);
}, 32).addMessage('en', 'urlstrict', 'This value should be a valid URL.');

// Add URI validator (must be URN or URL), fields validated with urn
// must be empty or be a valid URN  or URL (with protocol).
window.Parsley.addValidator('uri', function (value, requirement) {
    return validateEmpty(value) || validateUrl(value) || validateUrn(value);
}, 32).addMessage('en', 'uri', 'This value should be a valid URL or URN.');

function validateEmpty(value) {
    return value === '';
}

function validateUrl(value) {
    var regExp = /^(https?|s?ftp|git):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i;

    return regExp.test(value);
}

function validateUrn(value) {
    var regExp = /^urn:[a-z0-9][a-z0-9-]{0,31}:[a-z0-9()+,\-.:=@;$_!*'%/?#]+$/i;

    return regExp.test(value);
}

window.Parsley.addValidator('redirecturis', {
    validateString: function(value, requirement, instance) {
        let count  = 0;
        instance.$element.closest('.collection-widget').find('input').each(function (idx, value) {
            if ($(value).val().length > 0) {
                count++;
            }
        });
        return count > 0;
    },
    messages: {
        en: 'At least one redirecturi must be set.',
    }
}, 32);

