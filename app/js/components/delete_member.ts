import * as $ from 'jquery';
import addFlashMessage from './addFlashMessage';

$(() => {
  const flashUl = document.querySelector('.teams__flashMessages') as HTMLUListElement;
  const deleteLinks = document.querySelectorAll('.teams__deleteMemberLink');

  if (!!flashUl && !!deleteLinks) {
    document.addEventListener('click', (event) => {
      const target = event.target as HTMLSelectElement;
      if (!target.matches('.teams__deleteMemberLink')) return;
      event.preventDefault();

      const td = document.querySelector('.teams__actions') as HTMLTableDataCellElement;
      td.classList.add('loading');

      const url = target.getAttribute('href') || '';
      const closeText = flashUl.getAttribute('data-close') || 'Close';
      const deleteMemberText = flashUl.getAttribute('data-deleteMember') || '';
      const name = target.getAttribute('data-name') || '';

      fetch(url)
        .then(response => response.json())
        .then(data => {
          if (data !== 'success') {
            addFlashMessage(flashUl, data, 'error', closeText);
            td.classList.remove('loading');
            return;
          }

          const message = deleteMemberText.replace('<name>', name);
          addFlashMessage(flashUl, message, 'info', closeText);
          td.classList.remove('loading');
          td.parentElement?.remove();
        })
        .catch(error => {
          addFlashMessage(flashUl, error.message, 'error', closeText);
          td.classList.remove('loading');
        });
    });
  }
});
