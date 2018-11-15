import * as $ from 'jquery';

class ServiceForm {
  private $privacyQuestionsEnabledToggle: JQuery;
  private $privacyQuestionsStatusContainer: JQuery;
  private $contractSignedToggle: JQuery;
  private $contractSignedContainer: JQuery;

  /**
   * @param $privacyQuestionToggle The selectbox to toggle the privacy question on
   * @param $privacyQuestionContainer The form-row twith the privacy enabled question to show/hide
   * @param $contractSignedToggle The container with radio buttons to toggle on
   * @param $contractSignedContainer The form-row with the signed-contract questions to show/hide
   */
  constructor(
    $privacyQuestionToggle: JQuery,
    $privacyQuestionContainer: JQuery,
    $contractSignedToggle: JQuery,
    $contractSignedContainer: JQuery,
  ) {
    this.$privacyQuestionsEnabledToggle = $privacyQuestionToggle;
    this.$privacyQuestionsStatusContainer = $privacyQuestionContainer;
    this.$contractSignedToggle = $contractSignedToggle;
    this.$contractSignedContainer = $contractSignedContainer;
  }

  /**
   * Init the eventhandlers on the elements
   */
  public registerEventHandlers() {
    // Init the privacy questions answered toggle
    this.initTogglePrivacyQuestionsAnsweredStatusField();

    // Init the contract signed toggle
    this.initToggleContractSignedStatusField();
  }

  /**
   * Disable the contract signed field when the type of service is for an institute
   */
  private initToggleContractSignedStatusField() {
    const toggleContractSigned = () => {
      const serviceTypeValue = this.$contractSignedToggle.find(':checked').val();
      if (serviceTypeValue === 'institute') {
        this.showElement(this.$contractSignedContainer.parent());
      } else {
        this.hideElement(this.$contractSignedContainer.parent());
      }
    };

    this.$contractSignedToggle.on('change', toggleContractSigned);
    toggleContractSigned();
  }

  /**
   * Disable the privacy questions answered field when the privact question enabled checkbox is unchecked
   */
  private initTogglePrivacyQuestionsAnsweredStatusField() {
    const togglePrivacyQuestions = () => {
      if (this.$privacyQuestionsEnabledToggle.is(':checked')) {
        this.showElement(this.$privacyQuestionsStatusContainer.parent());
      } else {
        this.hideElement(this.$privacyQuestionsStatusContainer.parent());
      }
    };
    this.$privacyQuestionsEnabledToggle.on('change', togglePrivacyQuestions);
    togglePrivacyQuestions();
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
  if ($('#dashboard_bundle_edit_service_type').length > 0 || $('#dashboard_bundle_service_type').length > 0) {
    const serviceForm = new ServiceForm(
      $('.privacy-questions-toggle'),
      $('.privacy-questions-container'),
      $('.contract-signed-toggle'),
      $('.contract-signed-container'),
    );
    serviceForm.registerEventHandlers();
  }
}

$(document).ready(loadServiceForm);
