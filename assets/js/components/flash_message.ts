import * as $ from 'jquery';

$('.flashMessage__close').on('click', (el: JQuery.TriggeredEvent) => {
  $(el.target).parent('.flashMessage').remove();
});
