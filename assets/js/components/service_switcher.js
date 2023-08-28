'use strict';
import $ from 'jquery';
require('jquery.mousewheel');
require('select2');

$(document).ready(function() {
    function submitFormOnSelectionChange() {
        $(this).parents('form').submit();
    }

    $('select#service-switcher')
        .select2({
            placeholder: ''
        })
        .change(submitFormOnSelectionChange);
});
