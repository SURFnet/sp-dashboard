export default function addFlashMessage(element: HTMLElement, message: string, type: string, buttonText: string) {
  const li = document.createElement('li');
  const innerHtml = `<p>${message}</p><button class="flashMessage__close">${buttonText}</button>`;

  li.setAttribute('class', `flashMessage ${type}`);
  li.innerHTML = innerHtml;

  element.appendChild(li);

  li.querySelector('.flashMessage__close')?.addEventListener('click', (event) => {
    const target = event.target as HTMLButtonElement;
    target.parentElement?.remove();
  });
}
