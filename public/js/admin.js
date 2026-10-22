document.addEventListener('DOMContentLoaded', () => {
  const navigation = [...document.querySelectorAll('[data-admin-nav]')];
  const panels = [...document.querySelectorAll('[data-admin-panel]')];
  const breadcrumb = document.getElementById('adminBreadcrumb');
  const sidebar = document.getElementById('adminSidebar');
  const menuToggle = document.getElementById('adminMenuToggle');
  const dialog = document.getElementById('nftDialog');
  const nftForm = document.getElementById('nftForm');
  const toast = document.getElementById('adminToast');
  const nftRows = document.getElementById('nftRows');
  const overviewNftRows = document.getElementById('overviewNftRows');
  const nftItems = [
    { title: 'Neon Wanderer', creator: 'pixel.dreams', category: 'Space', price: '0.86', status: 'Listed', image: 'assets/anime-style-character-space.jpg' },
    { title: 'Moonlit Guardian', creator: 'neko.studio', category: 'Characters', price: '1.24', status: 'Listed', image: 'assets/cute-astronaut-samurai-with-kitsune-mask-katana-sword-cartoon-vector-icon-illustration-science.png' },
    { title: 'Starlight Drift', creator: 'cosmic.art', category: 'Space', price: '1.52', status: 'Review', image: 'assets/anime-style-character-space (2).jpg' },
    { title: 'Aurora Veil', creator: 'jema.works', category: 'Anime', price: '1.64', status: 'Listed', image: 'assets/29108282_JEMA GER 1446-01.jpg' },
    { title: 'Pixel Ronin', creator: 'pixel.soul', category: 'Pixel art', price: '0.98', status: 'Draft', image: 'assets/36101435_7200_4_03.jpg' },
    { title: 'Virtual Voyager', creator: 'astro.design', category: 'Characters', price: '1.13', status: 'Listed', image: 'assets/anime-character-using-virtual-reality-glasses-metaverse.jpg' }
  ];
  let activeNftFilter = 'all';
  let toastTimer;

  function notify(message) {
    toast.textContent = message;
    toast.classList.add('visible');
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(() => toast.classList.remove('visible'), 2800);
  }

  function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, character => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    })[character]);
  }

  function renderNfts() {
    const search = (document.getElementById('nftSearch').value || '').trim().toLowerCase();
    const filtered = nftItems.filter(item => {
      const matchesFilter = activeNftFilter === 'all' || item.status === activeNftFilter;
      const matchesSearch = `${item.title} ${item.creator} ${item.category}`.toLowerCase().includes(search);
      return matchesFilter && matchesSearch;
    });
    nftRows.innerHTML = filtered.map(item => `
      <tr>
        <td><span class="admin-art-cell"><img src="${escapeHtml(item.image.startsWith('assets/') ? `/public/${item.image}` : item.image)}" alt="">${escapeHtml(item.title)}</span></td>
        <td>${escapeHtml(item.creator)}</td>
        <td><span class="admin-category-tag">${escapeHtml(item.category)}</span></td>
        <td class="admin-price">${escapeHtml(item.price)} ETH</td>
        <td><span class="status-badge ${item.status === 'Listed' ? 'status-listed' : item.status === 'Review' ? 'status-review' : 'status-draft'}">${escapeHtml(item.status)}</span></td>
        <td><div class="admin-row-actions"><button class="row-action nft-edit" type="button" data-index="${nftItems.indexOf(item)}" aria-label="Edit ${escapeHtml(item.title)}"><i class="bi bi-pencil"></i></button><button class="row-action nft-delete" type="button" data-index="${nftItems.indexOf(item)}" aria-label="Delete ${escapeHtml(item.title)}"><i class="bi bi-trash3"></i></button></div></td>
      </tr>`).join('') || '<tr><td colspan="6" class="admin-empty-state">No artwork matches this filter.</td></tr>';
    overviewNftRows.innerHTML = nftItems.slice(0, 4).map(item => `
      <tr><td><span class="admin-art-cell"><img src="${escapeHtml(item.image.startsWith('assets/') ? `/public/${item.image}` : item.image)}" alt="">${escapeHtml(item.title)}</span></td><td>${escapeHtml(item.creator)}</td><td><span class="admin-category-tag">${escapeHtml(item.category)}</span></td><td class="admin-price">${escapeHtml(item.price)} ETH</td><td><span class="status-badge ${item.status === 'Listed' ? 'status-listed' : item.status === 'Review' ? 'status-review' : 'status-draft'}">${escapeHtml(item.status)}</span></td></tr>`).join('');
    document.getElementById('nftNavCount').textContent = String(128 + Math.max(0, nftItems.length - 6));
    document.getElementById('statNfts').textContent = String(128 + Math.max(0, nftItems.length - 6));
    document.getElementById('statListed').textContent = String(84 + nftItems.filter(item => item.added && item.status === 'Listed').length);
    document.getElementById('nftFilterAll').textContent = String(nftItems.length);
    document.getElementById('nftTableCount').textContent = `${filtered.length} artwork${filtered.length === 1 ? '' : 's'}`;
  }

  navigation.forEach(button => {
    button.addEventListener('click', () => {
      const target = button.dataset.adminNav;
      const activeButton = navigation.find(item => item.dataset.adminNav === target);
      navigation.forEach(item => item.classList.toggle('active', item === activeButton));
      panels.forEach(panel => {
        const active = panel.dataset.adminPanel === target;
        panel.classList.toggle('active', active);
        panel.hidden = !active;
      });
      breadcrumb.textContent = activeButton.textContent.trim().replace(/\s+\d+$/, '');
      document.getElementById('adminSearch').value = '';
      if (target === 'nfts') renderNfts();
      sidebar.classList.remove('mobile-open');
      menuToggle.setAttribute('aria-expanded', 'false');
    });
  });

  menuToggle.addEventListener('click', () => {
    const open = sidebar.classList.toggle('mobile-open');
    menuToggle.setAttribute('aria-expanded', String(open));
  });

  document.querySelectorAll('[data-open-nft]').forEach(button => button.addEventListener('click', () => dialog.showModal()));
  document.querySelectorAll('[data-close-dialog]').forEach(button => button.addEventListener('click', () => dialog.close()));
  dialog.addEventListener('click', event => {
    if (event.target === dialog) dialog.close();
  });

  nftForm.addEventListener('submit', event => {
    event.preventDefault();
    if (!nftForm.reportValidity()) return;
    const data = new FormData(nftForm);
    const imageUrl = String(data.get('image') || '').trim();
    let image = 'assets/Neon Pixel Gradient Pixmoree Logo.png';
    if (imageUrl) {
      const parsedImageUrl = new URL(imageUrl);
      if (!['http:', 'https:'].includes(parsedImageUrl.protocol)) {
        notify('Artwork image URLs must use HTTP or HTTPS.');
        return;
      }
      image = parsedImageUrl.href;
    }
    nftItems.unshift({
      title: String(data.get('title')).trim(),
      creator: String(data.get('creator')).trim(),
      category: String(data.get('category')),
      price: Number(data.get('price')).toFixed(2),
      status: String(data.get('status')),
      image,
      added: true
    });
    nftForm.reset();
    dialog.close();
    renderNfts();
    notify('Demo artwork added for this session.');
  });

  nftRows.addEventListener('click', event => {
    const button = event.target.closest('button[data-index]');
    if (!button) return;
    const index = Number(button.dataset.index);
    const item = nftItems[index];
    if (!item) return;
    if (button.classList.contains('nft-delete')) {
      if (!window.confirm(`Remove "${item.title}" from this demo list?`)) return;
      nftItems.splice(index, 1);
      renderNfts();
      notify('Demo artwork removed for this session.');
    } else if (button.classList.contains('nft-edit')) {
      const title = window.prompt('Artwork title', item.title);
      if (title === null) return;
      const price = window.prompt('Price in ETH', item.price);
      if (price === null) return;
      const parsedPrice = Number(price);
      if (!title.trim() || !Number.isFinite(parsedPrice) || parsedPrice < 0) {
        notify('Enter a title and a valid non-negative price.');
        return;
      }
      item.title = title.trim();
      item.price = parsedPrice.toFixed(2);
      renderNfts();
      notify('Demo artwork updated for this session.');
    }
  });

  document.querySelectorAll('[data-nft-filter]').forEach(button => {
    button.addEventListener('click', () => {
      activeNftFilter = button.dataset.nftFilter;
      document.querySelectorAll('[data-nft-filter]').forEach(item => item.classList.toggle('active', item === button));
      renderNfts();
    });
  });
  document.getElementById('nftSearch').addEventListener('input', renderNfts);

  document.querySelectorAll('.user-toggle').forEach(button => {
    button.addEventListener('click', () => {
      const row = button.closest('tr');
      const status = row.querySelector('.user-status');
      const active = status.textContent.trim() === 'Active';
      status.textContent = active ? 'Inactive' : 'Active';
      status.classList.toggle('status-listed', !active);
      status.classList.toggle('status-draft', active);
      button.textContent = active ? 'Activate' : 'Deactivate';
      notify(`${row.querySelector('.admin-user-cell strong').textContent} is now ${active ? 'inactive' : 'active'} in this demo.`);
    });
  });

  document.querySelectorAll('.category-remove').forEach(button => {
    button.addEventListener('click', () => {
      const card = button.closest('.admin-category-card');
      if (!window.confirm(`Remove the "${card.querySelector('h2').textContent}" demo category?`)) return;
      card.remove();
      notify('Demo category removed for this session.');
    });
  });
  document.getElementById('addCategory').addEventListener('click', () => {
    const name = window.prompt('Name for the new demo category:');
    if (!name || !name.trim()) return;
    const card = document.createElement('article');
    card.className = 'admin-category-card';
    card.innerHTML = `<span class="category-icon category-purple"><i class="bi bi-stars"></i></span><div><h2>${escapeHtml(name.trim())}</h2><p>0 artworks · Active</p></div><button class="admin-more-button category-remove" type="button" aria-label="Remove ${escapeHtml(name.trim())} category"><i class="bi bi-three-dots"></i></button>`;
    document.getElementById('categoryGrid').appendChild(card);
    card.querySelector('.category-remove').addEventListener('click', () => {
      if (window.confirm(`Remove the "${name.trim()}" demo category?`)) {
        card.remove();
        notify('Demo category removed for this session.');
      }
    });
    notify('Demo category added for this session.');
  });

  document.getElementById('saveSettings').addEventListener('click', () => {
    const feedback = document.getElementById('settingsFeedback');
    feedback.textContent = 'Preferences updated for this demo session only.';
    feedback.hidden = false;
  });

  document.getElementById('userSearch').addEventListener('input', event => {
    const query = event.target.value.trim().toLowerCase();
    document.querySelectorAll('#userRows tr').forEach(row => row.hidden = !row.dataset.search.includes(query));
  });
  document.getElementById('transactionSearch').addEventListener('input', event => {
    const query = event.target.value.trim().toLowerCase();
    document.querySelectorAll('#transactionRows tr').forEach(row => row.hidden = !row.dataset.search.includes(query));
  });
  document.getElementById('adminSearch').addEventListener('input', event => {
    const panel = document.querySelector('.admin-panel.active');
    const query = event.target.value.trim().toLowerCase();
    panel?.querySelectorAll('tbody tr').forEach(row => row.hidden = !row.textContent.toLowerCase().includes(query));
  });

  document.querySelectorAll('[data-admin-nav="transactions"]').forEach(button => button.addEventListener('click', () => {
    document.getElementById('transactionSearch').value = '';
    document.querySelectorAll('#transactionRows tr').forEach(row => row.hidden = false);
  }));
  document.querySelectorAll('[data-admin-nav="users"]').forEach(button => button.addEventListener('click', () => {
    document.getElementById('userSearch').value = '';
    document.querySelectorAll('#userRows tr').forEach(row => row.hidden = false);
  }));

  renderNfts();
});
