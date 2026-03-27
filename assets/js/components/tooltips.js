'use strict';

import tippy from 'tippy.js'
import 'tippy.js/themes/light.css'
import 'tippy.js/animations/scale.css'

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
