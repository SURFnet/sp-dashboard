import * as $ from 'jquery';

class CollectionWidget {
  private $collectionWidget: JQuery;
  private $collectionList: JQuery<any>;
  private $input: JQuery;
  private prototype: string;
  private index: number =  0;
  private errorMessageSelector: string;
  private errorMessage: string;
  private $sendButton: JQuery;
  private $connectionRequestForm: JQuery;

  /**
   * @param $collectionWidget The collection widget
   */
  constructor(
    $collectionWidget: JQuery,
  ) {
    this.$collectionWidget = $collectionWidget;
    this.$collectionList = this.$collectionWidget.find('table.collection-list');
    this.$collectionList.hide();
    this.prototype = this.$collectionWidget.data('prototype');
    this.$input = $(this.prototype);
    this.$sendButton = $('button[id="connection_request_container_send"]');
    this.$connectionRequestForm = $('form[name="connection_request_container"]');
    this.errorMessageSelector = '.error-message';
    this.errorMessage = '<div class="error-message">This institution is already requested to be connected.</div>';
  }

  /**
   * Init the eventhandlers on the elements
   */
  public registerEventHandlers() {
    // Init the grant type toggle
    this.initCollectionWidget();
  }

  /**
   * Init the collection widget
   * - add input based on the prototype
   * - add button
   */
  private initCollectionWidget() {
    const $collectionContainer = $('<div class="collection-entry base-form"></div>');
    const $addEntryButton = $('<button type="button" class="button-small blue add_collection_entry"><i class="fa fa-plus"></i></button>');

    const $input = this.$input;
    $input.removeAttr('name');
    $input.removeAttr('id');

    $collectionContainer.append(this.$input);
    $collectionContainer.append($addEntryButton);

    this.$collectionWidget.prepend($collectionContainer);

    this.index = this.$collectionList.find('.collection-entry').length;

    this.$collectionList.find('.remove_collection_entry').each((_index: number, el: any) => {
      this.registerRemoveClickHandler($(el));
    });

    this.registerAddClickHandler($addEntryButton);
    this.registerBeforeSubmitHandler($addEntryButton);
    this.registerPreventFormSubmitHandler($input);
    this.registerSendHandler(this.$sendButton);
  }

  /**
   * Add new collection entry with new id
   */
  private addCollectionEntry() {
    const newElement = this.createNewCollectionEntry();
    const isUnique = this.isUnique(newElement);

    if (!isUnique || !this.hasValidInputs()) {
      this.$input.parent().addClass('error');
      if (!isUnique && $(this.errorMessageSelector).length === 0) {
        this.$input.parent().prepend(this.errorMessage);
      }

      return;
    }

    this.clearInputs();
    this.$input.parent().removeClass('error');
    $(this.errorMessageSelector).remove();

    const collectionEntry = $('.collection-list');
    const $removeEntryButton = $('<td><button type="button" class="button-small remove_collection_entry"><i class="fa fa-trash"></i></button></td>');
    this.$collectionList.show();
    this.registerRemoveClickHandler($removeEntryButton);

    // Finally add the remove button to the read only list entry and close the table row.
    newElement.append($removeEntryButton);
    newElement.append('</tr>');
    collectionEntry.append(newElement);
    this.$collectionList.append(collectionEntry);

    this.index += 1;
  }

  /**
   * Remove the collection entry from the list
   * @param el
   */
  private removeCollectionEntry(el: JQuery.TriggeredEvent) {
    const element = $(el.target);

    element.closest('.collection-entry').remove();
    console.log(this.$collectionList.find('tr'))
    if (this.$collectionList.find('tr').length === 1) {
      this.$collectionList.hide();
    }
  }

  /**
   * Create new collection entry with unique name
   *
   * The input fields are hidden, and their value (and label values) are used to create a
   * more compact read-only view. Some exotic mix and match logic was required to create
   * the more compact read-only view.
   */
  private createNewCollectionEntry(): JQuery<HTMLElement> {
    const inputFields = this.prototype.replace(/__name__/g, this.index.toString());
    const $inputContainer = $(inputFields);
    const $fields = $inputContainer.find('input');
    const $outputElement = $('<tr class="collection-entry">');

    const $valueInputElements = this.$input.find('input');
    $fields.each((_index: number, el: HTMLElement) => {
      const $fieldValue = $valueInputElements.eq(_index).val();
      const $el = $(el);
      $el.val($fieldValue as string);
      // The input fields should be in the output element, but they are hidden for the eye candy factor
      $el.hide();
      $outputElement.append(`<td>${$fieldValue}`);
      $outputElement.append($el);
      $outputElement.append('</td>');
    });

    return $outputElement;
  }

  /**
   * Add click handler to add removal of entry
   * @param $removeEntryButton
   */
  private registerRemoveClickHandler($removeEntryButton: JQuery<HTMLElement>) {
    const handleRemoveClick = (el: JQuery.TriggeredEvent) => {
      this.removeCollectionEntry(el);
      if (this.$collectionList.find('.collection-entry').length === 0) {
        this.disableButton(this.$sendButton);
      }
    };
    $removeEntryButton.on('click', handleRemoveClick);
  }

  /**
   * Add click handler to add entry
   * @param $addEntryButton
   */
  private registerAddClickHandler($addEntryButton: JQuery<HTMLElement>) {
    const handleAddClick = () => {
      this.enableButton(this.$sendButton);
      this.addCollectionEntry();
    };
    $addEntryButton.on('click', handleAddClick);
  }

  /**
   * Add prevent submit handler to prevent form submission on enter and instead add entry
   * @param $input
   */
  private registerPreventFormSubmitHandler($input: JQuery<HTMLElement>) {
    const handleKeydownEnter = (event: JQuery.Event) => {
      if (event.key === 'Enter') {
        event.preventDefault();
        this.enableButton(this.$sendButton);
        this.addCollectionEntry();
      }
    };
    $input.on('keydown', handleKeydownEnter);
  }

  /**
   * Add submit handler to add data entered but ot already added to the collection
   * @param $addEntryButton
   */
  private registerBeforeSubmitHandler($addEntryButton: JQuery<HTMLElement>) {
    const handleBeforeSubmit = () => {
      const value = String(this.$input.val());
      if (value.length > 0) {
        $addEntryButton.click();
      }
    };
    const $form = this.$collectionWidget.closest('form');
    $form.on('submit', handleBeforeSubmit);
  }

  private registerSendHandler($sendButton: JQuery<HTMLElement>) {
    const handleSubmit = () => {
      this.disableInputs();
      if (this.hasConnectionRequests()) {
        this.disableParsleyValidation();
      }
      $sendButton.click();
    };
    const $form = this.$collectionWidget.closest('form');
    $form.on('submit', handleSubmit);
  }

  private enableButton($button: JQuery<HTMLElement>) {
    $button.prop('disabled', false);
  }

  private disableButton($button: JQuery<HTMLElement>) {
    $button.prop('disabled', true);
  }

  private clearInputs() {
    this.$input.find('input').val('');
  }

  private disableInputs() {
    this.$input.find('input').prop('disabled', true);
  }

  private hasValidInputs() {
    // @ts-ignore
    $(this.$connectionRequestForm).parsley().validate();
    // @ts-ignore
    return $(this.$connectionRequestForm).parsley().isValid();
  }

  private disableParsleyValidation() {
    // @ts-ignore
    $(this.$connectionRequestForm).parsley().disable();
  }

  private hasConnectionRequests() {
    return this.$collectionList.find('.collection-entry').length > 0;
  }
  /**
   * Verify the institution value of the entry is unique.
   */
  private isUnique(newElement: JQuery<HTMLElement>): boolean {
    let isUnique = true;
    const newInstitutionValue = newElement.find('input').first().val();

    this.$collectionList.find('li').each((_index: number, el: HTMLElement) => {
      const $el = $(el);
      const existingValue = $el.find('input').first().val();

      if (existingValue === newInstitutionValue) {
        isUnique = false;
      }
    });

    return isUnique;
  }
}

export function load() {

  // Exclude the ConnectionRequest collection widget from being loaded
  const $widgets = $('form #connection_request_container_connectionRequests');
  if ($widgets.length > 0) {

    $widgets.each((_index: number, el: HTMLElement) => {
      const collectionWidget = new CollectionWidget($(el));
      collectionWidget.registerEventHandlers();
    });
  }
}

$(document).ready(load);
