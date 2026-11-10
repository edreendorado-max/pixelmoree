document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('marketplaceFilters');
  const grid = document.querySelector('#artwork .row.g-4');
  const search = document.getElementById('marketplaceSearch');
  const category = document.getElementById('marketplaceCategory');
  const sort = document.getElementById('marketplaceSort');
  const resultCount = document.getElementById('marketplaceResultCount');
  const noResults = document.getElementById('marketplaceNoResults');
  if (!form || !search || !category || !sort || !resultCount || !noResults) return;

  const items = grid ? [...grid.querySelectorAll('[data-marketplace-item]')] : [];
  if (!items.length) {
    form.hidden = true;
    return;
  }
  const applyFilters = () => {
    const query = search.value.trim().toLocaleLowerCase();
    const selectedCategory = category.value;
    const visibleItems = items.filter(item => {
      const matchesQuery = `${item.dataset.title} ${item.dataset.creator}`.includes(query);
      return matchesQuery && (!selectedCategory || item.dataset.category === selectedCategory);
    });

    visibleItems.sort((left, right) => {
      if (sort.value === 'price-low') return Number(left.dataset.price) - Number(right.dataset.price);
      if (sort.value === 'price-high') return Number(right.dataset.price) - Number(left.dataset.price);
      if (sort.value === 'title') return left.dataset.title.localeCompare(right.dataset.title);
      return right.dataset.created.localeCompare(left.dataset.created);
    });

    items.forEach(item => { item.hidden = !visibleItems.includes(item); });
    visibleItems.forEach(item => grid.append(item));
    resultCount.textContent = String(visibleItems.length);
    noResults.hidden = visibleItems.length > 0;
  };

  form.addEventListener('submit', event => event.preventDefault());
  search.addEventListener('input', applyFilters);
  category.addEventListener('change', applyFilters);
  sort.addEventListener('change', applyFilters);
  applyFilters();
});
