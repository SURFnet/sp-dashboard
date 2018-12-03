import * as $ from 'jquery';

class EntityOidcForm {
  private $grantTypeToggle: JQuery;
  private $grantTypeContainer: JQuery;

  /**
   * @param $grantTypeToggle The container with radio buttons to toggle on
   * @param $grantTypeContainer The form-row with the signed-contract questions to show/hide
   */
  constructor(
    $grantTypeToggle: JQuery,
    $grantTypeContainer: JQuery,
  ) {
    this.$grantTypeToggle = $grantTypeToggle;
    this.$grantTypeContainer = $grantTypeContainer;
  }

  /**
   * Init the eventhandlers on the elements
   */
  public registerEventHandlers() {
    // Init the grant type toggle
    this.initToggleGrantTypeField();
  }

  /**
   * Disable the contract signed field when the type of service is for an institute
   */
  private initToggleGrantTypeField() {
    const $first = this.$grantTypeToggle.find(':radio:first');
    $first.prop('checked', true);

    this.registerToggleGrantHandler();
  }

  private registerToggleGrantHandler() {
    const toggleGrantType = () => {
      const grantTypeValue = this.$grantTypeToggle.find(':checked').val();
      const inputs = this.$grantTypeContainer.find('input');
      for (const input of inputs) {
        const row = input.closest('.radio-container');
        if (input.data('show') === grantTypeValue) {
          row.show();
        } else {
          if (input.is(':checked')) {
            input.prop('checked', false);
          }
          row.hide();
        }
      }
    };
    toggleGrantType();
    this.$grantTypeToggle.on('change', toggleGrantType);
  }
}

export function loadEntityOidcForm() {
  if ($('form[name="dashboard_bundle_entity_type"]').length > 0) {
    const entityOidcForm = new EntityOidcForm(
      $('.grant-type-toggle'),
      $('.grant-type-response-type-container'),
    );
    entityOidcForm.registerEventHandlers();
  }
}

$(document).ready(loadEntityOidcForm);
