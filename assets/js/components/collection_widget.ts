import * as $ from 'jquery';

class CollectionWidget {
  private $collectionWidget: JQuery;
  private $collectionList: JQuery<any>;
  private $input: JQuery;
  private prototype: string;
  private index: number =  0;

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
    const $collectionContainer = $('<div class="collection-entry"></div>');
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
    this.$collectionList.find('.edit_collection_entry').each((_index: number, el: any) => {
      this.registerEditClickHandler($(el));
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
      return;
    }

    this.$input.val('');
    this.$input.parent().removeClass('error');

    const collectionEntry = $('<li class="collection-entry"></li>');
    const $removeEntryButton = $('<button type="button" class="button-small remove_collection_entry"><i class="fa fa-trash"></i></button>');
    const $editEntryButton = $('<button type="button" class="button-small edit_collection_entry"><i class="fa fa-pencil"></i></button>');

    this.registerRemoveClickHandler($removeEntryButton);
    this.registerEditClickHandler($editEntryButton);

    collectionEntry.append(newElement);
    collectionEntry.append($removeEntryButton);
    collectionEntry.append($editEntryButton);
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
   * Remove the collection entry from the list
   * @param el
   */
  private editCollectionEntry(el: JQuery.TriggeredEvent) {
    // First make all entries readonly once again
    this.$collectionList.find('.edit_collection_entry').each((_index: number, el: any) => {
      $(el).closest('.collection-entry').children('input').prop('readonly', true);
    });
    const element = $(el.target);
    const target = element.closest('.collection-entry').children('input');
    // Then make targeted element editable
    target.removeAttr('readonly');

  }

  /**
   * Create new collection entry with unique name
   */
  private createNewCollectionEntry(): JQuery<HTMLElement> {
    const input = this.prototype.replace(/__name__/g, this.index.toString());
    const $input = $(input);
    $input.val(this.$input.val() as string);
    $input.prop('readonly', true);

    return $input;
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

  private registerEditClickHandler($editEntryButton: JQuery<HTMLElement>) {
    const handleRemoveClick = (el: JQuery.TriggeredEvent) => {
      this.editCollectionEntry(el);
    };
      $editEntryButton.on('click', handleRemoveClick);
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

  private isUnique(newElement: JQuery<HTMLElement>): boolean {
    let isUnique = true;
    this.$collectionList.find('li').toArray().forEach((value) => {
      const existingValue = $(value).find('input').val();
      if (existingValue === newElement.val()) {
        isUnique = false;
      }
    });

    return isUnique;
  }
}

export function loadEntityOidcForm() {
  // Exclude the ConnectionRequest collection widget from being loaded
  const $widgets = $('form .collection-widget:not(#connection_request_container_connectionRequests)');
  if ($widgets.length > 0) {

    $widgets.each((_index: number, el: HTMLElement) => {
      const collectionWidget = new CollectionWidget($(el));
      collectionWidget.registerEventHandlers();
    });
  }
}

$(document).ready(loadEntityOidcForm);
