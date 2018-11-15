import { loadServiceStatus } from "./service_status";

import * as $ from "jquery";

jest
    .dontMock('fs')
    .dontMock('jquery');

(<any>window).$ = $;

(<any>window).$.ajax = function() {
    return {
        done: function(e: any) {
            let result = {
                service: {
                    link: '/service/edit/1',
                    name: 'service name',
                    entities: [{
                        name: 'prod entity name',
                        link: '/entity/edit/2',
                        environment: 'production',

                    },
                    {
                        name: 'test entity name',
                        link: '/entity/edit/1',
                        environment: 'test',

                    }],
                    states: {
                        "intake-conducted": "success",
                        "entity-on-test":"success",
                        "representative-approved":"success",
                        "privacy-questions":"success",
                        "production-connection":"info"
                    },
                    labels: {
                        "intake-conducted":"Intake done",
                        "entity-on-test":"Entity on test",
                        "representative-approved":"Approved by representative",
                        "contract-signed":"Contract signed",
                        "privacy-questions":"Privacy questions answered",
                        "production-connection":"Production connection aquired"
                    },
                    tooltips: {
                        "intake-conducted":"Is the intake done",
                        "entity-on-test":"Is there an entity on the test environment",
                        "representative-approved":"Is the service approved by a representative",
                        "contract-signed":"Is a contract signed",
                        "privacy-questions":"Are the privacy questions answered",
                        "production-connection":"Is a production connection active"
                    },
                }
            };
            e(result);
        }
    }
};



describe('validate donut status graph', function() {

    let stateHtml = `
        <div id="service-states">
            <div class="service-status-container" data-service-id="2"></div>
            <div class="service-status-container" data-service-id="1"></div>
        </div>`;

    it('should generate content based on the api call', function() {
        document.body.innerHTML = stateHtml;
        loadServiceStatus();

        let expected = `
            <div class="service-status-container" data-service-id="2"><div class="service-status-title"><a href="/service/edit/1">service name</a></div><div class="service-status-canvas"><div style="display: block;" class="chartjs-render-monitor"></div></div><div class="service-status-entities"><a href="/entity/edit/1" class="service-status-entity-link">test entity name</a></div><div class="service-status-entities"></div></div>
            <div class="service-status-container" data-service-id="1"><div class="service-status-title"><a href="/service/edit/1">service name</a></div><div class="service-status-canvas"><div style="display: block;" class="chartjs-render-monitor"></div></div><div class="service-status-entities"><a href="/entity/edit/1" class="service-status-entity-link">test entity name</a></div><div class="service-status-entities"></div></div>
        `;

        let actual = $('#service-states').html();

        expect(actual).toBe(expected);
    });
});