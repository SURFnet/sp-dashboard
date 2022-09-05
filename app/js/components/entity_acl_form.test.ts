/**
 * @jest-environment jsdom
 */

import { loadEntityAclForm } from "./entity_acl_form";
import * as $ from "jquery";

jest
  .dontMock('fs')
  .dontMock('jquery');

(<any>window).$ = $;

describe('validate visibility toggling of acl list on the entity acl form', function() {

  let aclFormHtml = require('fs').readFileSync('./app/js/components/mock/entity_acl_form.html').toString();

  it('hides the acl container when select all is checked', function() {
    document.body.innerHTML = aclFormHtml;

    // Before page load the acl container should be hidden
    expect($('#acl-container').hasClass('hidden')).toBeTruthy();

    loadEntityAclForm();

    // On page load the acl container should be visible
    expect($('#acl-container').hasClass('hidden')).toBeFalsy();

    // On select-all clicked the acl container should become hidden
    $('#acl_entity_selectAll').trigger('click');
    expect($('#acl-container').hasClass('hidden')).toBeTruthy();

    // On select-all clicked again the acl container should be visible
    $('#acl_entity_selectAll').trigger('click');
    expect($('#acl-container').hasClass('hidden')).toBeFalsy();
  });

});
