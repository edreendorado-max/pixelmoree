document.addEventListener('DOMContentLoaded', () => {
  const demoEmail = 'demo@pixmoree.test';
  const demoPassword = 'PixmoreeDemo2026!';
  const demoSessionKey = 'pixmoree.demo.session.v1';
  const profileStorageKey = 'pixmoree.user.profile.v1';

  const showFeedback = (form, message) => {
    const feedback = form.querySelector('.auth-feedback');
    if (!feedback) return;
    feedback.textContent = message;
    feedback.hidden = false;
  };

  document.querySelectorAll('[data-auth-form]').forEach(form => {
    form.addEventListener('submit', event => {
      event.preventDefault();
      if (!form.reportValidity()) return;

      const password = form.querySelector('[name="password"]');
      const confirmation = form.querySelector('[name="password_confirmation"]');
      if (confirmation && password.value !== confirmation.value) {
        confirmation.setCustomValidity('The passwords do not match.');
        confirmation.reportValidity();
        confirmation.addEventListener('input', () => confirmation.setCustomValidity(''), { once: true });
        return;
      }

      const email = form.querySelector('[name="email"]');
      if (email && password && form.querySelector('#login-email')) {
        if (email.value.trim().toLowerCase() !== demoEmail || password.value !== demoPassword) {
          showFeedback(form, 'Those details do not match the demo account. Use the credentials shown below the form.');
          return;
        }
        try {
          localStorage.setItem(profileStorageKey, JSON.stringify({
            name: 'Pixmoree Demo User',
            email: demoEmail,
            phone: '',
            bio: 'Exploring digital art on Pixmoree.',
            social: '',
            emailNotifications: true,
            avatar: '',
            banner: ''
          }));
          localStorage.setItem(demoSessionKey, JSON.stringify({ email: demoEmail, signedInAt: new Date().toISOString() }));
        } catch (error) {
          console.error('Could not start the Pixmoree demo account in this browser.', error);
          showFeedback(form, 'Could not start the demo account. Check browser storage settings and try again.');
          return;
        }
        window.location.href = '/user';
        return;
      }

      showFeedback(form, 'Authentication is not connected yet. This page is currently a visual demo; no account data was sent or saved.');
    });
  });

  document.querySelector('[data-demo-login]')?.addEventListener('click', () => {
    const form = document.querySelector('[data-auth-form]');
    if (!form) return;
    form.querySelector('[name="email"]').value = demoEmail;
    form.querySelector('[name="password"]').value = demoPassword;
    form.requestSubmit();
  });

  document.querySelectorAll('[data-auth-provider]').forEach(button => {
    button.addEventListener('click', event => {
      event.preventDefault();
      const card = button.closest('.auth-card');
      const form = card?.querySelector('[data-auth-form]');
      if (form) {
        showFeedback(form, `${button.dataset.authProvider} sign-in is not configured yet. No account data was sent or saved.`);
      }
    });
  });
});
