import * as $ from 'jquery';
import addFlashMessage from './addFlashMessage';

$(() => {
  const selects = document.querySelectorAll('select[data-url]');
  const flashUl = document.querySelector('.teams__flashMessages') as HTMLUListElement;

  if (!!selects && !!flashUl) {
    document.addEventListener('change', (event) => {
      const target = event.target as HTMLSelectElement;
      if (!target.matches('select[data-url]')) return;

      target.classList.add('loading');

      const value = target.value;
      const name = target?.parentElement?.previousElementSibling?.previousElementSibling?.previousElementSibling?.textContent || '';
      const closeText = flashUl.getAttribute('data-close') || 'Close';
      const changeRoleText = flashUl.getAttribute('data-roleChange') || `Role changed to ${value}`;
      const url = target.getAttribute('data-url')?.replace('stand-in', value) || '';

      fetch(url)
        .then(response => response.json())
        .then(data => {
          if (data !== 'ok') {
            addFlashMessage(flashUl, data, 'error', closeText);
            target.classList.remove('loading');
            return;
          }

          const message = changeRoleText.replace('<role>', value).replace('<name>', name);
          addFlashMessage(flashUl, message, 'info', closeText);
          target.classList.remove('loading');
        })
        .catch(error => {
          addFlashMessage(flashUl, error.message, 'error', closeText);
          target.classList.remove('loading');
        });
    });
  }
});
