import * as $ from 'jquery';

class ServiceForm {
  private $toggle: JQuery;
  private $contractSignedContainer: JQuery;
  private $repApprovedContainer: JQuery;
  private $institutionIdContainer: JQuery;

  /**
   * @param $toggle The container with radio buttons to toggle on
   * @param $contractSignedContainer The form-row with the signed-contract questions to show/hide
   * @param $repApprovedContainer The form-row with the representative approved radio buttons to show/hide
   */
  constructor(
    $toggle: JQuery,
    $contractSignedContainer: JQuery,
    $repApprovedContainer: JQuery,
    $institutionIdContainer: JQuery,
  ) {
    this.$toggle = $toggle;
    this.$contractSignedContainer = $contractSignedContainer;
    this.$repApprovedContainer = $repApprovedContainer;
    this.$institutionIdContainer = $institutionIdContainer;
  }

  /**
   * Init the eventhandlers on the elements
   */
  public registerEventHandlers() {
    // Init the container fields
    this.initToggleField();
  }

  /**
   * Show the Representative approved container when institute is checked
   * Show the Contract signed container when the non-institute is checked
   */
  private initToggleField() {
    const toggleContractSigned = () => {
      const serviceTypeValue = this.$toggle.find(':checked').val();
      if (serviceTypeValue === 'institute') {
        this.showElement(this.$repApprovedContainer.parent());
        this.showElement(this.$institutionIdContainer.parent());
        this.hideElement(this.$contractSignedContainer.parent());
      } else {
        this.showElement(this.$contractSignedContainer.parent());
        this.hideElement(this.$repApprovedContainer.parent());
        this.hideElement(this.$institutionIdContainer.parent());
      }
    };

    this.$toggle.on('change', toggleContractSigned);
    toggleContractSigned();
  }

  /**
   * Helper method to hide an element
   */
  private hideElement($element: JQuery) {
    $element.addClass('hidden');
    $element.hide(200);

  }

  /**
   * Helper method to show an element
   */
  private showElement($element: JQuery) {
    $element.removeClass('hidden');
    $element.show(200);
  }
}

export function loadServiceForm() {

  const createFormOnPage = $('form[name="dashboard_bundle_service_type"]').length > 0;
  const editFormOnPage = $('form[name="dashboard_bundle_edit_service_type"]').length > 0;

  if (createFormOnPage || editFormOnPage) {
    const serviceForm = new ServiceForm(
      $('.contract-signed-toggle'),
      $('.contract-signed-container'),
      $('.representative-signed-container'),
      $('.institution-id-container'),
    );
    serviceForm.registerEventHandlers();
  }
}

$(document).ready(loadServiceForm);
