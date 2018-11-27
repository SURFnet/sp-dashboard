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
                    legend: {
                      info: {
                        label: "Unstarted",
                        color: "#d1d2d6"
                      },
                      'in-progress': {
                        label: "In progress",
                        color: "#f6aa61"
                      },
                      success: {
                        label: "Done",
                        color: "#67a979",
                      },
                    },
                    percentage:	80,
                }
            };
            e(result);
        }
    }
};



describe('validate donut status graph', function() {

    let stateHtml = `
        <div id="service-states">
            <div class="service-status-container"> <div class="service-status-graph" data-service-id="2"></div></div>
            <div class="service-status-container"> <div class="service-status-graph" data-service-id="1"></div></div>
        </div>`;

    it('should generate content based on the api call', function() {
        document.body.innerHTML = stateHtml;
        loadServiceStatus();

        let expected = `
            <div class="service-status-container"> <div class="service-status-graph" data-service-id="2"><div class="service-status-canvas"><div style="display: block;" class="chartjs-render-monitor"></div></div><div class="service-status-legend"><div class="legend-item" style="background-color: rgb(209, 210, 214);"></div><div class="legend-item" style="background-color: rgb(246, 170, 97);"></div><div class="legend-item" style="background-color: rgb(103, 169, 121);"></div></div><div class="service-status-percentage"></div></div></div>
            <div class="service-status-container"> <div class="service-status-graph" data-service-id="1"><div class="service-status-canvas"><div style="display: block;" class="chartjs-render-monitor"></div></div><div class="service-status-legend"><div class="legend-item" style="background-color: rgb(209, 210, 214);"></div><div class="legend-item" style="background-color: rgb(246, 170, 97);"></div><div class="legend-item" style="background-color: rgb(103, 169, 121);"></div></div><div class="service-status-percentage"></div></div></div>
        `;

        let actual = $('#service-states').html();

        expect(actual).toBe(expected);
    });
});