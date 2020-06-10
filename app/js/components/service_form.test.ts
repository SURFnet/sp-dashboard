import { loadServiceForm } from "./service_form";
import * as $ from "jquery";

jest
    .dontMock('fs')
    .dontMock('jquery');

(<any>window).$ = $;

describe('validate visibility toggling of service status fields on the service edit form', function() {

    let editFormHtml = require('fs').readFileSync('./app/js/components/mock/service_edit_form.html').toString();

    it('hides the contract signed fields when the service type is institution', function() {
        document.body.innerHTML = editFormHtml;
        loadServiceForm();

        // On page load the type of service is set to 'Not an institute' so the 'Contract signed' radiogroup should
        // be visible
        expect($('.representative-signed-container').parent().hasClass('hidden')).toBeTruthy();
        expect($('.institution-id-container').parent().hasClass('hidden')).toBeTruthy();
        expect($('.contract-signed-container').parent().hasClass('hidden')).toBeFalsy();

        // When institute is selected the 'SURFconext representative approved' radiogroup should be visible
        $('.contract-signed-toggle :radio[value="institute"]').trigger('click');
        expect($('.contract-signed-container').parent().hasClass('hidden')).toBeTruthy();
        expect($('.representative-signed-container').parent().hasClass('hidden')).toBeFalsy();
        expect($('.institution-id-container').parent().hasClass('hidden')).toBeFalsy();

        // When institute is selected the 'Contract signed' radiogroup should be visible
        $('.contract-signed-toggle :radio[value="non-institute"]').trigger('click');
        expect($('.representative-signed-container').parent().hasClass('hidden')).toBeTruthy();
        expect($('.institution-id-container').parent().hasClass('hidden')).toBeTruthy();
        expect($('.contract-signed-container').parent().hasClass('hidden')).toBeFalsy();
    });

});

describe('validate visibility toggling of service status fields on the service create form', function() {

    let createFormHtml = require('fs').readFileSync('./app/js/components/mock/service_create_form.html').toString();

    it('hides the contract signed fields when the service type is institution', function() {
        document.body.innerHTML = createFormHtml;
        loadServiceForm();

        // On page load the type of service is set to 'institute' so the 'Contract signed' radiogroup should
        // be visible
        expect($('.contract-signed-container').parent().hasClass('hidden')).toBeTruthy();
        expect($('.institution-id-container').parent().hasClass('hidden')).toBeFalsy();
        expect($('.representative-signed-container').parent().hasClass('hidden')).toBeFalsy();

        // When institute is selected the 'SURFconext representative approved' radiogroup should be visible
        $('.contract-signed-toggle :radio[value="institute"]').trigger('click');
        expect($('.contract-signed-container').parent().hasClass('hidden')).toBeTruthy();
        expect($('.representative-signed-container').parent().hasClass('hidden')).toBeFalsy();
        expect($('.institution-id-container').parent().hasClass('hidden')).toBeFalsy();

        // When non-institute is selected the 'Contract signed' radiogroup should be visible
        $('.contract-signed-toggle :radio[value="non-institute"]').trigger('click');
        expect($('.representative-signed-container').parent().hasClass('hidden')).toBeTruthy();
        expect($('.institution-id-container').parent().hasClass('hidden')).toBeTruthy();
        expect($('.contract-signed-container').parent().hasClass('hidden')).toBeFalsy();

    });

});
