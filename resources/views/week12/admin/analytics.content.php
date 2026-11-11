<section class="container py-4 py-lg-5" data-admin-analytics data-endpoint="<?= week12_escape(route('admin.api.analytics')) ?>">
    <a class="w12-back-link" href="<?= week12_escape(route('week12.admin')) ?>"><i class="bi bi-arrow-left"></i> Back to admin overview</a>
    <header class="mt-3 mb-4">
        <p class="w12-eyebrow mb-2">ADMIN / ANALYTICS</p>
        <h1 class="w12-section-title">Artwork and creator analytics</h1>
        <p class="text-secondary mb-0">Compare listed asking prices, estimated market-value history, and engagement signals.</p>
    </header>
    <div class="alert w12-alert" role="note"><i class="bi bi-info-circle-fill"></i><span>Estimated market values are engagement-based estimates, not completed sales or financial advice. Daily price history begins when snapshots are collected.</span></div>
    <div class="row g-4">
        <div class="col-12">
            <section class="w12-panel">
                <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                    <div><h2 class="h5 mb-1">Estimated value history</h2><p class="text-secondary small mb-0" data-price-note>Loading historical snapshots…</p></div>
                    <label class="analytics-select-label" for="analytics-artwork">Artwork
                        <select class="form-select" id="analytics-artwork" disabled><option>Loading artwork…</option></select>
                    </label>
                </div>
                <div class="analytics-chart-wrap"><canvas id="analytics-price-chart" aria-label="Estimated market value history chart"></canvas></div>
                <p class="analytics-chart-empty" data-chart-empty hidden>No daily snapshots are available yet. The scheduled job records a daily estimate for listed artwork.</p>
            </section>
        </div>
        <div class="col-xl-7">
            <section class="w12-panel h-100">
                <h2 class="h5 mb-3">Popular listed artwork</h2>
                <div class="table-responsive">
                    <table class="table w12-table align-middle mb-0">
                        <thead><tr><th>Artwork / creator</th><th>Ask</th><th>Est. value</th><th>Likes</th><th>Views</th><th>Followers</th></tr></thead>
                        <tbody data-artwork-analytics><tr><td colspan="6" class="text-secondary">Loading artwork metrics…</td></tr></tbody>
                    </table>
                </div>
            </section>
        </div>
        <div class="col-xl-5">
            <section class="w12-panel h-100">
                <h2 class="h5 mb-3">Creator engagement</h2>
                <div class="table-responsive">
                    <table class="table w12-table align-middle mb-0">
                        <thead><tr><th>Creator</th><th>Followers</th><th>Likes</th><th>Views</th><th>Listed</th></tr></thead>
                        <tbody data-creator-analytics><tr><td colspan="5" class="text-secondary">Loading creator metrics…</td></tr></tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</section>
<script>
(() => {
    const root = document.querySelector('[data-admin-analytics]');
    if (!root) return;

    const artworkSelect = root.querySelector('#analytics-artwork');
    const canvas = root.querySelector('#analytics-price-chart');
    const artworkRows = root.querySelector('[data-artwork-analytics]');
    const creatorRows = root.querySelector('[data-creator-analytics]');
    const emptyMessage = root.querySelector('[data-chart-empty]');
    const priceNote = root.querySelector('[data-price-note]');
    let artworks = [];

    const number = value => new Intl.NumberFormat().format(value);
    const money = value => `${Number(value).toFixed(2)} ETH`;
    const cell = (tag, text) => {
        const element = document.createElement(tag);
        element.textContent = text;
        return element;
    };
    const emptyRow = (columns, text, className = 'text-secondary') => {
        const row = document.createElement('tr');
        const column = cell('td', text);
        column.colSpan = columns;
        column.className = className;
        row.append(column);
        return row;
    };

    function drawPriceHistory(artwork) {
        const rect = canvas.getBoundingClientRect();
        const ratio = window.devicePixelRatio || 1;
        canvas.width = Math.max(1, rect.width * ratio);
        canvas.height = Math.max(1, rect.height * ratio);
        const context = canvas.getContext('2d');
        context.scale(ratio, ratio);
        const width = rect.width;
        const height = rect.height;
        context.clearRect(0, 0, width, height);

        const history = artwork?.price_history || [];
        if (history.length === 0) {
            emptyMessage.hidden = false;
            return;
        }
        emptyMessage.hidden = true;

        const values = history.map(point => point.value);
        const low = Math.min(...values);
        const high = Math.max(...values);
        const span = Math.max(high - low, high * .04, 1);
        const pad = {top: 20, right: 18, bottom: 34, left: 64};
        const chartWidth = Math.max(1, width - pad.left - pad.right);
        const chartHeight = Math.max(1, height - pad.top - pad.bottom);
        context.font = '12px Inter, sans-serif';
        context.strokeStyle = 'rgba(255,255,255,.12)';
        context.fillStyle = '#aaa7bd';
        context.lineWidth = 1;

        for (let tick = 0; tick <= 4; tick += 1) {
            const y = pad.top + chartHeight * tick / 4;
            const value = high + span * .1 - (span * 1.2 * tick / 4);
            context.beginPath();
            context.moveTo(pad.left, y);
            context.lineTo(width - pad.right, y);
            context.stroke();
            context.fillText(value.toFixed(2), 4, y + 4);
        }

        const points = history.map((point, index) => ({
            x: pad.left + chartWidth * (history.length === 1 ? .5 : index / (history.length - 1)),
            y: pad.top + chartHeight * (high + span * .1 - point.value) / (span * 1.2),
            date: point.date,
        }));
        context.strokeStyle = '#55dfff';
        context.lineWidth = 3;
        context.beginPath();
        points.forEach((point, index) => index === 0 ? context.moveTo(point.x, point.y) : context.lineTo(point.x, point.y));
        context.stroke();
        context.fillStyle = '#e887f1';
        points.forEach(point => {
            context.beginPath();
            context.arc(point.x, point.y, 4, 0, Math.PI * 2);
            context.fill();
        });
        context.fillStyle = '#aaa7bd';
        context.textAlign = 'left';
        context.fillText(points[0].date, pad.left, height - 8);
        context.textAlign = 'right';
        context.fillText(points[points.length - 1].date, width - pad.right, height - 8);
        context.textAlign = 'start';
    }

    function selectArtwork() {
        drawPriceHistory(artworks.find(artwork => artwork.id === artworkSelect.value));
    }

    async function loadAnalytics() {
        try {
            const response = await fetch(root.dataset.endpoint, {headers: {'Accept': 'application/json'}});
            if (!response.ok) throw new Error(`Analytics request failed (${response.status}).`);
            const data = await response.json();
            artworks = data.artworks;
            priceNote.textContent = data.price_note;

            artworkRows.replaceChildren();
            creatorRows.replaceChildren();
            artworkSelect.replaceChildren();
            artworks.forEach(artwork => {
                const option = document.createElement('option');
                option.value = artwork.id;
                option.textContent = artwork.title;
                artworkSelect.append(option);

                const row = document.createElement('tr');
                const name = cell('td', `${artwork.title} / ${artwork.creator}`);
                row.append(name, cell('td', money(artwork.asking_price)), cell('td', money(artwork.estimated_market_value)),
                    cell('td', number(artwork.likes)), cell('td', number(artwork.views)), cell('td', number(artwork.creator_followers)));
                artworkRows.append(row);
            });
            data.creators.forEach(creator => {
                const row = document.createElement('tr');
                row.append(cell('td', creator.name), cell('td', number(creator.followers)), cell('td', number(creator.likes)),
                    cell('td', number(creator.views)), cell('td', number(creator.listed_artworks)));
                creatorRows.append(row);
            });

            artworkSelect.disabled = artworks.length === 0;
            if (artworks.length === 0) {
                artworkRows.append(emptyRow(6, 'No listed artwork yet.'));
                emptyMessage.hidden = false;
            } else {
                selectArtwork();
            }
            if (data.creators.length === 0) {
                creatorRows.append(emptyRow(5, 'No creator activity yet.'));
            }
        } catch (error) {
            priceNote.textContent = error.message;
            artworkRows.replaceChildren(emptyRow(6, 'Could not load artwork metrics.', 'text-danger'));
            creatorRows.replaceChildren(emptyRow(5, 'Could not load creator metrics.', 'text-danger'));
        }
    }

    artworkSelect.addEventListener('change', selectArtwork);
    window.addEventListener('resize', selectArtwork);
    loadAnalytics();
})();
</script>
