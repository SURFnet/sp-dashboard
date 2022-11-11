import * as $ from 'jquery';

class EntityAclForm {
  private $toggle: JQuery;
  private $aclContainer: JQuery;

  /**
   * @param $toggle The selectAll checkbox
   * @param $aclContainer The container with the list of idp's
   */
  constructor(
    $toggle: JQuery,
    $aclContainer: JQuery,
  ) {
    this.$toggle = $toggle;
    this.$aclContainer = $aclContainer;
  }

  /**
   * Init the eventhandlers on the elements
   */
  public registerEventHandlers() {
    // Hide container by default
    this.$aclContainer.hide();
    // Init the container fields
    this.initToggleField();
  }

  /**
   * Toggle the list with idp's
   */
  private initToggleField() {
    const toggleAclContainer = () => {
      const selectAll = this.$toggle.prop('checked');
      if (selectAll) {
        this.hideElement(this.$aclContainer);
      } else {
        this.showElement(this.$aclContainer);
      }
    };

    this.$toggle.on('change', toggleAclContainer);
    toggleAclContainer();
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

export function loadEntityAclForm() {

  const aclFormOnPage = $('form[name="acl_entity"]').length > 0;

  if (aclFormOnPage) {
    const entityAclForm = new EntityAclForm(
      $('#acl_entity_selectAll'),
      $('#acl-container'),
    );
    entityAclForm.registerEventHandlers();
  }
}

$(document).ready(loadEntityAclForm);
