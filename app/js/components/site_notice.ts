'use strict';

import * as $ from 'jquery';
import * as Cookies from 'js-cookie';

/**
 * Set cookie when closing the site notice to prevent it opening again
 */
$(document).on('click', '.site-notice .notice', () => {
  const siteNotice = $('.site-notice');
  const cookieString = siteNotice.data('cookiestring');
  Cookies.set(cookieString, 'true', { expires: 30, secure: true, sameSite: 'strict' });
  siteNotice.hide('fast', () => {
    siteNotice.remove();
  });
});
