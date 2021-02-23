import * as $ from 'jquery';

$(() => {
  $('.add-entity-protocol .add-entity-radio').on('change', (e) => {
    const value = $(e.target).val();
    const modal = $(e.target).closest('.add-entity-modal');

    handleModalChange(value as string, modal);
  });
});

function handleModalChange(value: string, modal: JQuery) {
  const templateChoices = modal.find('.add-entity-template-choices');
  const yesButton = modal.find('.add-entity-yes');

  // reset all choices & re-enable the yesButton
  templateChoices
    .find('.add-entity-field')
    .removeClass('hidden');
  yesButton.removeAttr('disabled');

  // hide invalid protocol choices
  templateChoices
    .find(`.add-entity-field:not([data-protocol="${value}"])`)
    .addClass('hidden');

  // disable yesButton if there are no valid protocol choices
  const visibleItems = templateChoices.find('.add-entity-field:not(.hidden)');
  if (!visibleItems.length) {
    yesButton.attr('disabled', 'disabled');
  }
}
