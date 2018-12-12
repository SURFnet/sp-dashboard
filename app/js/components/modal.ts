import * as $ from 'jquery';
import 'jquery-modal';

$.modal.defaults = {
  // Dont show the close button, a cancel button on the modal should perform this task
  showClose: false,
};

if ($('#oidc-published-popup').length) {
  $('#oidc-published-popup').modal();
}
