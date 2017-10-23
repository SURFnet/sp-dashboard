'use strict';

require('../../../node_modules/jquery-mousewheel/jquery.mousewheel.js');
require('../../../node_modules/select2/dist/js/select2.js');

$(document).ready(function() {
    function submitFormOnSelectionChange() {
        $(this).parents('form').submit();
    }

    $('select#service-switcher')
        .select2()
        .change(submitFormOnSelectionChange);
});
