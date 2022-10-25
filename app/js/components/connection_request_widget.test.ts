/**
 * @jest-environment jsdom
 */

import { load } from "./connection_request_widget";

import * as $ from "jquery";

jest
  .dontMock('fs')
  .dontMock('jquery');

describe('validate connection request widget', function() {

  const stateHtml = `
        <form action="/" method="POST">
        <div class="collection-widget" id="connection_request_container_connectionRequests" name="connection_request_container[connectionRequests]" required="required" class="connection-request-container" data-prototype="            
    &lt;div id=&quot;connection_request_container_connectionRequests___name__&quot; class=&quot;connection-request&quot;&gt;&lt;div class=&quot;form-row&quot;&gt;&lt;label for=&quot;connection_request_container_connectionRequests___name___institution&quot; class=&quot;required&quot;&gt;Institution&lt;/label&gt;&lt;p class=&quot;parsley-errors&quot;&gt;&lt;/p&gt;            
    &lt;input type=&quot;text&quot; id=&quot;connection_request_container_connectionRequests___name___institution&quot; name=&quot;connection_request_container[connectionRequests][__name__][institution]&quot; required=&quot;required&quot; class=&quot;add-form-input&quot;  /&gt;&lt;/div&gt;&lt;div class=&quot;form-row&quot;&gt;&lt;label for=&quot;connection_request_container_connectionRequests___name___name&quot;&gt;Name&lt;/label&gt;&lt;p class=&quot;parsley-errors&quot;&gt;&lt;/p&gt;            
    &lt;input type=&quot;text&quot; id=&quot;connection_request_container_connectionRequests___name___name&quot; name=&quot;connection_request_container[connectionRequests][__name__][name]&quot; class=&quot;add-form-input&quot;  /&gt;&lt;/div&gt;&lt;div class=&quot;form-row&quot;&gt;&lt;label for=&quot;connection_request_container_connectionRequests___name___email&quot; class=&quot;required&quot;&gt;Email&lt;/label&gt;&lt;p class=&quot;parsley-errors&quot;&gt;&lt;/p&gt;            
    &lt;input type=&quot;email&quot; id=&quot;connection_request_container_connectionRequests___name___email&quot; name=&quot;connection_request_container[connectionRequests][__name__][email]&quot; required=&quot;required&quot; class=&quot;add-form-input&quot;  /&gt;&lt;/div&gt;&lt;/div&gt;"> 
        <ul class="collection-list"></ul>
    </div>
        </form>`;

  it('should three input field based on prototype', function() {
    document.body.innerHTML = stateHtml;
    load();

    const expected = ` 
        <ul class="collection-list"></ul>
    <div class="collection-entry base-form"><div class="connection-request"><div class="form-row"><label for="connection_request_container_connectionRequests___name___institution" class="required">Institution</label><p class="parsley-errors"></p>            
    <input type="text" id="connection_request_container_connectionRequests___name___institution" name="connection_request_container[connectionRequests][__name__][institution]" required="required" class="add-form-input"></div><div class="form-row"><label for="connection_request_container_connectionRequests___name___name">Name</label><p class="parsley-errors"></p>            
    <input type="text" id="connection_request_container_connectionRequests___name___name" name="connection_request_container[connectionRequests][__name__][name]" class="add-form-input"></div><div class="form-row"><label for="connection_request_container_connectionRequests___name___email" class="required">Email</label><p class="parsley-errors"></p>            
    <input type="email" id="connection_request_container_connectionRequests___name___email" name="connection_request_container[connectionRequests][__name__][email]" required="required" class="add-form-input"></div></div><button type="button" class="button-small blue add_collection_entry"><i class="fa fa-plus"></i></button></div>`;

    let actual = $('.collection-widget').html();

    expect(actual).toBe(expected);
  });

  it('should add the three fields on click', function() {
    document.body.innerHTML = stateHtml;
    load();

    $('.add_collection_entry').click();

    let expected = ` 
        <ul class="collection-list"><li class="collection-entry"><div class="read-only-view"><div class="item"><label>Institution</label></div><input type="text" id="connection_request_container_connectionRequests_0_institution" name="connection_request_container[connectionRequests][0][institution]" required="required" class="add-form-input" style="display: none;"><div class="item"><label>Name</label></div><input type="text" id="connection_request_container_connectionRequests_0_name" name="connection_request_container[connectionRequests][0][name]" class="add-form-input" style="display: none;"><div class="item"><label>Email</label></div><input type="email" id="connection_request_container_connectionRequests_0_email" name="connection_request_container[connectionRequests][0][email]" required="required" class="add-form-input" style="display: none;"></div><button type="button" class="button-small remove_collection_entry"><i class="fa fa-trash"></i></button></li></ul>
    <div class="collection-entry base-form"><div class="connection-request"><div class="form-row"><label for="connection_request_container_connectionRequests___name___institution" class="required">Institution</label><p class="parsley-errors"></p>            
    <input type="text" id="connection_request_container_connectionRequests___name___institution" name="connection_request_container[connectionRequests][__name__][institution]" required="required" class="add-form-input"></div><div class="form-row"><label for="connection_request_container_connectionRequests___name___name">Name</label><p class="parsley-errors"></p>            
    <input type="text" id="connection_request_container_connectionRequests___name___name" name="connection_request_container[connectionRequests][__name__][name]" class="add-form-input"></div><div class="form-row"><label for="connection_request_container_connectionRequests___name___email" class="required">Email</label><p class="parsley-errors"></p>            
    <input type="email" id="connection_request_container_connectionRequests___name___email" name="connection_request_container[connectionRequests][__name__][email]" required="required" class="add-form-input"></div></div><button type="button" class="button-small blue add_collection_entry"><i class="fa fa-plus"></i></button></div>`;

    let actual = $('.collection-widget').html();

    expect(actual).toBe(expected);
  });

  it('should remove the fields on click', function() {
    document.body.innerHTML = stateHtml;
    load();

    const expected = ` 
        <ul class="collection-list"></ul>
    <div class="collection-entry base-form"><div class="connection-request"><div class="form-row"><label for="connection_request_container_connectionRequests___name___institution" class="required">Institution</label><p class="parsley-errors"></p>            
    <input type="text" id="connection_request_container_connectionRequests___name___institution" name="connection_request_container[connectionRequests][__name__][institution]" required="required" class="add-form-input"></div><div class="form-row"><label for="connection_request_container_connectionRequests___name___name">Name</label><p class="parsley-errors"></p>            
    <input type="text" id="connection_request_container_connectionRequests___name___name" name="connection_request_container[connectionRequests][__name__][name]" class="add-form-input"></div><div class="form-row"><label for="connection_request_container_connectionRequests___name___email" class="required">Email</label><p class="parsley-errors"></p>            
    <input type="email" id="connection_request_container_connectionRequests___name___email" name="connection_request_container[connectionRequests][__name__][email]" required="required" class="add-form-input"></div></div><button type="button" class="button-small blue add_collection_entry"><i class="fa fa-plus"></i></button></div>`;

    // add input
    $('.add_collection_entry').click();

    // remove input
    $('.remove_collection_entry').click();

    const actual = $('.collection-widget').html();

    expect(actual).toBe(expected);
  });

  it('should not allow duplicate entries', function() {
    document.body.innerHTML = stateHtml;
    load();
    // Add a connection request
    $('.connection_request_container[connectionRequests][__name__][institution]').val('Harderwijk University');
    $('.connection_request_container[connectionRequests][__name__][name]').val('My Name');
    $('.connection_request_container[connectionRequests][__name__][email]').val('foobar@example.com');
    // Add the new entry
    $('.add_collection_entry').click();
    // Add another request
    $('.connection_request_container[connectionRequests][__name__][institution]').val('Harderwijk seals veterinary');
    $('.connection_request_container[connectionRequests][__name__][name]').val('Lenie \'t Hart');
    $('.connection_request_container[connectionRequests][__name__][email]').val('lenie@example.com');
    // And add it
    $('.add_collection_entry').click();
    // Attempt to add an existing request (institution must be unique)_
    $('.connection_request_container[connectionRequests][__name__][institution]').val('Harderwijk University');
    $('.connection_request_container[connectionRequests][__name__][name]').val('My Name');
    $('.connection_request_container[connectionRequests][__name__][email]').val('foobar@example.com');
    // Should yield an error
    $('.add_collection_entry').click();

    const expected = `<li class="collection-entry"><div class="read-only-view"><div class="item"><label>Institution</label></div><input type="text" id="connection_request_container_connectionRequests_0_institution" name="connection_request_container[connectionRequests][0][institution]" required="required" class="add-form-input" style="display: none;"><div class="item"><label>Name</label></div><input type="text" id="connection_request_container_connectionRequests_0_name" name="connection_request_container[connectionRequests][0][name]" class="add-form-input" style="display: none;"><div class="item"><label>Email</label></div><input type="email" id="connection_request_container_connectionRequests_0_email" name="connection_request_container[connectionRequests][0][email]" required="required" class="add-form-input" style="display: none;"></div><button type="button" class="button-small remove_collection_entry"><i class="fa fa-trash"></i></button></li>`;
    const actual = $('.collection-list').html();
    expect(actual).toBe(expected);

    // This error is displayed in the add form
    const addFormHtmlElement = $('.base-form');
    expect(addFormHtmlElement.hasClass('error')).toBeTruthy();
  });
});
