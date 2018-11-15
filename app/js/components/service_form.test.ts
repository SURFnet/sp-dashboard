import { loadServiceForm } from "./service_form";
import * as $ from "jquery";

jest
    .dontMock('fs')
    .dontMock('jquery');

(<any>window).$ = $;

describe('validate visibility toggling of service status fields on the service edit form', function() {

    let editFormHtml = require('fs').readFileSync('./app/js/components/mock/service_edit_form.html').toString();

    it('hides the privacy question answered fields when the privacy questions checkbox is disabled', function() {
        document.body.innerHTML = editFormHtml;
        loadServiceForm();

         expect($('.privacy-questions-container').parent().hasClass('hidden')).toBeTruthy();
        $('.privacy-questions-toggle').click();
        expect($('.privacy-questions-container').parent().hasClass('hidden')).toBeFalsy();
        $('.privacy-questions-toggle').click();
        expect($('.privacy-questions-container').parent().hasClass('hidden')).toBeTruthy();
        $('.privacy-questions-toggle').click();
        expect($('.privacy-questions-container').parent().hasClass('hidden')).toBeFalsy();
    });

    it('hides the contract signed fields when the service type is institution', function() {
        document.body.innerHTML = editFormHtml;
        loadServiceForm();

        expect($('.contract-signed-container').parent().hasClass('hidden')).toBeTruthy();
        $('.contract-signed-toggle :radio[value="institute"]').trigger('click');
        expect($('.contract-signed-container').parent().hasClass('hidden')).toBeFalsy();
        $('.contract-signed-toggle :radio[value="non-institute"]').trigger('click');
        expect($('.contract-signed-container').parent().hasClass('hidden')).toBeTruthy();
        $('.contract-signed-toggle :radio[value="institute"]').trigger('click');
        expect($('.contract-signed-container').parent().hasClass('hidden')).toBeFalsy();
    });

});

describe('validate visibility toggling of service status fields on the service create form', function() {

    let createFormHtml = require('fs').readFileSync('./app/js/components/mock/service_create_form.html').toString();

    it('hides the privacy question answered fields when the privacy questions checkbox is disabled', function() {
        document.body.innerHTML = createFormHtml;
        loadServiceForm();

        expect($('.privacy-questions-container').parent().hasClass('hidden')).toBeFalsy();
        $('.privacy-questions-toggle').click();
        expect($('.privacy-questions-container').parent().hasClass('hidden')).toBeTruthy();
        $('.privacy-questions-toggle').click();
        expect($('.privacy-questions-container').parent().hasClass('hidden')).toBeFalsy();
        $('.privacy-questions-toggle').click();
        expect($('.privacy-questions-container').parent().hasClass('hidden')).toBeTruthy();

    });

    it('hides the contract signed fields when the service type is institution', function() {
        document.body.innerHTML = createFormHtml;
        loadServiceForm();

        expect($('.contract-signed-container').parent().hasClass('hidden')).toBeTruthy();
        $('.contract-signed-toggle :radio[value="institute"]').trigger('click');
        expect($('.contract-signed-container').parent().hasClass('hidden')).toBeFalsy();
        $('.contract-signed-toggle :radio[value="non-institute"]').trigger('click');
        expect($('.contract-signed-container').parent().hasClass('hidden')).toBeTruthy();
        $('.contract-signed-toggle :radio[value="institute"]').trigger('click');
        expect($('.contract-signed-container').parent().hasClass('hidden')).toBeFalsy();
    });

});
