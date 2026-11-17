document.addEventListener('DOMContentLoaded', () => {
  const popup = document.querySelector('[data-notification-popup]');
  if (!popup || !(popup instanceof HTMLDialogElement)) return;

  if (typeof popup.showModal === 'function') popup.showModal();
  else popup.setAttribute('open', '');

  let closing = false;
  const finishClose = () => {
    if (!popup.open) return;
    if (typeof popup.close === 'function') popup.close();
    else popup.removeAttribute('open');
  };
  const closeWithAnimation = () => {
    if (closing) return;
    closing = true;
    popup.classList.add('is-closing');
    popup.addEventListener('animationend', (event) => {
      if (event.target === popup && event.animationName === 'notification-exit') finishClose();
    }, { once: true });
    window.setTimeout(finishClose, 300);
  };

  popup.addEventListener('cancel', (event) => {
    event.preventDefault();
    closeWithAnimation();
  });
  popup.querySelector('[data-notification-close]')?.addEventListener('click', () => {
    closeWithAnimation();
  });
});
