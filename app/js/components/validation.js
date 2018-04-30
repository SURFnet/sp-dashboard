import 'parsleyjs/src/parsley';

// Configure Parsley
const parsleyConfig = {
    errorsWrapper: '<ul></ul>',
    errorTemplate: '<li class="error"></li>',
    errorClass: 'error',
    classHandler: function(parsleyField) {
        return parsleyField.$element.closest('.form-row');
    },
    errorsContainer: function(parsleyField) {
        return parsleyField.$element.closest('.form-row').find('.parsley-errors');
    }
};

const form = $('form[name="dashboard_bundle_entity_type"]');

if (form.length) {
    // Use parsley for url and email verification
    $(form).parsley(parsleyConfig);

    // Custom validation is added to show the attribute motivation warnings
    $('input.motivation').on('keyup', function () {

        let parent = $(this).closest('.form-row');
        let errorContainer = parent.parents('.form-row').find('.parsley-errors').first();

        if ($(this).val().length <= 10) {
            errorContainer.parent('.form-row').addClass('warning');
            errorContainer.text('Please provide a more detailed motivation.');
        } else {
            errorContainer.parent('.form-row').removeClass('warning');
            errorContainer.text('');
        }
    });
}