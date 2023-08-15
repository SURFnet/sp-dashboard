import * as $ from 'jquery';
import addFlashMessage from './addFlashMessage';

$(() => {
  const flashUl = document.querySelector('.teams__flashMessages') as HTMLUListElement;
  const changeRoleLinks = document.querySelectorAll('.teams__changeMemberRole');
  changeRoleLinks.forEach(item => {
    item.addEventListener('click', (event) => {
      event.preventDefault();
      const target = event.target as HTMLAnchorElement;
      const roleName = target.getAttribute('data-role');
      const parentElement = target.closest('div');
      if (roleName === null) return;
      if (parentElement === null) return;

      if (parentElement.getAttribute('data-url')) {
        const parent = target.closest('td') as HTMLTableDataCellElement;
        const name = (parent?.previousElementSibling?.previousElementSibling?.textContent)?.trim() || '';
        const closeText = flashUl.getAttribute('data-close') || 'Close';
        const changeRoleText = flashUl.getAttribute('data-roleChange') || `Role changed to ${roleName}`;
        const url = parentElement!.getAttribute('data-url')?.replace('stand-in', roleName) || '';

        fetch(url)
          .then(response => response.json())
          .then(data => {
            if (data !== 'ok') {
              addFlashMessage(flashUl, data, 'error', closeText);
              return;
            }
            const message = changeRoleText.replace('<role>', roleName).replace('<name>', name);
            addFlashMessage(flashUl, message, 'info', closeText);
            parent.querySelectorAll('span').forEach(role => {
              role.classList.add('hidden');
            });
            const newRole = parent.querySelector(`.role__${roleName}`);
            if (newRole) newRole.classList.remove('hidden');
          })
          .catch(error => {
            addFlashMessage(flashUl, error.message, 'error', closeText);
          });
      } else {
        const parent = target.closest('.roles') as HTMLDivElement;
        const field = parent.querySelector('input');
        if (!field) return;
        field.setAttribute('value', roleName);
        parent.querySelectorAll('span').forEach(role => {
          role.classList.add('hidden');
        });
        const newRole = parent.querySelector(`.role__${roleName}`);
        if (newRole) newRole.classList.remove('hidden');
      }
    });
  });
});
