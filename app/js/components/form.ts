'use strict';

import * as $ from 'jquery';

/**
 * Add prevent submission of forms by pressing enter in input fields
 * @param $input
 */
$(document).on('keydown', ':input:not(textarea):not(:submit)', (event: JQuery.Event) => {
  if (event.key === 'Enter') {
    event.preventDefault();
  }
});
