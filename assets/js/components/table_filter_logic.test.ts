/**
 * @jest-environment jsdom
 */

import { filterRow } from './table_filter_logic';

describe('filterRow', () => {
  let row: any;

  beforeEach(() => {
    document.body.innerHTML = ''; // Clear the DOM
    row = document.createElement('tr');
    row.style.display = 'table-row';
    document.body.appendChild(row);
  });

  afterEach(() => {
    document.body.innerHTML = '';
  });

  describe('when row has checked inputs', () => {
    beforeEach(() => {
      const input = document.createElement('input');
      input.type = 'checkbox';
      input.checked = true;
      row.appendChild(input);
    });

    it('should always display the row regardless of search term', () => {
      filterRow(row, 'nonexistent');
      expect(row.style.display).toBe('table-row');
    });
  });

  describe('when row contains th elements', () => {
    beforeEach(() => {
      const th = document.createElement('th');
      th.textContent = 'header content';
      row.appendChild(th);
    });

    it('should always display the row regardless of search term', () => {
      filterRow(row, 'nonexistent');
      expect(row.style.display).toBe('table-row');
    });
  });

  describe('when filtering regular content', () => {
    beforeEach(() => {
      row.textContent = 'Test Row Content';
    });

    it('should show row when search term matches content', () => {
      filterRow(row, 'test');
      expect(row.style.display).toBe('table-row');
    });

    it('should hide row when search term does not match content', () => {
      filterRow(row, 'nonexistent');
      expect(row.style.display).toBe('none');
    });

    it('should be case insensitive', () => {
      filterRow(row, 'TEST');
      expect(row.style.display).toBe('table-row');
    });

    it('should cache lowercase content after first call', () => {
      filterRow(row, 'test');
      const cachedContent = row.lowerTextContent;
      filterRow(row, 'row');
      expect(row.lowerTextContent).toBe(cachedContent);
    });
  });

  describe('edge cases', () => {
    beforeEach(() => {
      row.textContent = 'Test Content';
    });

    it('should handle empty search string', () => {
      filterRow(row, '');
      expect(row.style.display).toBe('table-row');
    });

    it('should handle special characters in search', () => {
      row.textContent = 'Test (Content)';
      filterRow(row, '(content)');
      expect(row.style.display).toBe('table-row');
    });

    it('should handle multiple consecutive calls with different search terms', () => {
      filterRow(row, 'test');
      expect(row.style.display).toBe('table-row');
      filterRow(row, 'nonexistent');
      expect(row.style.display).toBe('none');
      filterRow(row, 'content');
      expect(row.style.display).toBe('table-row');
    });
  });
});