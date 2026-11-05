document.addEventListener('DOMContentLoaded', () => {
  const balances = [...document.querySelectorAll('[data-pixelcoin-balance]')];
  const topupButtons = [...document.querySelectorAll('[data-pixel-topup]')];
  const activity = document.getElementById('memberPixelCoinActivity');
  const feedback = document.getElementById('memberPixelWalletFeedback');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
  let walletLoaded = false;
  let transactions = [];

  function showMessage(message, isError = false) {
    if (!feedback) return;
    feedback.textContent = message;
    feedback.classList.toggle('is-error', isError);
    feedback.hidden = false;
  }

  function renderActivity() {
    if (!activity) return;
    if (!transactions.length) {
      const row = document.createElement('tr');
      const cell = document.createElement('td');
      cell.colSpan = 4;
      cell.className = 'member-empty-state';
      cell.textContent = 'No wallet activity yet.';
      row.append(cell);
      activity.replaceChildren(row);
      return;
    }

    activity.replaceChildren(...transactions.map(transaction => {
      const row = document.createElement('tr');
      const label = document.createElement('td');
      label.textContent = transaction.description || 'Pixel Coin activity';
      const amount = document.createElement('td');
      const isCredit = transaction.type === 'demo_top_up';
      amount.textContent = `${isCredit ? '+' : '-'}${Number(transaction.amount).toLocaleString()} PIXEL COIN`;
      const balance = document.createElement('td');
      balance.textContent = `${Number(transaction.balance_after).toLocaleString()} PIXEL COIN`;
      const date = document.createElement('td');
      const dateValue = transaction.created_at ? new Date(transaction.created_at) : null;
      date.textContent = dateValue && Number.isFinite(dateValue.getTime())
        ? new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(dateValue)
        : '-';
      row.append(label, amount, balance, date);
      return row;
    }));
  }

  function applyWallet(payload) {
    if (!Number.isSafeInteger(payload?.balance) || payload.balance < 0) {
      throw new Error('The wallet returned an invalid balance. Refresh and try again.');
    }

    balances.forEach(balance => {
      balance.textContent = payload.balance.toLocaleString();
    });
    if (Array.isArray(payload.transactions)) transactions = payload.transactions;
    else if (payload.transaction) transactions = [payload.transaction, ...transactions].slice(0, 20);
    walletLoaded = true;
    topupButtons.forEach(button => { button.disabled = false; });
    renderActivity();
  }

  async function requestWallet(method = 'GET', body = null) {
    if (!csrfToken) throw new Error('Secure wallet token missing. Refresh the page and try again.');
    const response = await fetch(body === null ? '/api/pixel-coins' : '/api/pixel-coins/top-ups', {
      method,
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      ...(body === null ? {} : { body: JSON.stringify(body) })
    });
    const payload = await response.json().catch(() => null);
    if (!response.ok) {
      throw new Error(payload?.message || Object.values(payload?.errors || {}).flat()[0] || 'Wallet request failed. Try again.');
    }
    return payload;
  }

  async function loadWallet() {
    try {
      applyWallet(await requestWallet());
    } catch (error) {
      showMessage(error instanceof Error ? error.message : 'Could not load the wallet.', true);
      if (activity) {
        activity.innerHTML = '<tr><td colspan="4" class="member-empty-state">Wallet activity is unavailable.</td></tr>';
      }
    }
  }

  topupButtons.forEach(button => {
    button.addEventListener('click', async () => {
      const amount = Number(button.dataset.pixelTopup);
      if (!Number.isSafeInteger(amount) || ![500, 1000, 2500].includes(amount) || !walletLoaded) return;
      topupButtons.forEach(option => { option.disabled = true; });
      feedback.hidden = true;
      try {
        const result = await requestWallet('POST', { amount });
        applyWallet(result);
        showMessage(`${amount.toLocaleString()} free demo PIXEL COIN added. No payment was made.`);
      } catch (error) {
        showMessage(error instanceof Error ? error.message : 'Could not add demo credits.', true);
      } finally {
        topupButtons.forEach(option => { option.disabled = !walletLoaded; });
      }
    });
  });

  loadWallet();
});