import { loadEntityOidcForm } from "./collection_widget";

import * as $ from "jquery";

jest
  .dontMock('fs')
  .dontMock('jquery');

describe('validate collection widget', function() {

  let stateHtml = `
        <form>
        <div class="collection-widget" data-prototype="<input type=&quot;text&quot; id=&quot;dashboard_bundle_entity_type_metadata_redirectUris___name__&quot; name=&quot;dashboard_bundle_entity_type[metadata][redirectUris][__name__]&quot;  />">
        <ul class="collection-list"></ul>
        </div>
        </form>`;

  it('should add a input field based on prototype', function() {
    document.body.innerHTML = stateHtml;
    loadEntityOidcForm();

    let expected = `
        <ul class="collection-list"></ul>
        <div class="collection-entry"><input type="text"><button type="button" class="button-small blue add_collection_entry"><i class="fa fa-plus"></i></button></div>`;

    let actual = $('.collection-widget').html();

    expect(actual).toBe(expected);
  });

  it('should add a field on click', function() {
    document.body.innerHTML = stateHtml;
    loadEntityOidcForm();

    $('.add_collection_entry').click();

    let expected = `
        <ul class=\"collection-list\"><li class=\"collection-entry\"><input type=\"text\" id=\"dashboard_bundle_entity_type_metadata_redirectUris_0\" name=\"dashboard_bundle_entity_type[metadata][redirectUris][0]\" readonly=\"\"><button type=\"button\" class=\"button-small remove_collection_entry\"><i class=\"fa fa-trash\"></i></button></li></ul>
        <div class="collection-entry"><input type="text"><button type="button" class="button-small blue add_collection_entry"><i class="fa fa-plus"></i></button></div>`;

    let actual = $('.collection-widget').html();

    expect(actual).toBe(expected);
  });

  it('should remove a field on click', function() {
    document.body.innerHTML = stateHtml;
    loadEntityOidcForm();

    let expected = `
        <ul class="collection-list"></ul>
        <div class="collection-entry"><input type="text"><button type="button" class="button-small blue add_collection_entry"><i class="fa fa-plus"></i></button></div>`;

    // add input
    $('.add_collection_entry').click();

    // remove input
    $('.remove_collection_entry').click();

    let actual = $('.collection-widget').html();

    expect(actual).toBe(expected);
  });

});