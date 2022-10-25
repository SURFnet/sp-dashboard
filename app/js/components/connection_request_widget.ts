import * as $ from 'jquery';

class CollectionWidget {
  private $collectionWidget: JQuery;
  private $collectionList: JQuery<any>;
  private $input: JQuery;
  private prototype: string;
  private index: number =  0;
  private errorMessageSelector: string;
  private errorMessage: string;

  /**
   * @param $collectionWidget The collection widget
   */
  constructor(
    $collectionWidget: JQuery,
  ) {
    this.$collectionWidget = $collectionWidget;
    this.$collectionList = this.$collectionWidget.find('ul.collection-list');
    this.prototype = this.$collectionWidget.data('prototype');
    this.$input = $(this.prototype);
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

    this.$collectionWidget.append($collectionContainer);

    this.index = this.$collectionList.find('.collection-entry').length;

    this.$collectionList.find('.remove_collection_entry').each((_index: number, el: any) => {
      this.registerRemoveClickHandler($(el));
    });

    this.registerAddClickHandler($addEntryButton);
    this.registerBeforeSubmitHandler($addEntryButton);
    this.registerPreventFormSubmitHandler($input);
  }

  /**
   * Add new collection entry with new id
   */
  private addCollectionEntry() {

    const newElement = this.createNewCollectionEntry();
    if (!this.isUnique(newElement)) {
      this.$input.parent().addClass('error');
      if ($(this.errorMessageSelector).length === 0) {
        this.$input.parent().prepend(this.errorMessage);
      }
      return;
    }

    this.$input.val('');
    this.$input.parent().removeClass('error');
    $(this.errorMessageSelector).remove();

    const collectionEntry = $('<li class="collection-entry"></li>');
    const $removeEntryButton = $('<button type="button" class="button-small remove_collection_entry"><i class="fa fa-trash"></i></button>');

    this.registerRemoveClickHandler($removeEntryButton);

    collectionEntry.append(newElement);
    collectionEntry.append($removeEntryButton);
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
    const $outputElement = $('<div class="read-only-view">');
    // The value[Label|Input]Elements are used to match the input text with the new
    // element that is added to the read-only list of connection requests.
    const $valueLabelElements = this.$input.find('label:not(error)');
    const $valueInputElements = this.$input.find('input');
    $fields.each((_index: number, el: HTMLElement) => {
      const $fieldValue = $valueInputElements.eq(_index).val();
      const $label = $valueLabelElements.eq(_index).text();
      const $el = $(el);
      $el.val($fieldValue as string);
      // The input fields should be in the output element, but they are hidden for the eye candy factor
      $el.hide();
      $outputElement.append(`<div class="item"><label>${$label}</label>${$fieldValue}</div>`);
      $outputElement.append($el);
      $outputElement.append('</div>');
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
    };
    $removeEntryButton.on('click', handleRemoveClick);
  }

  /**
   * Add click handler to add entry
   * @param $addEntryButton
   */
  private registerAddClickHandler($addEntryButton: JQuery<HTMLElement>) {
    const handleAddClick = () => {
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
