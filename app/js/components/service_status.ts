import * as Chart from 'chart.js';
import { ChartTooltipItem } from 'chart.js';
import * as $ from 'jquery';

interface StatesDictionary {
  [key: string]: string;
}

interface EntityJson {
  name: string;
  environment: string;
  link: string;
}

interface StatesResponseJson {
  service: {
    entities: EntityJson[];
    link: string;
    name: string;
    states: StatesDictionary;
    labels: StatesDictionary;
    tooltips: StatesDictionary;
  };
}

export class ServiceStatus {
  private stateColors: StatesDictionary;
  private elementContainer: HTMLElement;
  private elementTitle: HTMLElement;
  private elementListTest: HTMLElement;
  private elementListProd: HTMLElement;
  private canvas: HTMLCanvasElement;
  private serviceId: string;
  private chart: Chart|undefined;

  constructor(elementContainer: HTMLElement, serviceId: string) {
    this.elementContainer = elementContainer;
    this.serviceId = serviceId;
    this.stateColors = {};
    this.chart = undefined;
    this.initStateColors();
    this.canvas = document.createElement('canvas') as HTMLCanvasElement;
    this.elementTitle = document.createElement('div') as HTMLElement;
    this.elementListTest = document.createElement('div') as HTMLElement;
    this.elementListProd = document.createElement('div') as HTMLElement;
    this.initCanvas();
  }

  /**
   * Init the eventhandlers on the elements
   */
  public registerEventHandlers() {
    this.fetchState();
  }

  /**
   * Check if loaded
   */
  public isLoaded() {
    return this.chart !== undefined;
  }

  private fetchState() {
    $.ajax({
      url: `/api/service/status/${this.serviceId}`,
      context: document.body,
    }).done((e) => {
      const statesResponse = e as StatesResponseJson;
      this.drawTitle(statesResponse);
      this.drawChart(statesResponse);
      this.drawEntities(statesResponse);
    });
  }

  private drawChart(states: StatesResponseJson) {
    const labels: string[] = [];
    const tooltips: string[] = [];
    const data: number[] = [];
    const backgroundColor: string[] = [];

    const ratio = 100 / Object.keys(states.service.states).length;
    for (const key in states.service.states) {
      if (!states.service.states.hasOwnProperty(key)) {
        continue;
      }

      const state = states.service.states[key];

      data.push(ratio);
      labels.push(states.service.labels[key]);
      backgroundColor.push(this.stateColors[state]);
      tooltips.push(states.service.tooltips[key]);
    }

    const options: Chart.ChartConfiguration = {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{
          data,
          backgroundColor,
        }],
      },
      options: {
        cutoutPercentage: 85,
        responsive: true,
        legend: {
          position: 'right',
          labels: {
            fontSize: 14,
          },
        },
        title: {
          display: false,
        },
        animation: {
          animateScale: true,
          animateRotate: true,
        },
        tooltips: {
          callbacks: {
            label: (tooltipItem: ChartTooltipItem) => {
              if (tooltipItem.index === undefined) {
                return '';
              }
              return tooltips[tooltipItem.index];
            },
          },
        },
      },
    };

    this.chart = new Chart(this.canvas, options);
  }

  private drawEntities(states: StatesResponseJson) {
    for (const id in states.service.entities) {
      if (!states.service.entities.hasOwnProperty(id)) {
        continue;
      }

      const entity = states.service.entities[id];

      const link = document.createElement('a') as HTMLAnchorElement;
      link.href = entity.link;
      link.innerHTML = entity.name;
      link.classList.add('service-status-entity-link');

      if (entity.environment === 'prod') {
        if (this.elementListProd.innerText === '') {
          this.elementListProd.innerText = 'Prod';
        }
        this.elementListProd.appendChild(link);
      } else if (entity.environment === 'test') {
        if (this.elementListTest.innerText === '') {
          this.elementListTest.innerText = 'Test';
        }
        this.elementListTest.appendChild(link);
      }
    }
  }

  private drawTitle(states: StatesResponseJson) {
    const link = document.createElement('a') as HTMLAnchorElement;
    link.href = states.service.link;
    link.innerHTML = states.service.name;

    this.elementTitle.appendChild(link);
  }

  private initStateColors() {
    this.stateColors.danger = '#d73232';
    this.stateColors.success = '#67a979';
    this.stateColors.info = '#f6aa61';
  }

  private initCanvas() {
    // title
    this.elementTitle.classList.add('service-status-title');
    this.elementContainer.appendChild(this.elementTitle);

    // canvas
    const container = document.createElement('div');
    container.classList.add('service-status-canvas');

    container.appendChild(this.canvas);
    this.elementContainer.appendChild(container);

    // entitylist
    this.elementListTest.classList.add('service-status-entities');
    this.elementContainer.appendChild(this.elementListTest);
    this.elementListProd.classList.add('service-status-entities');
    this.elementContainer.appendChild(this.elementListProd);
  }
}

export function loadServiceStatus() {
  if ($('#service-states').length > 0) {

    const elements = document.getElementsByClassName('service-status-container');
    Array.prototype.forEach.call(elements,  (el: HTMLCanvasElement) => {

      const serviceId = el.dataset.serviceId;
      if (serviceId === undefined) {
        return;
      }

      const serviceStatus = new ServiceStatus(
        el,
        serviceId,
      );
      serviceStatus.registerEventHandlers();
    });
  }
}

$(document).ready(loadServiceStatus);
