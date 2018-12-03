import * as $ from 'jquery';

class CollectionWidget {
  private $collectionWidget: JQuery;
  private $collectionList: JQuery;
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
    this.$collectionList = this.$collectionWidget.find('ul');
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

    const handleAddClick = () => {
      this.addCollectionEntry();
    };
    $addEntryButton.on('click', handleAddClick);
  }

  /**
   * Add new collection entry with new id
   */
  private addCollectionEntry() {
    const collectionEntry = $('<li class="collection-entry"></li>');
    const $removeEntryButton = $('<button type="button" class="button-small remove_collection_entry"><i class="fa fa-trash"></i></button>');

    const handleRemoveClick = (el: JQuery.Event) => {
      this.removeCollectionEntry(el);
    };
    $removeEntryButton.on('click', handleRemoveClick);

    collectionEntry.append(this.createNewCollectionEntry());
    collectionEntry.append($removeEntryButton);
    this.$collectionList.append(collectionEntry);
    this.index += 1;
  }

  /**
   * Remove the collection entry from the list
   * @param el
   */
  private removeCollectionEntry(el: JQuery.Event) {
    const element = $(el.target);

    element.closest('.collection-entry').remove();
  }

  /**
   * Create new collection entry with unique name
   */
  private createNewCollectionEntry(): JQuery<HTMLElement> {
    const input = this.prototype.replace(/__name__/g, this.index.toString());
    const $input = $(input);
    $input.val(this.$input.val() as string);
    $input.prop('readonly', true);
    this.$input.val('');
    return $input;
  }
}

export function loadEntityOidcForm() {
  const $widgets = $('form .collection-widget');
  if ($widgets.length > 0) {

    $widgets.each(() => {
      const collectionWidget = new CollectionWidget($(this));
      collectionWidget.registerEventHandlers();
    });
  }
}

$(document).ready(loadEntityOidcForm);
