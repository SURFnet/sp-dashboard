/**
 * @jest-environment jsdom
 */

import { loadEntityOidcForm } from "./collection_widget";

import * as $ from "jquery";
import * as jQuery from "jquery";

jest
  .dontMock('fs')
  .dontMock('jquery');

describe('validate collection widget', function() {

  let stateHtml = `
        <form action="/" method="POST">
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

  it('should not allow duplicate entries', function() {
    document.body.innerHTML = stateHtml;
    loadEntityOidcForm();
    // Add a redirect uri
    $('.collection-entry input[type="text"]').val('https://redirect-url.org/redirect-uri');
    // Add a new entry
    $('.add_collection_entry').click();
    // Add another redirect uri
    $('.collection-entry input[type="text"]').last().val('https://redirect-url.org/redirect-uri-2');
    // And add it
    $('.add_collection_entry').click();
    // Attempt to add an existing
    $('.collection-entry input[type="text"]').last().val('https://redirect-url.org/redirect-uri');
    // Should yield an error
    $('.add_collection_entry').click();

    let expected= `<li class="collection-entry"><input type="text" id="dashboard_bundle_entity_type_metadata_redirectUris_0" name="dashboard_bundle_entity_type[metadata][redirectUris][0]" readonly=""><button type="button" class="button-small remove_collection_entry"><i class="fa fa-trash"></i></button></li><li class="collection-entry"><input type="text" id="dashboard_bundle_entity_type_metadata_redirectUris_1" name="dashboard_bundle_entity_type[metadata][redirectUris][1]" readonly=""><button type="button" class="button-small remove_collection_entry"><i class="fa fa-trash"></i></button></li>`;
    let actual = $('.collection-list').html();
    expect(actual).toBe(expected);
  });


  it('should not submit form on enter', function () {
    document.body.innerHTML = stateHtml;
    loadEntityOidcForm();

    let actual = $('.collection-entry input[type="text"]').length;
    expect(actual).toBe(1);

    // press enter
    let e = jQuery.Event('keydown', { key: 'Enter' });
    $('.collection-entry input[type="text"]').last().trigger(e);

    actual = $('.collection-entry input[type="text"]').length;
    expect(actual).toBe(2);

  });

});
