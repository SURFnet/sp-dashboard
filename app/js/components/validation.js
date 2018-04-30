import 'parsleyjs/src/parsley';

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
    $(form).parsley(parsleyConfig);
}