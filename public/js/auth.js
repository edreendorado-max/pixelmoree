document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-auth-provider]').forEach(button => {
    button.addEventListener('click', event => {
      event.preventDefault();
      const form = button.closest('.auth-card')?.querySelector('.auth-form');
      const feedback = form?.querySelector('.auth-feedback');
      if (!feedback) return;

      feedback.textContent = `${button.dataset.authProvider} sign-in is not configured yet.`;
      feedback.hidden = false;
    });
  });
});
