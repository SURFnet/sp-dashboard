import { filterRow } from './table_filter_logic';

(function () {
  "use strict";


  function dquery(selector: string) {
    return Array.prototype.slice.call(document.querySelectorAll(selector));
  }

  function onInputEvent(e: any) {
    const input = e.target;
    const search = input.value.toLocaleLowerCase();

    const tableContainer = input.closest('.input-group').parentElement;
    const table = tableContainer.querySelector('table');
    const rows = table.querySelectorAll('tbody tr');

    [].forEach.call(rows, function(row: any) {
      filterRow(row, search);
    });
  }

  function init() {
    const inputs = dquery("input[data-table]");
    [].forEach.call(inputs, function (input: HTMLInputElement) {
      input.oninput = onInputEvent;
      if (input.value !== "") input.oninput(({ target: input } as unknown) as Event);
    });
  }

  init();
})();