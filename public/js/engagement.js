document.addEventListener('DOMContentLoaded', () => {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

  function matchingMetrics(type, id) {
    return [...document.querySelectorAll('[data-engagement-metrics]')]
      .filter(element => element.dataset.engagementMetrics === type && element.dataset.entityId === String(id));
  }

  function renderMetrics(type, id, result) {
    matchingMetrics(type, id).forEach(metrics => {
      for (const key of ['views', 'likes', 'followers']) {
        const target = metrics.querySelector(`[data-engagement-${key}]`);
        if (target && Number.isSafeInteger(result[key])) target.textContent = result[key].toLocaleString();
      }

      metrics.querySelectorAll('[data-engagement-like], [data-engagement-follow]').forEach(button => {
        if (button.dataset.engagementLike) {
          button.dataset.liked = String(Boolean(result.liked));
          button.setAttribute('aria-pressed', button.dataset.liked);
          const icon = button.querySelector('i');
          if (icon) icon.className = `bi ${result.liked ? 'bi-heart-fill' : 'bi-heart'}`;
        }
        if (button.dataset.engagementFollow) {
          button.dataset.following = String(Boolean(result.following));
          button.setAttribute('aria-pressed', button.dataset.following);
          const icon = button.querySelector('i');
          const label = button.querySelector('[data-engagement-label]');
          if (icon) icon.className = `bi ${result.following ? 'bi-person-check' : 'bi-person-plus'}`;
          if (label) label.textContent = result.following ? 'Following' : 'Follow';
        }
      });
    });

    if (type === 'artwork') {
      [...document.querySelectorAll('[data-engagement-like]')]
        .filter(button => button.dataset.entityId === String(id))
        .forEach(button => {
          button.dataset.liked = String(Boolean(result.liked));
          button.setAttribute('aria-pressed', button.dataset.liked);
          const icon = button.querySelector('i');
          if (icon) icon.className = `bi ${result.liked ? 'bi-heart-fill' : 'bi-heart'}`;
        });
      window.dispatchEvent(new Event('pixmoree:engagement-updated'));
    }
  }

  function showError(button, message) {
    const feedback = button.closest('[data-engagement-metrics]')?.querySelector('[data-engagement-feedback]') ||
      document.querySelector('[data-engagement-feedback]');
    if (feedback) {
      feedback.textContent = message;
      feedback.hidden = false;
    } else {
      console.error(message);
    }
  }

  async function sendRequest(path, method) {
    if (!csrfToken) throw new Error('Secure request token missing. Refresh the page and try again.');
    const response = await fetch(path, {
      method,
      credentials: 'same-origin',
      headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken }
    });
    const result = await response.json().catch(() => null);
    if (!response.ok) throw new Error(result?.message || 'Could not update this engagement. Please try again.');

    return result;
  }

  async function recordArtworkView(id) {
    try {
      const result = await sendRequest(`/api/artworks/${encodeURIComponent(id)}/views`, 'POST');
      renderMetrics('artwork', id, result);
    } catch (error) {
      console.error('Could not record this artwork view.', error);
    }
  }

  document.querySelectorAll('[data-engagement-like], [data-engagement-follow]').forEach(button => {
    button.addEventListener('click', async () => {
      const type = button.dataset.engagementLike || button.dataset.engagementFollow;
      const action = button.dataset.engagementLike ? 'like' : 'follow';
      const active = action === 'like' ? button.dataset.liked === 'true' : button.dataset.following === 'true';
      const collection = type === 'artwork' ? 'artworks' : 'creators';
      const path = `/api/${collection}/${encodeURIComponent(button.dataset.entityId)}/${action}`;
      button.disabled = true;
      const feedback = button.closest('[data-engagement-metrics]')?.querySelector('[data-engagement-feedback]') ||
        document.querySelector('[data-engagement-feedback]');
      if (feedback) feedback.hidden = true;
      try {
        const result = await sendRequest(path, active ? 'DELETE' : 'PUT');
        renderMetrics(type, button.dataset.entityId, result);
      } catch (error) {
        showError(button, error instanceof Error ? error.message : 'Could not update this engagement.');
      } finally {
        button.disabled = false;
      }
    });

    window.addEventListener('pixmoree:engagement-updated', () => {
      document.querySelectorAll('#memberFavoriteItems a.member-art-tile').forEach(tile => {
        const likeButton = tile.querySelector('[data-engagement-like]');
        if (likeButton?.dataset.liked === 'false') tile.remove();
      });
    });
  });

  window.addEventListener('pixmoree:artwork-view', async event => {
    const id = event.detail?.id;
    if (typeof id !== 'string' || id === '') return;
    recordArtworkView(id);
  });

  const detailPanel = document.getElementById('nftDetailEngagement');
  if (detailPanel?.dataset.entityId) recordArtworkView(detailPanel.dataset.entityId);
});