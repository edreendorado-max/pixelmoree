document.addEventListener('DOMContentLoaded', () => {
  const popup = document.querySelector('[data-notification-popup]');
  if (!popup || !(popup instanceof HTMLDialogElement)) return;

  if (typeof popup.showModal === 'function') popup.showModal();
  else popup.setAttribute('open', '');

  popup.querySelector('[data-notification-close]')?.addEventListener('click', () => {
    if (typeof popup.close === 'function') popup.close();
    else popup.removeAttribute('open');
  });
});
