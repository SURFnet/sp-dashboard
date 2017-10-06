$(document).ready(function() {
    function initTinyMceTextarea(event) {
        var targetRow = $(event.target).parents('tr');
        if (targetRow.find('td:contains(".help"),td:contains(".html"),td:contains(".information.")').length) {
            var textarea = targetRow.find('textarea');
            if (!textarea.hasClass('tinymce')) {
                textarea.addClass('tinymce');
            }

            initTinyMCE();

            var tinymceInstance = tinymce.get(textarea.attr('id'));
            tinymceInstance.on('Change', function() {
                textarea.val(
                    tinymceInstance.getContent()
                );

                angular.element(textarea)
                    .triggerHandler('input');
            });
        }
    }

    function removeTinyMceTextarea(event) {
        var textarea = $(event.target).parents('tr').find('textarea');

        tinymce.get(textarea.attr('id')).remove();
    }

    $('.translation-ui').on('click', 'tbody td .actions .btn-primary', initTinyMceTextarea);
    $('.translation-ui').on('click', 'tbody td .actions .btn-success', removeTinyMceTextarea);
});
