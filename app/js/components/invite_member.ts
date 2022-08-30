import * as $ from 'jquery';

$(() => {
  const select = document.getElementById('role-select') as HTMLSelectElement;
  const hiddenRole = document.getElementById('role_hidden') as HTMLInputElement;

  if (!!select) {
    document.addEventListener('change', (event) => {
      const target = event.target as HTMLSelectElement;
      if (!target.matches('#role-select')) return;
      hiddenRole.value = target.value;
    });
  }
});
