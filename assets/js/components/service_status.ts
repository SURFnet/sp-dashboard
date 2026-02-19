
import {
  Chart,
  ChartConfiguration,
  DoughnutController,
  ArcElement,
  Tooltip,
  Legend,
  TooltipItem
} from 'chart.js';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import * as $ from 'jquery';

// Register required Chart.js components for tree-shaking
Chart.register(DoughnutController, ArcElement, Tooltip, Legend, ChartDataLabels);

interface StatesDictionary {
  [key: string]: string;
}

interface LegendJson {
  label: string;
  color: string;
}

interface StatesResponseJson {
  service: {
    states: StatesDictionary;
    labels: StatesDictionary;
    tooltips: StatesDictionary;
    legend: LegendJson[];
    percentage: number;
  };
}

export class ServiceStatus {
  private stateColors: StatesDictionary;
  private elementContainer: HTMLElement;
  private canvas: HTMLCanvasElement;
  private serviceId: string;
  private chart: Chart | undefined;

  constructor(elementContainer: HTMLElement, serviceId: string) {
    this.elementContainer = elementContainer;
    this.serviceId = serviceId;
    this.stateColors = {};
    this.chart = undefined;
    this.canvas = document.createElement('canvas') as HTMLCanvasElement;
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
      this.initStateColors(statesResponse);
      this.drawChart(statesResponse);
      this.drawLegend(statesResponse);
      this.drawPercentage(statesResponse);

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

    const customTooltip = function (context: {chart: Chart, tooltip: any}) {
      const tooltipModel = context.tooltip;
      
      // Tooltip Element
      let tooltipEl = document.getElementById('chartjs-tooltip');

      // Create element on first render
      if (!tooltipEl) {
        tooltipEl = document.createElement('div');
        tooltipEl.id = 'chartjs-tooltip';
        tooltipEl.innerHTML = '';
        document.body.appendChild(tooltipEl);
      }

      // Set caret Position
      tooltipEl.classList.remove('above', 'below', 'no-transform');
      if (tooltipModel.yAlign) {
        tooltipEl.classList.add(tooltipModel.yAlign);
      } else {
        tooltipEl.classList.add('no-transform');
      }

      // Set Text
      if (tooltipModel.body) {
        const titleLines = tooltipModel.title || [];
        const bodyLines = tooltipModel.body.map((bodyItem: any) => {
          return bodyItem.lines;
        });

        let innerHtml = '<h4>';

        titleLines.forEach((title: any) => {
          innerHtml += `${title} <br>`;
        });
        innerHtml += '</h4>';

        bodyLines.forEach((body: any, _i: number) => {
          innerHtml += `${body} <br>`;
        });

        tooltipEl.innerHTML = innerHtml;
      }

      // Use context.chart instead of this._chart
      const position = context.chart.canvas.getBoundingClientRect();

      // Display, position, and set styles for font
      tooltipEl.style.position = 'absolute';
      tooltipEl.style.left = `${position.left + window.pageXOffset + tooltipModel.caretX}px`;
      tooltipEl.style.top = `${position.top + window.pageYOffset + tooltipModel.caretY}px`;
    };

    const options: ChartConfiguration<'doughnut'> = {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{
          data,
          backgroundColor,
        }],
      },
      options: {
        cutout: '75%',
        responsive: true,
        aspectRatio: 1,
        layout: {
          padding: {
            left: 60,
            right: 60,
            top: 60,
            bottom: 60,
          },
        },
        plugins: {
          legend: {
            display: false,
          },
          title: {
            display: false,
          },
          tooltip: {
            // Disable the on-canvas tooltip
            enabled: false,
            mode: 'index',
            position: 'nearest',
            external: customTooltip,
            callbacks: {
              label: (tooltipItem: TooltipItem<'doughnut'>) => {
                return tooltips[tooltipItem.dataIndex];
              },
            },
          },
          datalabels: {
            backgroundColor: null,
            borderColor: 'none',
            display: true,
            font: {
              weight: 'bold',
            },
            formatter: (_value: any, context: any) => {
              return labels[context.dataIndex];
            },
            offset: 4,
            align: 'end',
            anchor: 'end',
            textAlign: 'center',
          },
        },
        animation: {
          animateScale: true,
          animateRotate: true,
        },
      },
    };

    this.chart = new Chart(this.canvas, options);
  }

  private drawLegend(states: StatesResponseJson) {
    // create legend container
    const legend = document.createElement('div');
    legend.classList.add('service-status-legend');

    // create legend items
    for (const id in states.service.legend) {
      if (!states.service.legend.hasOwnProperty(id)) {
        continue;
      }

      const current = states.service.legend[id];

      // add legend item
      const item = document.createElement('div');
      item.classList.add('legend-item');
      item.style.backgroundColor = current.color;
      item.innerText = current.label;

      legend.append(item);
    }

    this.elementContainer.appendChild(legend);
  }

  private drawPercentage(states: StatesResponseJson) {
    const percentage = document.createElement('div');
    percentage.classList.add('service-status-percentage');
    percentage.innerText = `${states.service.percentage}%`;

    this.elementContainer.appendChild(percentage);
  }

  private initStateColors(states: StatesResponseJson) {
    for (const id in states.service.legend) {
      if (!states.service.legend.hasOwnProperty(id)) {
        continue;
      }

      this.stateColors[id] = states.service.legend[id].color;
    }
  }

  private initCanvas() {
    // canvas
    const container = document.createElement('div');
    container.classList.add('service-status-canvas');

    container.appendChild(this.canvas);
    this.elementContainer.appendChild(container);
  }
}

export function loadServiceStatus() {
  if ($('.service-status-container').length > 0) {

    const elements = document.getElementsByClassName('service-status-graph');
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
