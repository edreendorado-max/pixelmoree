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
  const favoritesStorageKey = 'pixmoree.user.favorites.v1';
  const collectionStorageKey = 'pixmoree.user.collection.v1';
  const historyStorageKey = 'pixmoree.user.purchase-history.v1';
  const recentStorageKey = 'pixmoree.user.recent.v1';
  const createdArtStorageKey = 'pixmoree.user.created-art.v1';
  const nftDetailStoragePrefix = 'pixmoree.nft.detail.v1.';
  const pendingCheckoutStorageKey = 'pixmoree.user.pending-checkout.v1';
  const pixelCoinBalanceStorageKey = 'pixmoree.user.pixel-coin-balance.v1';
  const pixelCoinActivityStorageKey = 'pixmoree.user.pixel-coin-activity.v1';
  const initialPixelCoinBalance = 2500;
  const demoPixelCoinPerEth = 1000;
  const notificationReadStorageKey = 'pixmoree.user.notifications-read.v1';
  const memberPath = window.location.pathname.replace(/\/+$/, '');
  const nftDetailMatch = window.location.pathname.match(/^\/(?:user\/)?nft\/([a-z0-9][a-z0-9-]*)\/?$/i);
  const isNftDetailPage = Boolean(nftDetailMatch);
  const isMemberPage = memberPath === '/user' || memberPath.startsWith('/user/');
  const requestedMemberSection = isMemberPage && memberPath !== '/user' ? memberPath.split('/').pop() : 'home';
  const memberSection = requestedMemberSection === 'purchases' ? 'history'
    : requestedMemberSection === 'nft-management' || requestedMemberSection === 'dashboard' ? 'dashboard'
      : requestedMemberSection === 'settings' || requestedMemberSection === 'profile-settings' ? 'profile'
        : requestedMemberSection === 'favorites' ? 'collection'
      : requestedMemberSection;
  const requestedCollectionTab = requestedMemberSection === 'favorites'
    ? 'favorites'
    : new URLSearchParams(window.location.search).get('tab');
  const isMemberAccountPage = isMemberPage && memberPath !== '/user' && !isNftDetailPage;
  const memberProfile = document.getElementById('memberProfile');
  const memberWelcome = document.getElementById('memberWelcome');
  const memberPanels = [...document.querySelectorAll('[data-member-panel-content]')];
  let currentPreviewArtwork = null;
  let activeFilter = 'all';
  let activeTrendingSlide = 0;
  let visibleGalleryLimit = 8;
  let trendingAutoplayTimer;
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (memberPath === '/user/payment') {
    let pendingCheckout = null;
    try {
      pendingCheckout = window.localStorage.getItem(pendingCheckoutStorageKey);
    } catch (error) {
      console.error('Could not verify the Pixmoree pending checkout in this browser.', error);
    }
    if (!pendingCheckout) {
      window.location.replace('/user');
      return;
    }
  }

  if (isMemberPage) {
    document.body.classList.add('member-area');
    if (isMemberAccountPage) document.body.classList.add('member-account-page');
    document.title = isMemberAccountPage
      ? `${memberSection === 'history' ? 'Purchases' : memberSection === 'payment' ? 'Payment & Transactions' : memberSection === 'terms' ? 'Terms & Originality' : memberSection === 'collection' ? 'My Collection' : memberSection === 'create' ? 'Create' : memberSection === 'profile' ? 'Profile Settings' : memberSection === 'notifications' ? 'Notifications' : memberSection === 'about' ? 'About Pixmoree' : 'Dashboard'} — Pixmoree`
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
    collectionLink.href = '/user/collection';
    collectionLink.querySelector('span').textContent = 'My Collection';
    const dashboardLink = document.querySelector('[data-member-nav="dashboard"]');
    dashboardLink.href = '/user/dashboard';
    document.querySelector('[data-member-nav="profile-settings"]').href = '/user/profile-settings';
    document.querySelector('[data-member-nav="about"]').href = '/user/about';
    document.querySelector('[data-member-nav="terms"]').href = '/user/terms';
    document.querySelector('.member-notification-button').hidden = false;
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
      const matchesFavorites = activeFilter !== 'favorites' || card.querySelector('.heart-button')?.classList.contains('liked');
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

  if (isMemberPage) {
    document.getElementById('artGrid')?.addEventListener('click', event => {
      if (!(event.target instanceof Element)) return;
      const button = event.target.closest('[data-artwork-purchase]');
      if (!button) return;
      const card = button.closest('.art-column');
      const favoriteButton = card?.querySelector('.heart-button');
      const artwork = favoriteButton ? getFavoriteFromButton(favoriteButton) : null;
      if (artwork) navigateToPayment(artwork);
    });
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

  function readSavedFavorites() {
    try {
      const raw = window.localStorage.getItem(favoritesStorageKey);
      if (!raw) return [];
      const favorites = JSON.parse(raw);
      if (!Array.isArray(favorites)) throw new TypeError('Expected an array of saved artwork.');
      return favorites.filter(item => item && typeof item.id === 'string');
    } catch (error) {
      console.error('Could not read Pixmoree saved artwork from this browser.', error);
      return [];
    }
  }

  function updateMemberFavoriteCount() {
    if (!isMemberPage) return;
    const count = readSavedFavorites().length;
    document.getElementById('memberWelcomeFavorites').textContent = String(count);
    document.getElementById('memberHomeFavoriteCount').textContent = String(count);
    document.getElementById('memberFavoritePanelCount').textContent = String(count);
  }

  function updateMemberProfile(profile) {
    const name = typeof profile.name === 'string' && profile.name.trim() ? profile.name.trim() : 'Account';
    document.getElementById('memberNavName').textContent = name;
    document.querySelector('#memberNavAvatar .member-avatar-initial').textContent = name.charAt(0).toUpperCase();
    document.getElementById('memberWelcomeName').textContent = name.split(/\s+/)[0];
    document.getElementById('memberDashboardProfileName').textContent = name;
    document.getElementById('memberDashboardUsername').textContent = `@${name.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g) || 'pixmoree-user'}`;
    document.querySelector('#memberDashboardAvatar .member-avatar-initial').textContent = name.charAt(0).toUpperCase();
  }

  const defaultDemoCollection = [
    { id: 'demo-samurai', title: 'SAMURAI #8423', creator: 'cosmic.art', image: '/public/assets/anime-style-character-space (1).jpg', alt: 'Samurai in a dreamlike landscape', price: '1.85 ETH', date: '2026-10-03', status: 'Completed' },
    { id: 'demo-astro', title: 'GOJO SATORU #083', creator: 'cosmic.art', image: '/public/assets/anime-character-using-virtual-reality-glasses-metaverse.jpg', alt: 'Anime character portrait', price: '1.60 ETH', date: '2026-09-28', status: 'Completed' }
  ];

  function readStoredList(key, label) {
    try {
      const raw = window.localStorage.getItem(key);
      if (!raw) return [];
      const value = JSON.parse(raw);
      if (!Array.isArray(value)) throw new TypeError('Expected a list.');
      return value.filter(item => item && typeof item === 'object' && typeof item.id === 'string');
    } catch (error) {
      console.error(`Could not read Pixmoree ${label} from this browser.`, error);
      return [];
    }
  }

  function writeStoredList(key, list, label) {
    try {
      window.localStorage.setItem(key, JSON.stringify(list));
      return true;
    } catch (error) {
      console.error(`Could not save Pixmoree ${label} to this browser.`, error);
      return false;
    }
  }

  function formatMemberDate(date) {
    const parsedDate = new Date(`${date}T00:00:00`);
    return Number.isNaN(parsedDate.valueOf()) ? 'Demo date unavailable' :
      parsedDate.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
  }

  function makeArtworkTile(artwork, panel) {
    const article = document.createElement('article');
    article.className = 'member-art-tile';
    const imageButton = document.createElement('button');
    imageButton.className = 'member-art-tile-image';
    imageButton.type = 'button';
    imageButton.dataset.artworkAction = 'view';
    imageButton.setAttribute('aria-label', `Open ${artwork.title} details`);
    const image = document.createElement('img');
    image.src = artwork.image;
    image.alt = artwork.alt || artwork.title;
    imageButton.append(image);
    const details = document.createElement('div');
    details.className = 'member-art-tile-details';
    const title = document.createElement('strong');
    title.textContent = artwork.title;
    const creator = document.createElement('small');
    creator.textContent = `By ${artwork.creator || 'Pixmoree creator'}`;
    const price = document.createElement('span');
    price.textContent = panel === 'favorites' ? (artwork.price || 'Digital artwork')
      : panel === 'created' ? `Listed at ${artwork.price || 'Demo'}`
        : `Paid ${artwork.price || 'Demo'}`;
    details.append(title, creator, price);
    if (artwork.date) {
      const date = document.createElement('small');
      date.textContent = `${panel === 'recent' ? 'Viewed' : panel === 'created' ? 'Listed' : 'Acquired'} ${formatMemberDate(artwork.date)}`;
      details.append(date);
    }
    const actions = document.createElement('div');
    actions.className = 'member-art-tile-actions';
    const viewButton = document.createElement('button');
    viewButton.type = 'button';
    viewButton.dataset.artworkAction = 'view';
    viewButton.textContent = 'View details';
    actions.append(viewButton);
    if (panel === 'favorites') {
      const removeButton = document.createElement('button');
      removeButton.type = 'button';
      removeButton.dataset.artworkAction = 'remove-favorite';
      removeButton.textContent = 'Remove';
      actions.append(removeButton);
    } else if (panel === 'created') {
      const removeButton = document.createElement('button');
      removeButton.type = 'button';
      removeButton.dataset.artworkAction = 'remove-listing';
      removeButton.textContent = 'Remove listing';
      actions.append(removeButton);
    }
    article.append(imageButton, details, actions);
    article.dataset.artworkId = artwork.id;
    return article;
  }

  function renderMemberDashboard() {
    if (!isMemberPage) return;
    const favorites = readSavedFavorites();
    let collection = readStoredList(collectionStorageKey, 'collection');
    let history = readStoredList(historyStorageKey, 'purchase history');
    const recent = readStoredList(recentStorageKey, 'recently viewed artwork');
    const createdArt = readStoredList(createdArtStorageKey, 'created artwork');
    const soldArtCount = createdArt.filter(item => item.sold === true || Number(item.soldCount) > 0 || /^sold\b/i.test(item.status || '')).length;
    document.getElementById('memberWelcomeFavorites').textContent = String(favorites.length);
    document.getElementById('memberFavoritePanelCount').textContent = String(favorites.length);
    document.getElementById('memberOwnedCount').textContent = String(collection.length);
    document.getElementById('memberCollectionCount').textContent = String(collection.length);
    document.getElementById('memberMenuCollectionCount').textContent = String(collection.length);
    document.getElementById('memberPurchaseCount').textContent = String(history.length);
    document.getElementById('memberHomeOwnedCount').textContent = String(collection.length);
    document.getElementById('memberHomePurchaseCount').textContent = String(history.length);
    document.getElementById('memberHomeFavoriteCount').textContent = String(favorites.length);
    document.getElementById('memberDashboardLikes').textContent = String(favorites.length);
    document.getElementById('memberDashboardSold').textContent = String(soldArtCount);

    const collectionItems = document.getElementById('memberCollectionItems');
    collectionItems.replaceChildren(...(collection.length
      ? collection.map(item => makeArtworkTile(item, 'collection'))
      : [Object.assign(document.createElement('p'), { className: 'member-empty-state', textContent: 'Your collection is empty. Explore NFTs and add a demo purchase.' })]));
    const favoriteItems = document.getElementById('memberFavoriteItems');
    favoriteItems.replaceChildren(...(favorites.length
      ? favorites.map(item => makeArtworkTile(item, 'favorites'))
      : [Object.assign(document.createElement('p'), { className: 'member-empty-state', textContent: 'No favorites yet. Save artwork with the heart button while exploring.' })]));
    const collectionUploads = document.getElementById('memberCollectionUploads');
    collectionUploads.replaceChildren(...(createdArt.length
      ? createdArt.map(item => makeArtworkTile(item, 'created'))
      : [Object.assign(document.createElement('p'), { className: 'member-empty-state', textContent: 'You have not uploaded any artwork yet. Start creating to add your first upload.' })]));
    const galleryArt = [...new Map([...collection, ...createdArt].map(item => [item.id, item])).values()];
    const dashboardGallery = document.getElementById('memberDashboardGallery');
    dashboardGallery.replaceChildren(...(galleryArt.length
      ? galleryArt.map(item => makeArtworkTile(item, createdArt.some(created => created.id === item.id) ? 'created' : 'collection'))
      : [Object.assign(document.createElement('p'), { className: 'member-empty-state', textContent: 'Your gallery is empty. Create or collect artwork to see it here.' })]));
    const recentItems = document.getElementById('memberRecentItems');
    const recentTiles = recent.length
      ? recent.slice(0, 4).map(item => makeArtworkTile(item, 'recent'))
      : [Object.assign(document.createElement('p'), { className: 'member-empty-state', textContent: 'Artwork you view will show up here.' })];
    recentItems.replaceChildren(...recentTiles);
    const historyItems = document.getElementById('memberHistoryItems');
    if (!history.length) {
      const emptyRow = document.createElement('tr');
      const emptyCell = document.createElement('td');
      emptyCell.colSpan = 4;
      emptyCell.className = 'member-empty-state';
      emptyCell.textContent = 'No demo purchases yet.';
      emptyRow.append(emptyCell);
      historyItems.replaceChildren(emptyRow);
    } else {
      historyItems.replaceChildren(...history.map(item => {
        const row = document.createElement('tr');
        [item.title, item.price, formatMemberDate(item.date)].forEach(value => {
          const cell = document.createElement('td');
          cell.textContent = value;
          row.append(cell);
        });
        const statusCell = document.createElement('td');
        const status = document.createElement('span');
        status.className = 'member-transaction-status';
        status.textContent = `${item.status || 'Completed'} · Demo`;
        statusCell.append(status);
        row.append(statusCell);
        return row;
      }));
    }
    renderPaymentPanel(collection);
    renderMemberNotifications();
  }

  function readPendingCheckout() {
    try {
      const stored = window.localStorage.getItem(pendingCheckoutStorageKey);
      if (!stored) return null;
      const artwork = JSON.parse(stored);
      if (!artwork || typeof artwork.id !== 'string' || typeof artwork.title !== 'string' ||
          typeof artwork.image !== 'string') {
        throw new TypeError('Expected an artwork for checkout.');
      }
      return artwork;
    } catch (error) {
      console.error('Could not read Pixmoree checkout details from this browser.', error);
      return null;
    }
  }

  function renderPaymentPanel(collection = readStoredList(collectionStorageKey, 'collection')) {
    const checkout = document.getElementById('memberCheckoutArtwork');
    const submit = document.getElementById('memberPaymentSubmit');
    const paymentTerms = document.getElementById('memberPaymentTerms');
    if (checkout && submit && paymentTerms) {
      const artwork = readPendingCheckout();
      if (!artwork) {
        checkout.replaceChildren();
        checkout.hidden = true;
        document.getElementById('memberCheckoutPrice').textContent = '—';
        document.getElementById('memberCheckoutTotal').textContent = '—';
        document.getElementById('memberPixelRateNote').hidden = true;
        document.getElementById('memberPixelInsufficient').hidden = true;
        submit.disabled = true;
        submit.textContent = 'No active order';
      } else {
        const summary = document.createElement('div');
        summary.className = 'member-checkout-summary';
        const image = document.createElement('img');
        image.src = artwork.image;
        image.alt = artwork.alt || artwork.title;
        const details = document.createElement('div');
        const title = document.createElement('strong');
        title.textContent = artwork.title;
        const creator = document.createElement('small');
        creator.textContent = `By ${artwork.creator || 'Pixmoree creator'}`;
        const price = document.createElement('span');
        price.textContent = artwork.price || 'Price unavailable';
        details.append(title, creator, price);
        summary.append(image, details);
        checkout.replaceChildren(summary);
        checkout.hidden = false;
        document.getElementById('memberCheckoutPrice').textContent = artwork.price || 'Price unavailable';
        const alreadyOwned = collection.some(item => item.id === artwork.id);
        updatePixelCoinCheckout(artwork, alreadyOwned, paymentTerms.checked);
      }
    }
    updatePixelCoinBalance();
    renderPixelCoinActivity();

    const transactionItems = document.getElementById('memberTransactionItems');
    if (!transactionItems) return;
    const history = readStoredList(historyStorageKey, 'purchase history');
    if (!history.length) {
      const row = document.createElement('tr');
      const cell = document.createElement('td');
      cell.colSpan = 6;
      cell.className = 'member-empty-state';
      cell.textContent = 'No demo transactions yet.';
      row.append(cell);
      transactionItems.replaceChildren(row);
      return;
    }
    transactionItems.replaceChildren(...history.map(item => {
      const row = document.createElement('tr');
      [item.transactionId || 'Demo record', item.title,
        item.pixelCoinAmount ? `${item.pixelCoinAmount.toLocaleString()} PIXEL COIN` : item.price || 'Demo',
        item.paymentMethod || 'Demo', formatMemberDate(item.date)].forEach(value => {
        const cell = document.createElement('td');
        cell.textContent = value;
        row.append(cell);
      });
      const statusCell = document.createElement('td');
      const status = document.createElement('span');
      status.className = 'member-transaction-status';
      status.textContent = `${item.status || 'Completed'} · Demo`;
      statusCell.append(status);
      row.append(statusCell);
      return row;
    }));
  }

  function parseEthPrice(price) {
    const match = typeof price === 'string' && price.match(/([\d,]+(?:\.\d+)?)\s*ETH/i);
    if (!match) return null;
    const value = Number(match[1].replace(/,/g, ''));
    return Number.isFinite(value) && value > 0 ? value : null;
  }

  function getPixelCoinBalance() {
    try {
      const stored = window.localStorage.getItem(pixelCoinBalanceStorageKey);
      if (stored === null) return initialPixelCoinBalance;
      const balance = Number(stored);
      return Number.isSafeInteger(balance) && balance >= 0 ? balance : initialPixelCoinBalance;
    } catch (error) {
      console.error('Could not read the Pixmoree Pixel Coin balance in this browser.', error);
      return 0;
    }
  }

  function updatePixelCoinBalance() {
    const balance = document.getElementById('memberPixelCoinBalance');
    if (balance) balance.textContent = getPixelCoinBalance().toLocaleString();
  }

  function updatePixelCoinCheckout(artwork, alreadyOwned, termsAccepted) {
    const method = document.getElementById('memberPaymentMethod').value;
    const total = document.getElementById('memberCheckoutTotal');
    const rateNote = document.getElementById('memberPixelRateNote');
    const insufficient = document.getElementById('memberPixelInsufficient');
    const submit = document.getElementById('memberPaymentSubmit');
    const feedback = document.getElementById('memberPaymentFeedback');
    const ethAmount = parseEthPrice(artwork.price);
    const coinCost = ethAmount === null ? null : Math.ceil(ethAmount * demoPixelCoinPerEth);
    const isPixelCoin = method === 'Pixel Coin (Demo)';
    rateNote.hidden = !isPixelCoin;
    insufficient.hidden = true;
    total.textContent = isPixelCoin
      ? coinCost === null ? 'Unavailable' : `${coinCost.toLocaleString()} PIXEL COIN`
      : artwork.price || 'Price unavailable';
    if (alreadyOwned) {
      submit.disabled = true;
      submit.innerHTML = '<i class="bi bi-check-circle"></i> Already in My Collection';
      return;
    }
    if (isPixelCoin && (coinCost === null || getPixelCoinBalance() < coinCost)) {
      submit.disabled = true;
      submit.innerHTML = '<i class="bi bi-coin"></i> Top up Pixel Coin to continue';
      insufficient.textContent = coinCost === null
        ? 'This listing does not have a supported ETH demo price for Pixel Coin checkout.'
        : `You need ${coinCost.toLocaleString()} PIXEL COIN, but your demo balance is ${getPixelCoinBalance().toLocaleString()}. Add demo coins above to continue.`;
      insufficient.hidden = false;
      return;
    }
    submit.disabled = !termsAccepted;
    submit.innerHTML = termsAccepted
      ? '<i class="bi bi-lock"></i> Confirm demo purchase'
      : '<i class="bi bi-lock"></i> Review order to continue';
    if (feedback.textContent.includes('Top up')) feedback.hidden = true;
  }

  function readPixelCoinActivity() {
    return readStoredList(pixelCoinActivityStorageKey, 'Pixel Coin activity');
  }

  function renderPixelCoinActivity() {
    const target = document.getElementById('memberPixelCoinActivity');
    if (!target) return;
    const activity = readPixelCoinActivity().slice(0, 4);
    if (!activity.length) {
      target.replaceChildren();
      return;
    }
    const heading = document.createElement('strong');
    heading.textContent = 'Recent demo top-ups';
    const list = document.createElement('ul');
    activity.forEach(item => {
      const row = document.createElement('li');
      const amount = document.createElement('span');
      amount.textContent = `+${Number(item.amount).toLocaleString()} PIXEL COIN`;
      const date = document.createElement('small');
      date.textContent = formatMemberDate(item.date);
      row.append(amount, date);
      list.append(row);
    });
    target.replaceChildren(heading, list);
  }

  document.querySelectorAll('[data-pixel-topup]').forEach(button => {
    button.addEventListener('click', () => {
      const amount = Number(button.dataset.pixelTopup);
      if (!Number.isSafeInteger(amount) || amount <= 0) {
        console.error('Ignored an invalid Pixel Coin demo top-up amount.');
        return;
      }
      const currentBalance = getPixelCoinBalance();
      const nextBalance = currentBalance + amount;
      const message = document.getElementById('memberPixelWalletFeedback');
      if (!Number.isSafeInteger(nextBalance)) {
        message.textContent = 'The demo balance limit has been reached.';
        message.hidden = false;
        return;
      }
      const activity = readPixelCoinActivity();
      const topUp = {
        id: `pixel-topup-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`,
        amount,
        date: new Date().toISOString().slice(0, 10)
      };
      try {
        window.localStorage.setItem(pixelCoinBalanceStorageKey, String(nextBalance));
        window.localStorage.setItem(pixelCoinActivityStorageKey, JSON.stringify([topUp, ...activity].slice(0, 20)));
      } catch (error) {
        console.error('Could not save Pixmoree Pixel Coin demo top-up.', error);
        try {
          window.localStorage.setItem(pixelCoinBalanceStorageKey, String(currentBalance));
        } catch (restoreError) {
          console.error('Could not restore the Pixmoree Pixel Coin balance after an incomplete demo top-up.', restoreError);
        }
        message.textContent = 'Could not save this demo top-up. Check browser storage settings and try again.';
        message.hidden = false;
        return;
      }
      message.textContent = `${amount.toLocaleString()} demo PIXEL COIN added. No payment was made.`;
      message.hidden = false;
      updatePixelCoinBalance();
      renderPixelCoinActivity();
      renderPaymentPanel();
    });
  });

  document.getElementById('memberPaymentMethod')?.addEventListener('change', () => {
    const artwork = readPendingCheckout();
    if (artwork) updatePixelCoinCheckout(
      artwork,
      readStoredList(collectionStorageKey, 'collection').some(item => item.id === artwork.id),
      document.getElementById('memberPaymentTerms').checked
    );
  });

  document.getElementById('memberPaymentTerms')?.addEventListener('change', () => {
    const artwork = readPendingCheckout();
    if (artwork) updatePixelCoinCheckout(
      artwork,
      readStoredList(collectionStorageKey, 'collection').some(item => item.id === artwork.id),
      document.getElementById('memberPaymentTerms').checked
    );
  });

  function renderMemberNotifications() {
    const notifications = [
      ...readStoredList(historyStorageKey, 'purchase history').map(item => ({
        id: `purchase:${item.id}`,
        icon: 'bi-bag-check',
        title: `Purchase recorded: ${item.title}`,
        detail: `${item.price || 'Demo purchase'} · ${formatMemberDate(item.date)}`,
        date: item.date
      })),
      ...readStoredList(createdArtStorageKey, 'created artwork').map(item => ({
        id: `listing:${item.id}`,
        icon: 'bi-stars',
        title: `Your artwork is listed: ${item.title}`,
        detail: `Demo listing · ${formatMemberDate(item.date)}`,
        date: item.date
      }))
    ].sort((a, b) => (b.date || '').localeCompare(a.date || '')).slice(0, 12);
    let readIds = [];
    try {
      const stored = JSON.parse(window.localStorage.getItem(notificationReadStorageKey) || '[]');
      if (!Array.isArray(stored) || stored.some(id => typeof id !== 'string')) {
        throw new TypeError('Expected a list of notification identifiers.');
      }
      readIds = stored;
    } catch (error) {
      console.error('Could not read Pixmoree notification state from this browser.', error);
    }
    const unread = notifications.filter(item => !readIds.includes(item.id)).length;
    const sidebarBadge = document.getElementById('memberNotificationBadge');
    const topBadge = document.getElementById('memberTopNotificationBadge');
    sidebarBadge.textContent = String(unread);
    topBadge.textContent = String(unread);
    sidebarBadge.hidden = unread === 0;
    topBadge.hidden = unread === 0;
    const list = document.getElementById('memberNotificationsList');
    if (!notifications.length) {
      const empty = document.createElement('p');
      empty.className = 'member-empty-state';
      empty.textContent = 'You are all caught up. New purchases and listings will appear here.';
      list.replaceChildren(empty);
      return;
    }
    list.replaceChildren(...notifications.map(item => {
      const article = document.createElement('article');
      article.className = `member-notification-item${readIds.includes(item.id) ? ' is-read' : ''}`;
      const icon = document.createElement('span');
      icon.className = 'member-notification-icon';
      const iconGlyph = document.createElement('i');
      iconGlyph.className = `bi ${item.icon}`;
      icon.append(iconGlyph);
      const copy = document.createElement('span');
      copy.className = 'member-notification-copy';
      const title = document.createElement('strong');
      title.textContent = item.title;
      const detail = document.createElement('small');
      detail.textContent = item.detail;
      copy.append(title, detail);
      const markRead = document.createElement('button');
      markRead.type = 'button';
      markRead.className = 'member-notification-mark';
      markRead.dataset.notificationId = item.id;
      markRead.textContent = readIds.includes(item.id) ? 'Read' : 'Mark read';
      markRead.disabled = readIds.includes(item.id);
      article.append(icon, copy, markRead);
      return article;
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
    const activeNav = panelName === 'profile' ? 'profile-settings' : panelName;
    document.querySelectorAll('[data-member-nav]').forEach(link => {
      link.classList.toggle('active', link.dataset.memberNav === activeNav);
    });
    if (shouldScroll) memberWelcome.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  if (isMemberPage) {
    const profileToggle = document.getElementById('memberProfileToggle');
    const profileMenu = document.getElementById('memberProfileMenu');
    updateMemberProfile(window.pixmoreeAccount ?? {});
    try {
      if (window.localStorage.getItem(collectionStorageKey) === null) {
        writeStoredList(collectionStorageKey, defaultDemoCollection, 'collection');
      }
      if (window.localStorage.getItem(historyStorageKey) === null) {
        const initialCollection = readStoredList(collectionStorageKey, 'collection');
        writeStoredList(historyStorageKey, initialCollection.map(item => ({ ...item })), 'purchase history');
      }
      if (window.localStorage.getItem(pixelCoinBalanceStorageKey) === null) {
        window.localStorage.setItem(pixelCoinBalanceStorageKey, String(initialPixelCoinBalance));
      }
    } catch (error) {
      console.error('Could not initialize Pixmoree demo collection data in this browser.', error);
    }
    renderMemberDashboard();
    updatePixelCoinBalance();
    if (!isMemberAccountPage) {
      document.querySelectorAll('.art-column').forEach(card => {
        const artwork = getFavoriteFromButton(card.querySelector('.heart-button'));
        const body = card.querySelector('.art-card-body');
        if (!artwork || !body || body.querySelector('[data-artwork-purchase]')) return;
        const purchaseButton = document.createElement('button');
        purchaseButton.type = 'button';
        purchaseButton.className = 'member-card-purchase';
        purchaseButton.dataset.artworkPurchase = artwork.id;
        purchaseButton.textContent = 'Buy NFT (demo)';
        const feedback = document.createElement('small');
        feedback.className = 'member-card-purchase-feedback';
        feedback.setAttribute('role', 'status');
        feedback.hidden = true;
        body.append(purchaseButton, feedback);
        updatePurchaseButton(purchaseButton, artwork.id);
      });
    }
    document.getElementById('nftPreviewCollectButton').hidden = false;

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
      const activeNav = memberSection === 'profile' ? 'profile-settings' : memberSection;
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
          dashboard: '/user/dashboard',
          collection: '/user/collection',
          favorites: '/user/collection?tab=favorites',
          history: '/user/purchases',
          profile: '/user/profile',
          create: '/user/create'
        };
        window.location.href = routes[panel] || '/user';
      });
    });
    document.querySelector('.member-dashboard-content').addEventListener('click', event => {
      if (!(event.target instanceof Element)) return;
      const button = event.target.closest('[data-artwork-action]');
      if (!button) return;
      const article = button.closest('.member-art-tile');
      if (!article) return;
      const id = article.dataset.artworkId;
      if (button.dataset.artworkAction === 'remove-favorite') {
        const updatedFavorites = readSavedFavorites().filter(item => item.id !== id);
        if (writeStoredList(favoritesStorageKey, updatedFavorites, 'saved artwork')) {
          const favoriteIds = new Set(updatedFavorites.map(item => item.id));
          document.querySelectorAll('.heart-button').forEach(heart => {
            const favorite = getFavoriteFromButton(heart);
            setFavoriteState(heart, favorite && favoriteIds.has(favorite.id));
          });
          renderMemberDashboard();
          updateGallery();
        }
        return;
      }
      if (button.dataset.artworkAction === 'remove-listing') {
        const remainingListings = readStoredList(createdArtStorageKey, 'created artwork').filter(artwork => artwork.id !== id);
        if (writeStoredList(createdArtStorageKey, remainingListings, 'created artwork')) renderMemberDashboard();
        return;
      }
      const item = [...readSavedFavorites(), ...readStoredList(collectionStorageKey, 'collection'), ...readStoredList(recentStorageKey, 'recently viewed artwork'), ...readStoredList(createdArtStorageKey, 'created artwork')].find(artwork => artwork.id === id);
      if (item) openStoredArtworkPreview(item);
    });
    document.getElementById('memberMarkNotificationsRead').addEventListener('click', () => {
      const ids = [...document.querySelectorAll('.member-notification-mark')].map(button => button.dataset.notificationId);
      try {
        window.localStorage.setItem(notificationReadStorageKey, JSON.stringify(ids));
      } catch (error) {
        console.error('Could not save Pixmoree notification state to this browser.', error);
      }
      renderMemberNotifications();
    });
    document.getElementById('memberNotificationsList').addEventListener('click', event => {
      if (!(event.target instanceof Element)) return;
      const button = event.target.closest('[data-notification-id]');
      if (!button) return;
      let readIds = [];
      try {
        const stored = JSON.parse(window.localStorage.getItem(notificationReadStorageKey) || '[]');
        if (!Array.isArray(stored) || stored.some(id => typeof id !== 'string')) {
          throw new TypeError('Expected a list of notification identifiers.');
        }
        readIds = stored;
        if (!readIds.includes(button.dataset.notificationId)) readIds.push(button.dataset.notificationId);
        window.localStorage.setItem(notificationReadStorageKey, JSON.stringify(readIds));
      } catch (error) {
        console.error('Could not update Pixmoree notification state in this browser.', error);
      }
      renderMemberNotifications();
    });
    document.getElementById('memberPaymentForm').addEventListener('submit', event => {
      event.preventDefault();
      const form = event.currentTarget;
      if (!form.reportValidity()) return;
      const artwork = readPendingCheckout();
      const feedback = document.getElementById('memberPaymentFeedback');
      if (!artwork) {
        feedback.textContent = 'Choose an artwork and review the order before continuing.';
        feedback.hidden = false;
        return;
      }
      const paymentMethod = document.getElementById('memberPaymentMethod').value;
      const completed = purchaseArtwork(artwork, feedback, paymentMethod);
      if (!completed) return;
      document.getElementById('memberPaymentTerms').checked = false;
      renderPaymentPanel();
    });

    const createForm = document.getElementById('memberCreateForm');
    const artworkFile = document.getElementById('memberArtworkFile');
    const artworkPreview = document.getElementById('memberArtworkPreview');
    const createFeedback = document.getElementById('memberCreateFeedback');
    let selectedArtworkImage = '';
    artworkFile.addEventListener('change', () => {
      const file = artworkFile.files?.[0];
      selectedArtworkImage = '';
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
        selectedArtworkImage = reader.result;
        artworkPreview.src = selectedArtworkImage;
        artworkPreview.hidden = false;
        createFeedback.hidden = true;
      });
      reader.addEventListener('error', () => {
        createFeedback.textContent = 'Could not read this image. Please choose another file.';
        createFeedback.hidden = false;
      });
      reader.readAsDataURL(file);
    });
    createForm.addEventListener('submit', event => {
      event.preventDefault();
      if (!createForm.reportValidity()) return;
      const profileName = document.getElementById('memberNavName').textContent.trim();
      const listing = {
        id: `creator-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
        title: document.getElementById('memberArtworkTitle').value.trim(),
        description: document.getElementById('memberArtworkDescription').value.trim(),
        creator: profileName,
        image: selectedArtworkImage || '/public/assets/guest-hero-background.png',
        alt: document.getElementById('memberArtworkTitle').value.trim(),
        price: `${Number(document.getElementById('memberArtworkPrice').value).toFixed(2)} ETH`,
        category: document.getElementById('memberArtworkCategory').value,
        originalityConfirmed: document.getElementById('memberArtworkOriginality').checked,
        date: new Date().toISOString().slice(0, 10),
        status: 'Listed · Demo'
      };
      const listings = readStoredList(createdArtStorageKey, 'created artwork');
      if (!writeStoredList(createdArtStorageKey, [listing, ...listings], 'created artwork')) {
        createFeedback.textContent = 'Could not save your listing. Check available browser storage.';
        createFeedback.hidden = false;
        return;
      }
      createForm.reset();
      selectedArtworkImage = '';
      artworkPreview.hidden = true;
      createFeedback.textContent = 'Your artwork was added to your demo listings. No real sale was created.';
      createFeedback.hidden = false;
      renderMemberDashboard();
    });
  }

  function getFavoriteFromButton(button) {
    const card = button.closest('.art-column');
    const image = card?.querySelector('.art-image-wrap img');
    if (!card || !image) return null;
    const title = card.querySelector('.artwork-title')?.textContent.trim() || image.alt || 'Untitled artwork';
    const imagePath = image.getAttribute('src');
    return {
      id: `${imagePath}|${title}`,
      title,
      creator: card.querySelector('.art-creator span')?.textContent.trim() || 'Pixmoree creator',
      image: imagePath,
      alt: image.alt || title,
      price: card.querySelector('.art-price strong')?.textContent.trim() || 'Digital art'
    };
  }

  function updatePurchaseButton(button, artworkId) {
    const owned = readStoredList(collectionStorageKey, 'collection').some(item => item.id === artworkId);
    button.disabled = owned;
    button.textContent = owned ? 'In My Collection' : 'Buy NFT (demo)';
  }

  function purchaseArtwork(artwork, feedback, paymentMethod = 'Demo checkout') {
    if (!artwork || !isMemberPage) return false;
    const collection = readStoredList(collectionStorageKey, 'collection');
    if (collection.some(item => item.id === artwork.id)) {
      if (feedback) {
        feedback.textContent = 'This NFT is already in your collection.';
        feedback.hidden = false;
      }
      return false;
    }
    const pixelCoinAmount = paymentMethod === 'Pixel Coin (Demo)'
      ? (() => {
        const ethAmount = parseEthPrice(artwork.price);
        return ethAmount === null ? null : Math.ceil(ethAmount * demoPixelCoinPerEth);
      })()
      : null;
    const pixelCoinBalance = paymentMethod === 'Pixel Coin (Demo)' ? getPixelCoinBalance() : null;
    if (paymentMethod === 'Pixel Coin (Demo)' &&
        (pixelCoinAmount === null || pixelCoinBalance === null || pixelCoinBalance < pixelCoinAmount)) {
      if (feedback) {
        feedback.textContent = pixelCoinAmount === null
          ? 'Pixel Coin demo checkout is unavailable because this listing has no supported ETH price.'
          : `Not enough PIXEL COIN. You need ${pixelCoinAmount.toLocaleString()} and have ${pixelCoinBalance.toLocaleString()}. Add demo coins above to continue.`;
        feedback.hidden = false;
      }
      return false;
    }
    const purchase = {
      id: artwork.id,
      title: artwork.title,
      creator: artwork.creator,
      image: artwork.image,
      alt: artwork.alt || artwork.title,
      price: artwork.price || 'Demo purchase',
      date: new Date().toISOString().slice(0, 10),
      transactionId: `PM-DEMO-${Date.now().toString(36).toUpperCase()}-${Math.random().toString(36).slice(2, 6).toUpperCase()}`,
      paymentMethod,
      ...(pixelCoinAmount !== null ? { pixelCoinAmount } : {}),
      status: 'Completed'
    };
    const history = readStoredList(historyStorageKey, 'purchase history');
    if (!writeStoredList(collectionStorageKey, [...collection, purchase], 'collection')) {
      if (feedback) {
        feedback.textContent = 'Unable to complete this demo purchase. Check browser storage settings.';
        feedback.hidden = false;
      }
      return false;
    }
    if (!writeStoredList(historyStorageKey, [purchase, ...history], 'purchase history')) {
      writeStoredList(collectionStorageKey, collection, 'collection rollback');
      if (feedback) {
        feedback.textContent = 'The purchase record could not be saved, so the collection update was rolled back.';
        feedback.hidden = false;
      }
      return false;
    }
    if (pixelCoinAmount !== null && pixelCoinBalance !== null) {
      try {
        window.localStorage.setItem(pixelCoinBalanceStorageKey, String(pixelCoinBalance - pixelCoinAmount));
      } catch (error) {
        console.error('Could not update the Pixmoree Pixel Coin balance for this demo purchase.', error);
        writeStoredList(collectionStorageKey, collection, 'collection rollback');
        writeStoredList(historyStorageKey, history, 'purchase history rollback');
        if (feedback) {
          feedback.textContent = 'Could not save the Pixel Coin payment. No purchase was recorded; check browser storage settings.';
          feedback.hidden = false;
        }
        return false;
      }
    }
    if (feedback) {
      feedback.textContent = 'Demo purchase complete. No payment was processed.';
      feedback.hidden = false;
    }
    renderMemberDashboard();
    document.querySelectorAll('[data-artwork-purchase]').forEach(button => {
      updatePurchaseButton(button, button.dataset.artworkPurchase);
    });
    return true;
  }

  function navigateToPayment(artwork) {
    if (!isMemberPage || !artwork) return;
    try {
      window.localStorage.setItem(pendingCheckoutStorageKey, JSON.stringify(artwork));
      window.location.href = '/user/payment';
    } catch (error) {
      console.error('Could not save Pixmoree checkout details in this browser.', error);
      window.alert('Checkout could not be opened because browser storage is unavailable. Check your browser storage settings and try again.');
    }
  }

  function setFavoriteState(button, isFavorite) {
    isFavorite = Boolean(isFavorite);
    button.classList.toggle('liked', isFavorite);
    button.setAttribute('aria-pressed', String(isFavorite));
    const icon = button.querySelector('i');
    icon.classList.toggle('bi-heart', !isFavorite);
    icon.classList.toggle('bi-heart-fill', isFavorite);
    const favorite = getFavoriteFromButton(button);
    if (favorite) button.setAttribute('aria-label', `${isFavorite ? 'Remove' : 'Add'} ${favorite.title} ${isFavorite ? 'from' : 'to'} favorites`);
  }

  const savedFavoriteIds = new Set(readSavedFavorites().map(item => item.id));
  document.querySelectorAll('.heart-button').forEach(button => {
    const favorite = getFavoriteFromButton(button);
    setFavoriteState(button, favorite && savedFavoriteIds.has(favorite.id));
    button.addEventListener('click', () => {
      const favorite = getFavoriteFromButton(button);
      if (!favorite) return;
      const favorites = readSavedFavorites().filter(item => item.id !== favorite.id);
      const isFavorite = !button.classList.contains('liked');
      if (isFavorite) favorites.push(favorite);
      try {
        window.localStorage.setItem(favoritesStorageKey, JSON.stringify(favorites));
      } catch (error) {
        console.error('Could not save Pixmoree favorites to this browser.', error);
      }
      setFavoriteState(button, isFavorite);
      updateMemberFavoriteCount();
      renderMemberDashboard();
      if (button.closest('#artGrid')) updateGallery();
    });
  });

  window.addEventListener('storage', event => {
    if (event.key !== favoritesStorageKey) return;
    const favoriteIds = new Set(readSavedFavorites().map(item => item.id));
    document.querySelectorAll('.heart-button').forEach(button => {
      const favorite = getFavoriteFromButton(button);
      setFavoriteState(button, favorite && favoriteIds.has(favorite.id));
    });
    updateMemberFavoriteCount();
    renderMemberDashboard();
    updateGallery();
  });

  function openNftPreview(source) {
    const artwork = getArtworkFromSource(source);
    if (artwork) openStoredArtworkPreview(artwork);
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
      id: artwork?.id || `${image.getAttribute('src')}|${title}`,
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

  function getNftSlug(title) {
    return title.toLowerCase().normalize('NFKD').replace(/[\u0300-\u036f]/g, '')
      .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') || 'digital-artwork';
  }

  function saveNftDetails(artwork) {
    if (!isMemberPage) return null;
    let hash = 2166136261;
    for (const character of String(artwork.id || artwork.title || 'digital-artwork')) {
      hash = Math.imul(hash ^ character.charCodeAt(0), 16777619);
    }
    const slug = `${getNftSlug(artwork.title || 'digital-artwork')}-${(hash >>> 0).toString(36)}`;
    const detail = {
      ...artwork,
      slug,
      description: artwork.description || `A digital collectible by ${artwork.creator || 'a Pixmoree creator'}. No additional creator notes are available for this demo listing.`,
      category: artwork.category || 'Digital art',
      collection: artwork.collection || `${artwork.creator || 'Pixmoree'} Collection`,
      token: artwork.token || artwork.title.match(/#\S+/)?.[0] || `PM-${slug.toUpperCase()}`,
      items: artwork.items || '1'
    };
    try {
      window.localStorage.setItem(`${nftDetailStoragePrefix}${slug}`, JSON.stringify(detail));
      return slug;
    } catch (error) {
      console.error('Could not save Pixmoree NFT details in this browser.', error);
      window.alert('NFT details could not be opened because browser storage is unavailable. Check your browser storage settings and try again.');
      return null;
    }
  }

  function navigateToNftDetail(artwork) {
    if (!isMemberPage) {
      openStoredArtworkPreview(artwork);
      return;
    }
    const slug = saveNftDetails(artwork);
    if (!slug) return;
    window.location.href = `/user/nft/${encodeURIComponent(slug)}`;
  }

  function openStoredArtworkPreview(artwork) {
    if (!nftPreview || !artwork) return;
    currentPreviewArtwork = artwork;
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
    const detailSlug = isMemberPage ? saveNftDetails(artwork) : null;
    if (fullDetailsLink) {
      fullDetailsLink.hidden = !isMemberPage || !detailSlug;
      if (detailSlug) fullDetailsLink.href = `/user/nft/${encodeURIComponent(detailSlug)}`;
    }
    const collectButton = document.getElementById('nftPreviewCollectButton');
    if (collectButton) collectButton.hidden = !isMemberPage;
    const collectFeedback = document.getElementById('nftPreviewCollectFeedback');
    collectFeedback.hidden = true;

    if (isMemberPage) {
      const recent = readStoredList(recentStorageKey, 'recently viewed artwork')
        .filter(item => item.id !== artwork.id);
      recent.unshift({
        id: artwork.id,
        title: artwork.title,
        creator: artwork.creator,
        image: artwork.image,
        alt: artwork.alt || artwork.title,
        price: artwork.price,
        date: new Date().toISOString().slice(0, 10)
      });
      if (writeStoredList(recentStorageKey, recent.slice(0, 8), 'recently viewed artwork')) renderMemberDashboard();
    }
    nftPreview.showModal();
  }

  document.getElementById('nftPreviewCollectButton')?.addEventListener('click', () => {
    if (!currentPreviewArtwork) return;
    navigateToPayment(currentPreviewArtwork);
  });

  function initializeNftDetailPage() {
    if (!isNftDetailPage) return;
    document.body.classList.add('nft-detail-page-active');
    const page = document.getElementById('nftDetailPage');
    const layout = document.getElementById('nftDetailLayout');
    const error = document.getElementById('nftDetailError');
    const slug = decodeURIComponent(nftDetailMatch[1]).toLowerCase();
    page.hidden = false;
    if (!isMemberPage) {
      document.title = 'Account required — Pixmoree';
      const backLink = document.getElementById('nftDetailBack');
      backLink.href = '/login';
      backLink.innerHTML = '<i class="bi bi-arrow-left"></i> Log in to view NFT details';
      error.querySelector('h1').textContent = 'Account required';
      error.querySelector('p').textContent = 'Full NFT information is available to account users. Log in or create an account to view complete listing details.';
      const errorLink = document.getElementById('nftDetailErrorLink');
      errorLink.href = '/login';
      errorLink.textContent = 'Log in or create an account';
      error.hidden = false;
      return;
    }
    let artwork = null;
    try {
      const storedArtwork = window.localStorage.getItem(`${nftDetailStoragePrefix}${slug}`);
      artwork = storedArtwork ? JSON.parse(storedArtwork) : null;
    } catch (storageError) {
      console.error('Could not read Pixmoree NFT details from this browser.', storageError);
    }

    document.title = artwork?.title ? `${artwork.title} — Pixmoree` : 'NFT details — Pixmoree';
    const backLink = document.getElementById('nftDetailBack');
    backLink.href = '/user#explore';
    backLink.innerHTML = '<i class="bi bi-arrow-left"></i> Back to Pixmoree';
    if (!artwork || typeof artwork.title !== 'string' || typeof artwork.image !== 'string') {
      document.getElementById('nftDetailErrorLink').href = '/user#explore';
      error.hidden = false;
      return;
    }

    layout.hidden = false;
    document.getElementById('nftDetailImage').src = artwork.image;
    document.getElementById('nftDetailImage').alt = artwork.alt || artwork.title;
    document.getElementById('nftDetailTitle').textContent = artwork.title;
    document.getElementById('nftDetailCreator').textContent = artwork.creator || 'Pixmoree creator';
    document.getElementById('nftDetailDescription').textContent = artwork.description || 'No creator description is available for this demo listing.';
    document.getElementById('nftDetailPrice').textContent = artwork.price || 'Not listed';
    document.getElementById('nftDetailCollection').textContent = artwork.collection || `${artwork.creator || 'Pixmoree'} Collection`;
    document.getElementById('nftDetailCategory').textContent = artwork.category || 'Digital art';
    document.getElementById('nftDetailToken').textContent = artwork.token || artwork.title.match(/#\S+/)?.[0] || `PM-${slug.toUpperCase()}`;
    document.getElementById('nftDetailItems').textContent = artwork.items || '1';
    const rank = document.getElementById('nftDetailRank');
    rank.hidden = !artwork.rank;
    rank.textContent = artwork.rank ? `TRENDING ${artwork.rank}` : '';
    const favoriteId = typeof artwork.id === 'string' ? artwork.id : `${artwork.image}|${artwork.title}`;
    const favoriteButton = document.getElementById('nftDetailFavorite');
    favoriteButton.hidden = false;
    const isFavorite = () => readSavedFavorites().some(item => item.id === favoriteId);
    const updateFavoriteButton = () => {
      const favorite = isFavorite();
      favoriteButton.classList.toggle('is-favorite', favorite);
      favoriteButton.querySelector('i').className = `bi ${favorite ? 'bi-heart-fill' : 'bi-heart'}`;
      favoriteButton.querySelector('span').textContent = favorite ? 'Favorited' : 'Favorite';
      favoriteButton.setAttribute('aria-pressed', String(favorite));
    };
    updateFavoriteButton();
    favoriteButton.addEventListener('click', () => {
      const favorites = readSavedFavorites().filter(item => item.id !== favoriteId);
      if (!isFavorite()) {
        favorites.push({
          id: favoriteId,
          title: artwork.title,
          creator: artwork.creator || 'Pixmoree creator',
          image: artwork.image,
          alt: artwork.alt || artwork.title,
          price: artwork.price || 'Digital artwork'
        });
      }
      try {
        window.localStorage.setItem(favoritesStorageKey, JSON.stringify(favorites));
        updateFavoriteButton();
        updateMemberFavoriteCount();
        if (isMemberPage) renderMemberDashboard();
      } catch (storageError) {
        console.error('Could not save Pixmoree favorites to this browser.', storageError);
        document.getElementById('nftDetailFeedback').textContent = 'Could not save this favorite. Check browser storage settings.';
        document.getElementById('nftDetailFeedback').hidden = false;
      }
    });

    const buyButton = document.getElementById('nftDetailBuy');
    const feedback = document.getElementById('nftDetailFeedback');
    if (isMemberPage) {
      buyButton.hidden = false;
      const owned = readStoredList(collectionStorageKey, 'collection').some(item => item.id === favoriteId);
      buyButton.disabled = owned;
      if (owned) {
        buyButton.textContent = 'Already in My Collection';
        buyButton.disabled = true;
      }
      buyButton.addEventListener('click', () => {
        const purchaseArtworkData = {
          id: favoriteId,
          title: artwork.title,
          creator: artwork.creator || 'Pixmoree creator',
          image: artwork.image,
          alt: artwork.alt || artwork.title,
          price: artwork.price || 'Demo purchase'
        };
        navigateToPayment(purchaseArtworkData);
      });
    }

    if (isMemberPage) {
      const recent = readStoredList(recentStorageKey, 'recently viewed artwork')
        .filter(item => item.id !== favoriteId);
      recent.unshift({
        id: favoriteId,
        title: artwork.title,
        creator: artwork.creator || 'Pixmoree creator',
        image: artwork.image,
        alt: artwork.alt || artwork.title,
        price: artwork.price || 'Digital artwork',
        date: new Date().toISOString().slice(0, 10)
      });
      if (writeStoredList(recentStorageKey, recent.slice(0, 8), 'recently viewed artwork')) renderMemberDashboard();
    }
  }

  document.addEventListener('click', event => {
    if (!(event.target instanceof Element) || event.target.closest('.heart-button, .trending-feature-arrow, .trending-nft-pagination, [data-artwork-purchase]')) return;
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
  document.querySelector('.member-dashboard-content')?.addEventListener('click', event => {
    if (!(event.target instanceof Element)) return;
    const button = event.target.closest('[data-artwork-action="view"]');
    const article = button?.closest('.member-art-tile');
    if (!article) return;
    const id = article.dataset.artworkId;
    const artwork = [...readSavedFavorites(), ...readStoredList(collectionStorageKey, 'collection'), ...readStoredList(recentStorageKey, 'recently viewed artwork'), ...readStoredList(createdArtStorageKey, 'created artwork')].find(item => item.id === id);
    if (artwork) {
      event.stopImmediatePropagation();
      navigateToNftDetail(artwork);
    }
  }, true);
  nftPreview?.querySelector('.nft-preview-close')?.addEventListener('click', () => nftPreview.close());
  nftPreview?.addEventListener('click', event => {
    if (event.target === nftPreview) nftPreview.close();
  });
  nftPreview?.querySelector('.nft-preview-explore')?.addEventListener('click', () => nftPreview.close());
  nftPreview?.addEventListener('close', scheduleTrendingAutoplay);
  initializeNftDetailPage();

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
