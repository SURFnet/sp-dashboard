'use strict';

const tippy = require('../../../node_modules/tippy.js/dist/tippy.all.js');

$(document).ready(
    () => tippy(
        '.help-button',
       {
           animation: 'scale',
           arrow: true,
           duration: 200,
           placement: 'left',
           theme: 'light',
           trigger: 'click'
       }
    )
);
