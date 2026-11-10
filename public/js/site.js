document.addEventListener('DOMContentLoaded', () => {
  const gallery = document.getElementById('artGrid');
  const cards = [...document.querySelectorAll('.art-column')];
  const filterButtons = [...document.querySelectorAll('.filter-pill')];
  const sortSelect = document.getElementById('sortSelect');
  const search = document.getElementById('gallerySearch');
  const loadMoreButton = document.getElementById('loadMoreItems');
  const creatorTrack = document.getElementById('creatorTrack');
  const creatorPagination = [...document.querySelectorAll('.creator-pagination span')];
  const trendingFeature = document.querySelector('.trending-feature');
  const trendingSlides = [...document.querySelectorAll('.trending-feature-slide')];
  const trendingPagination = [...document.querySelectorAll('.trending-nft-pagination button')];
  const creatorArrowPrev = document.querySelector('.creator-arrow-prev');
  const creatorArrowNext = document.querySelector('.creator-arrow-next');
  const nftPreview = document.getElementById('nftPreview');
  const nftPreviewImage = document.getElementById('nftPreviewImage');
  const nftPreviewTitle = document.getElementById('nftPreviewTitle');
  const nftPreviewCreator = document.getElementById('nftPreviewCreator');
  const memberPath = window.location.pathname.replace(/\/+$/, '');
  const isMemberPage = memberPath === '/user' || memberPath.startsWith('/user/');
  const requestedMemberSection = isMemberPage && memberPath !== '/user' ? memberPath.split('/').pop() : 'home';
  const memberSection = requestedMemberSection === 'purchases' ? 'history'
    : requestedMemberSection === 'nft-management' || requestedMemberSection === 'dashboard' || requestedMemberSection === 'collection' ? 'me'
      : requestedMemberSection === 'settings' || requestedMemberSection === 'profile-settings' ? 'profile'
        : requestedMemberSection === 'favorites' ? 'me'
      : requestedMemberSection;
  const requestedCollectionTab = requestedMemberSection === 'favorites'
    ? 'favorites'
    : new URLSearchParams(window.location.search).get('tab');
  const isMemberAccountPage = isMemberPage && memberPath !== '/user';
  const memberProfile = document.getElementById('memberProfile');
  const memberWelcome = document.getElementById('memberWelcome');
  const memberPanels = [...document.querySelectorAll('[data-member-panel-content]')];
  let activeFilter = 'all';
  let activeTrendingSlide = 0;
  let visibleGalleryLimit = 8;
  let trendingAutoplayTimer;
  const accountPixelCoinBalance = Number(window.pixmoreeAccount?.pixel_coin_balance ?? 0);
  let pixelCoinBalance = Number.isSafeInteger(accountPixelCoinBalance) && accountPixelCoinBalance >= 0
    ? accountPixelCoinBalance
    : 0;
  let pixelCoinTransactions = [];
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (isMemberPage) {
    document.body.classList.add('member-area');
    if (isMemberAccountPage) document.body.classList.add('member-account-page');
    document.title = isMemberAccountPage
      ? `${memberSection === 'me' ? 'Me' : memberSection === 'history' ? 'Purchases' : memberSection === 'earnings' ? 'Earnings Balance' : memberSection === 'payment' ? 'Payment & Transactions' : memberSection === 'pixel-coin' ? 'Pixel Coin Wallet' : memberSection === 'terms' ? 'Terms & Originality' : memberSection === 'create' ? 'Create' : memberSection === 'profile' ? 'Profile Settings' : memberSection === 'notifications' ? 'Notifications' : memberSection === 'about' ? 'About Pixmoree' : 'Dashboard'} — Pixmoree`
      : 'Pixmoree — Your Creative Space';
    document.querySelector('.navbar-brand').href = '/user';
    memberProfile.hidden = false;
    memberWelcome.hidden = false;
    const loginLink = document.querySelector('.guest-nav .login-link');
    if (loginLink) loginLink.hidden = true;
    document.querySelectorAll('.member-only-nav-link').forEach(link => { link.hidden = false; });
    document.querySelectorAll('.guest-only-nav-link').forEach(link => { link.hidden = true; });
    const homeLink = document.querySelector('[data-member-nav="home"]');
    homeLink.href = '/user';
    const collectionLink = document.querySelector('[data-member-nav="collection"]');
    collectionLink.href = '/user/me';
    collectionLink.querySelector('span').textContent = 'Me';
    collectionLink.querySelector('i').className = 'bi bi-person-circle';
    document.querySelector('[data-member-nav="pixel-coin"]').href = '/user/pixel-coin';
    document.querySelector('[data-member-nav="profile-settings"]').href = '/user/profile-settings';
    document.querySelector('[data-member-nav="about"]').href = '/user/about';
    document.querySelector('[data-member-nav="terms"]').href = '/user/terms';
    document.querySelector('.guest-hero .hero-content h1').textContent = 'Your creative universe.';
    document.querySelector('.guest-hero .hero-tagline').textContent = 'Discover digital art, collect what inspires you, and share your own creations.';
    document.querySelector('.guest-hero .hero-benefits li:first-child span').textContent = 'Browse new pieces from creators across the community.';
    document.querySelector('.guest-hero .hero-benefits li:last-child span').textContent = 'Build your collection or list original artwork of your own.';
    document.querySelector('.guest-hero .hero-explore').textContent = 'Explore artwork';
    document.querySelector('.guest-hero .hero-explore').href = '#explore';
    document.querySelector('.explore-heading h2').innerHTML = 'Discover artwork <i class="bi bi-compass"></i>';
    document.querySelector('.explore-heading .muted').textContent = 'Find a new favorite or add a piece to your collection.';
    document.querySelector('.member-menu-explore').href = '/user#explore';
    document.querySelector('.member-menu-explore').hidden = false;
    document.querySelectorAll('.member-dashboard-content a[href="#explore"]').forEach(link => {
      link.href = '/user#explore';
    });
  }

  function updateGallery() {
    const query = (search?.value || '').trim().toLowerCase();
    const orderedCards = [...cards];
    const sortMode = sortSelect?.value && sortSelect.value !== 'default' ? sortSelect.value : activeFilter;
    let matchingCardCount = 0;
    if (sortMode === 'newest') {
      orderedCards.sort((a, b) => Number(b.dataset.created) - Number(a.dataset.created));
    } else if (sortMode === 'price' || sortMode === 'price-low') {
      orderedCards.sort((a, b) => Number(a.dataset.price) - Number(b.dataset.price));
    } else if (sortMode === 'price-high') {
      orderedCards.sort((a, b) => Number(b.dataset.price) - Number(a.dataset.price));
    }

    orderedCards.forEach(card => {
      const matchesSearch = card.textContent.toLowerCase().includes(query);
      const matchesFavorites = activeFilter !== 'favorites' || card.querySelector('.heart-button')?.dataset.liked === 'true';
      const matchesFilters = matchesSearch && matchesFavorites;
      const isVisible = matchesFilters && (query.length > 0 || matchingCardCount < visibleGalleryLimit);
      if (matchesFilters) matchingCardCount += 1;
      card.classList.toggle('d-none', !isVisible);
      gallery?.appendChild(card);
    });
    if (loadMoreButton?.parentElement) {
      loadMoreButton.parentElement.hidden = query.length > 0 || matchingCardCount <= visibleGalleryLimit;
    }
  }

  filterButtons.forEach(button => {
    button.addEventListener('click', () => {
      activeFilter = button.dataset.filter || 'all';
      if (sortSelect) sortSelect.value = 'default';
      filterButtons.forEach(item => item.classList.toggle('active', item === button));
      updateGallery();
    });
  });

  sortSelect?.addEventListener('change', updateGallery);
  search?.addEventListener('input', updateGallery);
  loadMoreButton?.addEventListener('click', () => {
    visibleGalleryLimit += 5;
    updateGallery();
  });

  function showTrendingSlide(index) {
    if (!trendingSlides.length) return;
    activeTrendingSlide = (index + trendingSlides.length) % trendingSlides.length;
    const slide = trendingSlides[activeTrendingSlide];
    trendingSlides.forEach((item, slideIndex) => {
      const isActive = slideIndex === activeTrendingSlide;
      item.classList.toggle('active', isActive);
      item.setAttribute('aria-hidden', String(!isActive));
      item.setAttribute('role', 'button');
      item.tabIndex = isActive ? 0 : -1;
      item.setAttribute('aria-label', `View ${item.dataset.title} NFT`);
    });
    trendingPagination.forEach((button, buttonIndex) => {
      const isActive = buttonIndex === activeTrendingSlide;
      button.classList.toggle('active', isActive);
      button.setAttribute('aria-current', String(isActive));
    });
    trendingFeature.querySelector('.trending-feature-title').firstChild.textContent = slide.dataset.title;
    trendingFeature.querySelector('.trending-feature-creator').textContent = `By ${slide.dataset.creator}`;
    trendingFeature.querySelector('.trending-feature-floor').textContent = slide.dataset.floor;
    trendingFeature.querySelector('.trending-feature-items').textContent = slide.dataset.items;
  }

  function scheduleTrendingAutoplay() {
    window.clearTimeout(trendingAutoplayTimer);
    if (reducedMotion || document.hidden || nftPreview?.open || !trendingSlides.length ||
        trendingFeature.matches(':hover') || trendingFeature.contains(document.activeElement)) return;
    trendingAutoplayTimer = window.setTimeout(() => {
      showTrendingSlide(activeTrendingSlide + 1);
      scheduleTrendingAutoplay();
    }, 5000);
  }

  trendingPagination.forEach((button, index) => {
    button.addEventListener('click', () => {
      showTrendingSlide(index);
      scheduleTrendingAutoplay();
    });
  });
  trendingFeature?.querySelector('.trending-feature-prev')?.addEventListener('click', () => {
    showTrendingSlide(activeTrendingSlide - 1);
    scheduleTrendingAutoplay();
  });
  trendingFeature?.querySelector('.trending-feature-next')?.addEventListener('click', () => {
    showTrendingSlide(activeTrendingSlide + 1);
    scheduleTrendingAutoplay();
  });
  trendingFeature?.addEventListener('pointerenter', () => window.clearTimeout(trendingAutoplayTimer));
  trendingFeature?.addEventListener('pointerleave', scheduleTrendingAutoplay);
  trendingFeature?.addEventListener('focusin', () => window.clearTimeout(trendingAutoplayTimer));
  trendingFeature?.addEventListener('focusout', event => {
    if (!trendingFeature.contains(event.relatedTarget)) scheduleTrendingAutoplay();
  });
  document.addEventListener('visibilitychange', scheduleTrendingAutoplay);
  scheduleTrendingAutoplay();

  const searchControl = document.querySelector('.guest-search');
  const searchToggle = document.querySelector('.guest-search .search-icon');
  searchToggle?.addEventListener('click', () => {
    const isOpen = searchControl?.classList.toggle('is-open') || false;
    searchToggle.setAttribute('aria-expanded', String(isOpen));
    if (search) {
      search.tabIndex = isOpen ? 0 : -1;
      if (isOpen) search.focus();
    }
  });

  function updateMemberProfile(profile) {
    const name = typeof profile.name === 'string' && profile.name.trim() ? profile.name.trim() : 'Account';
    document.getElementById('memberNavName').textContent = name;
    document.querySelector('#memberNavAvatar .member-avatar-initial').textContent = name.charAt(0).toUpperCase();
    document.getElementById('memberWelcomeName').textContent = name.split(/\s+/)[0];
    document.getElementById('memberDashboardProfileName').textContent = name;
    const username = typeof profile.username === 'string' && profile.username.trim()
      ? profile.username.trim()
      : name.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g) || 'pixmoree-user';
    document.getElementById('memberDashboardUsername').textContent = `@${username}`;
    document.querySelector('#memberDashboardAvatar .member-avatar-initial').textContent = name.charAt(0).toUpperCase();
  }

  function formatMemberDate(date) {
    const parsedDate = new Date(date);
    return Number.isNaN(parsedDate.valueOf()) ? 'Date unavailable' :
      parsedDate.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
  }

  function makeArtworkTile(artwork, panel) {
    const article = document.createElement('a');
    article.className = 'member-art-tile';
    article.href = `/week12/artworks/${encodeURIComponent(artwork.id)}`;
    article.dataset.artworkId = artwork.id;
    const imageContainer = document.createElement('span');
    imageContainer.className = 'member-art-tile-image';
    const image = document.createElement('img');
    image.src = artwork.image;
    image.alt = artwork.alt || artwork.title;
    imageContainer.append(image);
    const details = document.createElement('span');
    details.className = 'member-art-tile-details';
    const title = document.createElement('strong');
    title.textContent = artwork.title;
    const creator = document.createElement('small');
    creator.textContent = `By ${artwork.creator || 'Pixmoree creator'}`;
    const price = document.createElement('span');
    price.textContent = artwork.price || '';
    details.append(title, creator, price);
    article.append(imageContainer, details);
    return article;
  }

  function renderMemberDashboard() {
    if (!isMemberPage) return;
    const favorites = [...document.querySelectorAll('#artGrid .art-column')]
      .filter(card => card.querySelector('[data-engagement-like][data-liked="true"]'))
      .map(card => getFavoriteFromButton(card.querySelector('.heart-button')))
      .filter(Boolean);
    const favoriteItems = document.getElementById('memberFavoriteItems');
    if (favoriteItems) {
      favoriteItems.replaceChildren(...(favorites.length
        ? favorites.map(item => makeArtworkTile(item, 'favorites'))
        : [Object.assign(document.createElement('p'), {
          className: 'member-empty-state',
          textContent: 'You have not favorited any artwork yet. Use the heart on a listing to save it to your account.'
        })]));
    }
    document.getElementById('memberWelcomeFavorites').textContent = String(favorites.length);
    document.getElementById('memberFavoritePanelCount').textContent = String(favorites.length);
  }

  window.addEventListener('pixmoree:engagement-updated', () => {
    renderMemberDashboard();
    updateGallery();
  });

  function updatePixelCoinBalance() {
    document.querySelectorAll('[data-pixelcoin-balance]').forEach(balance => {
      balance.textContent = pixelCoinBalance.toLocaleString();
    });
  }

  async function requestPixelCoinApi() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) throw new Error('The secure wallet request token is missing. Refresh the page and try again.');
    const response = await fetch('/api/pixel-coins', {
      method: 'GET',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrfToken
      }
    });
    const payload = await response.json().catch(() => null);
    if (!response.ok) {
      throw new Error(payload?.message || `Wallet request failed with status ${response.status}.`);
    }
    if (!payload || !Number.isSafeInteger(payload.balance) || payload.balance < 0) {
      throw new Error('The wallet API returned an invalid balance. Refresh the page and try again.');
    }
    return payload;
  }

  function applyPixelCoinWallet(payload) {
    pixelCoinBalance = payload.balance;
    if (Array.isArray(payload.transactions)) pixelCoinTransactions = payload.transactions;
    const navStatus = document.getElementById('memberPixelCoinNavStatus');
    if (navStatus) navStatus.hidden = true;
    const walletLink = document.querySelector('.member-pixel-coin-link');
    if (walletLink) {
      walletLink.removeAttribute('title');
      walletLink.setAttribute('aria-label', 'Pixel Coin wallet');
    }
    updatePixelCoinBalance();
    renderPixelCoinActivity();
  }

  async function refreshPixelCoinWallet() {
    applyPixelCoinWallet(await requestPixelCoinApi());
  }

  function showPixelCoinWalletError(error, fallback) {
    console.error(fallback, error);
    const message = error instanceof Error ? error.message : fallback;
    const feedback = document.getElementById('memberPixelWalletFeedback');
    if (feedback) {
      feedback.textContent = message;
      feedback.hidden = false;
    }
    const navStatus = document.getElementById('memberPixelCoinNavStatus');
    const walletLink = document.querySelector('.member-pixel-coin-link');
    if (navStatus) navStatus.hidden = false;
    if (walletLink) {
      walletLink.title = message;
      walletLink.setAttribute('aria-label', `Pixel Coin balance unavailable: ${message}`);
    }
  }

  function renderPixelCoinActivity() {
    const target = document.getElementById('memberPixelCoinActivity');
    if (!target) return;
    if (!pixelCoinTransactions.length) {
      const row = document.createElement('tr');
      const cell = document.createElement('td');
      cell.colSpan = 4;
      cell.className = 'member-empty-state';
      cell.textContent = 'No wallet activity yet.';
      row.append(cell);
      target.replaceChildren(row);
      return;
    }
    target.replaceChildren(...pixelCoinTransactions.map(item => {
      const row = document.createElement('tr');
      const label = document.createElement('td');
      label.textContent = item.description || (item.type === 'demo_top_up' ? 'Historical credit' : 'Pixel Coin purchase');
      const amount = document.createElement('td');
      const isCredit = item.type === 'demo_top_up';
      amount.textContent = `${isCredit ? '+' : '−'}${Number(item.amount).toLocaleString()} PIXEL COIN`;
      const balance = document.createElement('td');
      balance.textContent = `${Number(item.balance_after).toLocaleString()} PIXEL COIN`;
      const date = document.createElement('td');
      date.textContent = formatMemberDate(item.created_at);
      row.append(label, amount, balance, date);
      return row;
    }));
  }

  function showMemberPanel(panelName, shouldScroll = true) {
    const selectedPanel = memberPanels.find(panel => panel.dataset.memberPanelContent === panelName);
    if (!selectedPanel) return;
    memberPanels.forEach(panel => {
      const isActive = panel === selectedPanel;
      panel.hidden = !isActive;
      panel.classList.toggle('active', isActive);
    });
    document.querySelectorAll('[data-member-panel]').forEach(button => {
      button.classList.toggle('active', button.dataset.memberPanel === panelName);
    });
    const activeNav = panelName === 'me' ? 'collection' : panelName === 'profile' ? 'profile-settings' : panelName;
    document.querySelectorAll('[data-member-nav]').forEach(link => {
      link.classList.toggle('active', link.dataset.memberNav === activeNav);
    });
    if (shouldScroll) memberWelcome.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  if (isMemberPage) {
    const profileToggle = document.getElementById('memberProfileToggle');
    const profileMenu = document.getElementById('memberProfileMenu');
    updateMemberProfile(window.pixmoreeAccount ?? {});
    renderMemberDashboard();
    updatePixelCoinBalance();
    refreshPixelCoinWallet().catch(error => {
      showPixelCoinWalletError(error, 'Could not load your Pixel Coin wallet.');
    });
    window.addEventListener('focus', () => {
      refreshPixelCoinWallet().catch(error => {
        showPixelCoinWalletError(error, 'Could not refresh your Pixel Coin wallet.');
      });
    });
    profileToggle.addEventListener('click', () => {
      const isExpanded = profileToggle.getAttribute('aria-expanded') === 'true';
      profileToggle.setAttribute('aria-expanded', String(!isExpanded));
      profileMenu.hidden = isExpanded;
    });
    document.addEventListener('click', event => {
      if (!memberProfile.contains(event.target)) {
        profileMenu.hidden = true;
        profileToggle.setAttribute('aria-expanded', 'false');
      }
    });
    document.querySelectorAll('[data-member-nav]').forEach(link => {
      const activeNav = memberSection === 'me' ? 'collection' : memberSection === 'profile' ? 'profile-settings' : memberSection;
      link.classList.toggle('active', link.dataset.memberNav === activeNav);
    });
    showMemberPanel(memberSection, false);
    const collectionTabButtons = [...document.querySelectorAll('[data-collection-tab]')];
    const collectionTabPanels = [...document.querySelectorAll('.member-collection-tab-panel')];
    function showCollectionTab(tabName) {
      const selectedTab = ['uploads', 'owned', 'favorites'].includes(tabName) ? tabName : 'owned';
      collectionTabButtons.forEach(button => {
        const isSelected = button.dataset.collectionTab === selectedTab;
        button.classList.toggle('active', isSelected);
        button.setAttribute('aria-selected', String(isSelected));
        button.tabIndex = isSelected ? 0 : -1;
      });
      collectionTabPanels.forEach(panel => {
        panel.hidden = panel.id !== `memberCollection${selectedTab.charAt(0).toUpperCase()}${selectedTab.slice(1)}Panel`;
      });
    }
    showCollectionTab(requestedCollectionTab);
    collectionTabButtons.forEach(button => {
      button.addEventListener('click', () => showCollectionTab(button.dataset.collectionTab));
    });
    document.querySelectorAll('[data-member-action]').forEach(link => {
      link.addEventListener('click', () => {
        profileMenu.hidden = true;
        profileToggle.setAttribute('aria-expanded', 'false');
      });
    });
    document.querySelectorAll('[data-member-panel-link]').forEach(button => {
      button.addEventListener('click', () => {
        const panel = button.dataset.memberPanelLink;
        const routes = {
          dashboard: '/user/me',
          collection: '/user/me?tab=owned',
          favorites: '/user/me?tab=favorites',
          history: '/user/purchases',
          profile: '/user/profile',
          create: '/user/create'
        };
        window.location.href = routes[panel] || '/user';
      });
    });
    const artworkFile = document.getElementById('memberArtworkFile');
    const artworkPreview = document.getElementById('memberArtworkPreview');
    const createFeedback = document.getElementById('memberCreateFeedback');
    artworkFile.addEventListener('change', () => {
      const file = artworkFile.files?.[0];
      artworkPreview.hidden = true;
      if (!file) return;
      if (!['image/png', 'image/jpeg', 'image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024) {
        createFeedback.textContent = 'Choose a PNG, JPG, or WEBP image up to 2 MB.';
        createFeedback.hidden = false;
        artworkFile.value = '';
        return;
      }
      const reader = new FileReader();
      reader.addEventListener('load', () => {
        if (typeof reader.result !== 'string') return;
        artworkPreview.src = reader.result;
        artworkPreview.hidden = false;
        createFeedback.hidden = true;
      });
      reader.addEventListener('error', () => {
        createFeedback.textContent = 'Could not read this image. Please choose another file.';
        createFeedback.hidden = false;
      });
      reader.readAsDataURL(file);
    });
  }

  function getFavoriteFromButton(button) {
    const card = button.closest('.art-column');
    const image = card?.querySelector('.art-image-wrap img');
    if (!card || !image) return null;
    const title = card.querySelector('.artwork-title')?.textContent.trim() || image.alt || 'Untitled artwork';
    const imagePath = image.getAttribute('src');
    return {
      id: card.dataset.artworkId || `${imagePath}|${title}`,
      title,
      creator: card.querySelector('.art-creator span')?.textContent.trim() || 'Pixmoree creator',
      image: imagePath,
      alt: image.alt || title,
      price: card.querySelector('.art-price strong')?.textContent.trim() || 'Digital art'
    };
  }

  function getArtworkFromSource(source) {
    if (!source) return;
    const image = source.querySelector('.art-image-wrap img, .trending-feature-slide img, img');
    if (!image) return;

    const title = source.querySelector('.artwork-title')?.textContent.trim() ||
      source.dataset.title ||
      source.querySelector('.trending-nft-item strong')?.textContent.trim() ||
      image.alt;
    const creator = source.querySelector('.art-creator')?.textContent.replace(/^By\s*/, '').trim() ||
      source.dataset.creator ||
      source.querySelector('.trending-nft-item small')?.textContent.replace(/^By\s*/, '').trim();
    const price = source.querySelector('.art-price strong')?.textContent.trim() ||
      source.dataset.floor ||
      source.querySelector('.trending-nft-item>b')?.textContent.trim();
    const items = source.querySelector('.art-items strong')?.textContent.trim() || source.dataset.items;
    const rank = source.querySelector('.trending-nft-rank')?.textContent.trim();

    const artwork = source.classList.contains('art-column') ? getFavoriteFromButton(source.querySelector('.heart-button')) : null;
    return {
      ...(artwork || {}),
      id: artwork?.id || source.dataset.artworkId || `${image.getAttribute('src')}|${title}`,
      title,
      creator: creator || 'Pixmoree creator',
      image: image.getAttribute('src'),
      alt: image.alt || title,
      price: price || 'Digital artwork',
      items,
      rank,
      category: source.dataset.category || 'Digital art'
    };
  }

  function navigateToNftDetail(artwork) {
    if (typeof artwork?.id !== 'string' || artwork.id === '') {
      openStoredArtworkPreview(artwork);
      return;
    }

    window.location.href = `/week12/artworks/${encodeURIComponent(artwork.id)}`;
  }

  function openStoredArtworkPreview(artwork) {
    if (!nftPreview || !artwork) return;
    nftPreviewImage.src = artwork.image;
    nftPreviewImage.alt = artwork.alt || artwork.title;
    nftPreviewTitle.textContent = artwork.title;
    nftPreviewCreator.textContent = artwork.creator ? `By ${artwork.creator}` : '';
    nftPreviewCreator.hidden = !artwork.creator;
    document.getElementById('nftPreviewRankRow').hidden = !artwork.rank;
    document.getElementById('nftPreviewPriceRow').hidden = !artwork.price;
    document.getElementById('nftPreviewItemsRow').hidden = !artwork.items;
    document.getElementById('nftPreviewRank').textContent = artwork.rank || '';
    document.getElementById('nftPreviewPrice').textContent = artwork.price || '';
    document.getElementById('nftPreviewItems').textContent = artwork.items || '';
    const fullDetailsLink = document.getElementById('nftPreviewFullDetails');
    if (fullDetailsLink) {
      fullDetailsLink.hidden = typeof artwork.id !== 'string';
      if (typeof artwork.id === 'string') {
        fullDetailsLink.href = `/week12/artworks/${encodeURIComponent(artwork.id)}`;
      }
    }
    const engagementPanel = document.getElementById('nftPreviewEngagement');
    if (engagementPanel && typeof artwork.id === 'string') {
      engagementPanel.dataset.entityId = artwork.id;
      const likeButton = engagementPanel.querySelector('[data-engagement-like]');
      if (likeButton) likeButton.dataset.entityId = artwork.id;
      window.dispatchEvent(new CustomEvent('pixmoree:artwork-view', { detail: { id: artwork.id } }));
    }

    nftPreview.showModal();
  }

  document.addEventListener('click', event => {
    if (!(event.target instanceof Element) || event.target.closest('.heart-button, .art-engagement-like, .trending-feature-arrow, .trending-nft-pagination, [data-engagement-like], [data-engagement-follow]')) return;
    const feature = event.target.closest('.trending-feature');
    const source = event.target.closest('.art-column, .trending-nft-item, .trending-feature-slide.active') ||
      feature?.querySelector('.trending-feature-slide.active');
    if (!source) return;
    if (source.matches('.trending-nft-item')) event.preventDefault();
    const artwork = getArtworkFromSource(source);
    if (artwork) {
      if (isMemberPage) navigateToNftDetail(artwork);
      else openStoredArtworkPreview(artwork);
    }
  });

  document.querySelectorAll('.art-image-wrap img').forEach(image => {
    image.tabIndex = 0;
    image.setAttribute('role', 'button');
    image.setAttribute('aria-label', `View ${image.alt || 'NFT'}`);
  });
  trendingSlides.forEach(slide => {
    slide.setAttribute('role', 'button');
    slide.tabIndex = slide.classList.contains('active') ? 0 : -1;
    slide.setAttribute('aria-label', `View ${slide.dataset.title} NFT`);
  });
  document.addEventListener('keydown', event => {
    if (!(event.target instanceof Element) || !event.target.matches('.art-image-wrap img, .trending-feature-slide.active')) return;
    if (event.key !== 'Enter' && event.key !== ' ') return;
    event.preventDefault();
    const artwork = getArtworkFromSource(event.target.closest('.art-column, .trending-feature-slide'));
    if (artwork) {
      if (isMemberPage) navigateToNftDetail(artwork);
      else openStoredArtworkPreview(artwork);
    }
  });
  nftPreview?.querySelector('.nft-preview-close')?.addEventListener('click', () => nftPreview.close());
  nftPreview?.addEventListener('click', event => {
    if (event.target === nftPreview) nftPreview.close();
  });
  nftPreview?.querySelector('.nft-preview-explore')?.addEventListener('click', () => nftPreview.close());
  nftPreview?.addEventListener('close', scheduleTrendingAutoplay);
  function updateCreatorCarousel() {
    if (!creatorTrack) return;
    const maxScroll = Math.max(0, creatorTrack.scrollWidth - creatorTrack.clientWidth);
    const pageWidth = creatorTrack.clientWidth || 1;
    const page = maxScroll > 0 ? Math.min(creatorPagination.length - 1, Math.round(creatorTrack.scrollLeft / pageWidth)) : 0;
    creatorPagination.forEach((indicator, index) => indicator.classList.toggle('active', index === page));
  }

  creatorTrack?.addEventListener('scroll', updateCreatorCarousel, { passive: true });
  creatorArrowPrev?.addEventListener('click', () => {
    if (!creatorTrack) return;
    const atStart = creatorTrack.scrollLeft <= 1;
    creatorTrack.scrollTo({
      left: atStart ? creatorTrack.scrollWidth : creatorTrack.scrollLeft - creatorTrack.clientWidth,
      behavior: 'smooth'
    });
  });
  creatorArrowNext?.addEventListener('click', () => {
    if (!creatorTrack) return;
    const atEnd = creatorTrack.scrollLeft >= creatorTrack.scrollWidth - creatorTrack.clientWidth - 1;
    creatorTrack.scrollTo({
      left: atEnd ? 0 : creatorTrack.scrollLeft + creatorTrack.clientWidth,
      behavior: 'smooth'
    });
  });
  window.addEventListener('resize', updateCreatorCarousel);
  updateCreatorCarousel();

  updateGallery();
});
