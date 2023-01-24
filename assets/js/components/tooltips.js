'use strict';

import tippy from 'tippy.js/dist/esm/tippy.standalone'

$(document).ready(
    () => tippy(
        '.help-button',
       {
           animation: 'scale',
           arrow: true,
           duration: 200,
           placement: 'left',
           content: '',
           theme: 'light',
           trigger: 'click'
       }
    )
);
