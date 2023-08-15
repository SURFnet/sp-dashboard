import * as $ from 'jquery';
import addFlashMessage from './addFlashMessage';

$(() => {
  const flashUl = document.querySelector('.teams__flashMessages') as HTMLUListElement;
  const resendInviteLinks = document.querySelectorAll('.teams__resendInviteLink');

  if (!!flashUl && !!resendInviteLinks) {
    resendInviteLinks.forEach(item => {
      item.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopImmediatePropagation();
        const target = event.target as HTMLSelectElement;
        if (!target.matches('.teams__resendInviteLink')) return;

        const td = target?.parentElement?.parentElement?.parentElement?.parentElement as HTMLTableDataCellElement;
        td.classList.add('loading');

        const url = target.getAttribute('href') || '';
        const closeText = flashUl.getAttribute('data-close') || 'Close';

        fetch(url)
          .then(response => response.json())
          .then(data => {
            if (data !== 'ok') {
              addFlashMessage(flashUl, data, 'error', closeText);
              td.classList.remove('loading');
              return;
            }

            const resentInviteText = flashUl.getAttribute('data-resendInvite') || '';
            const email = target.getAttribute('data-email') || '';
            const message = resentInviteText.replace('<email>', email);
            addFlashMessage(flashUl, message, 'info', closeText);
            td.classList.remove('loading');
          })
          .catch(error => {
            addFlashMessage(flashUl, error.message, 'error', closeText);
            td.classList.remove('loading');
          });
      });
    });
  }
});
