export function filterRow(row: any, search: string): void {
    if (row.lowerTextContent === undefined) {
      row.lowerTextContent = row.textContent.toLocaleLowerCase();
    }
  
    if (row.querySelectorAll('input:checked').length > 0) {
      row.style.display = 'table-row';
      return;
    }

    if (row.querySelectorAll('th').length > 0) {
      row.style.display = 'table-row';
      return;
    }

    row.style.display = row.lowerTextContent.indexOf(search.toLocaleLowerCase()) === -1 ? "none" : "table-row";
  }