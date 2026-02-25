<?php
$mapLat  = $mapLat  ?? '-6.2884';
$mapLng  = $mapLng  ?? '106.7135';
$mapZoom = $mapZoom ?? '12';
$geoJson = $geoJson ?? '';
?>

<style>
#pubMap { height: calc(100vh - 120px); min-height: 420px; }

/* Legend */
.legend-box { background:#fff; border-radius:.75rem; padding:10px 14px; box-shadow:0 4px 16px rgba(0,0,0,.14); font-size:.8rem; }
.legend-dot { width:11px; height:11px; border-radius:50%; display:inline-block; margin-right:7px; vertical-align:middle; flex-shrink:0; }

/* Map toolbar */
.map-toolbar { position:sticky; top:62px; z-index:999; background:rgba(255,255,255,.96);
               backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px);
               border-bottom:1px solid #e8ecef; box-shadow:0 2px 8px rgba(0,0,0,.07); }

/* Filter pill group – scrollable on mobile */
.filter-group { display:inline-flex; align-items:center; gap:4px; background:#f1f5f9;
                border-radius:50px; padding:4px; overflow-x:auto; flex-shrink:1;
                scrollbar-width:none; -ms-overflow-style:none; }
.filter-group::-webkit-scrollbar { display:none; }
.filter-pill  { cursor:pointer; border:none; background:transparent; border-radius:50px;
                padding:.28rem .85rem; font-size:.8rem; font-weight:500; color:#64748b;
                transition:background .2s, color .2s, box-shadow .2s; white-space:nowrap;
                flex-shrink:0; }
.filter-pill:hover  { background:#e2e8f0; color:#1e293b; }
.filter-pill.active { background:#fff; color:#198754; font-weight:600;
                      box-shadow:0 1px 6px rgba(0,0,0,.12); }
.filter-pill .dot   { width:8px; height:8px; border-radius:50%; display:inline-block; margin-right:5px; }

/* Popup overlay backdrop */
#popupOverlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.35);
                z-index:1040; backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px); }

/* Popup card – fixed center on all devices */
#popupCard { display:none; position:fixed; top:50%; left:50%;
             transform:translate(-50%,-50%);
             width:min(400px, calc(100vw - 24px));
             z-index:1050; }
#popupCard .card { border-radius:1rem; overflow:hidden; }
#popupCard .popup-header { background:linear-gradient(135deg,#f0fdf4,#ecfeff);
                            border-bottom:1px solid #e2e8f0; padding:.85rem 1rem; }
#popupCard .popup-body   { padding:1rem; }
#popupCard .popup-footer { padding:.65rem 1rem; background:#f8fafc;
                            border-top:1px solid #f1f5f9; }

@media (max-width: 575.98px) {
    /* On phones, slide up from bottom instead of center float */
    #pubMap { height: calc(100svh - 108px); min-height:300px; }
    .map-toolbar { top: 56px; }
    #popupCard { top:auto; bottom:0; left:0; right:0; transform:none;
                 width:100%; border-radius:1rem 1rem 0 0; }
    #popupCard .card { border-radius:1rem 1rem 0 0; }
}
</style>

<!-- Toolbar -->
<div class="map-toolbar d-flex align-items-center gap-2 gap-md-3 px-3 py-2">
    <span class="fw-bold text-dark me-auto d-flex align-items-center gap-2" style="font-size:.93rem;">
        <i class="bi bi-map-fill text-success"></i>
        <span class="d-none d-sm-inline">Peta Sebaran Sampah</span>
    </span>

    <!-- Filter pill group -->
    <div class="filter-group" role="group" aria-label="Filter status laporan">
        <button id="fAll"         class="filter-pill active"     onclick="applyFilter('')">
            <span class="dot" style="background:#64748b;"></span>Semua
        </button>
        <button id="fReviewed"    class="filter-pill"            onclick="applyFilter('reviewed')">
            <span class="dot" style="background:#0891b2;"></span>Ditinjau
        </button>
        <button id="fIn_progress" class="filter-pill"            onclick="applyFilter('in_progress')">
            <span class="dot" style="background:#2563eb;"></span>Diproses
        </button>
        <button id="fCleaned"     class="filter-pill"            onclick="applyFilter('cleaned')">
            <span class="dot" style="background:#198754;"></span>Selesai
        </button>
    </div>

    <span class="text-muted d-flex align-items-center gap-1" style="font-size:.8rem;white-space:nowrap;">
        <i class="bi bi-geo-alt-fill text-success"></i>
        <span id="markerCount">–</span> titik
    </span>
</div>

<div style="position:relative;">
    <div id="pubMap"></div>
</div>

<!-- Overlay backdrop -->
<div id="popupOverlay" onclick="closePopup()"></div>

<!-- Popup card – centered on screen -->
<div id="popupCard">
    <div class="card border-0 shadow-xl">
        <!-- Header -->
        <div class="popup-header d-flex align-items-start justify-content-between gap-2">
            <div>
                <div id="popupBadge" class="mb-1"></div>
                <small class="text-muted d-flex align-items-center gap-1" id="popupDate"></small>
            </div>
            <button class="btn-close mt-1 flex-shrink-0" onclick="closePopup()" aria-label="Tutup"></button>
        </div>
        <!-- Body -->
        <div class="popup-body">
            <p id="popupDesc" class="mb-0 text-dark" style="font-size:.9rem;line-height:1.55;"></p>
        </div>
        <!-- Footer -->
        <div class="popup-footer d-flex align-items-center gap-2">
            <i class="bi bi-geo-alt text-success"></i>
            <small class="text-muted" id="popupCoord" style="font-size:.75rem;"></small>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
const MAP_CENTER = [<?= $mapLat ?>, <?= $mapLng ?>];
const MAP_ZOOM   = <?= $mapZoom ?>;
const GEO_JSON_URL = '<?= base_url('api/reports/geojson') ?>';

// Only statuses visible on public map (pending & rejected are filtered server-side)
const STATUS_COLORS = {
    reviewed:    '#0891b2',
    in_progress: '#2563eb',
    cleaned:     '#198754',
};
const STATUS_LABELS = {
    reviewed:    'Ditinjau',
    in_progress: 'Diproses',
    cleaned:     'Selesai',
};

// ── Init map ────────────────────────────────────────────────────────────────────
const map = L.map('pubMap').setView(MAP_CENTER, MAP_ZOOM);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);

// ── Inverted polygon mask ───────────────────────────────────────────────────────
<?php if (!empty($geoJson)): ?>
const rawBoundary = <?= $geoJson ?>;
const worldRing   = [[-90,-180],[-90,180],[90,180],[90,-180],[-90,-180]];

(function applyMask(geom) {
    let rings = [];
    if (geom.type === 'Polygon')      rings = [geom.coordinates[0]];
    if (geom.type === 'MultiPolygon') rings = geom.coordinates.map(p => p[0]);

    rings.forEach(coordRing => {
        const cityRing = coordRing.map(c => [c[1], c[0]]);
        L.polygon([worldRing, cityRing], {
            fillColor: '#1a2e40', fillOpacity: 0.5,
            color: '#198754', weight: 2.5, dashArray: '4'
        }).addTo(map);
    });
})((() => {
    const b = rawBoundary;
    if (b.type === 'FeatureCollection') return b.features[0]?.geometry;
    if (b.type === 'Feature')           return b.geometry;
    return b;
})());
<?php endif; ?>

// ── GeoJSON layer ───────────────────────────────────────────────────────────────
let reportLayer = L.layerGroup().addTo(map);

function loadMarkers(statusFilter) {
    const url = statusFilter ? GEO_JSON_URL + '?status=' + statusFilter : GEO_JSON_URL;

    $.getJSON(url, function(data) {
        reportLayer.clearLayers();
        let count = 0;

        L.geoJSON(data, {
            pointToLayer: (f, ll) => {
                count++;
                const color = STATUS_COLORS[f.properties.status] ?? '#888';
                const isHotspot = f.properties.is_hotspot || f.properties.is_recurrent_hotspot;
                const marker = L.circleMarker(ll, {
                    radius:      isHotspot ? 9 : 7,
                    fillColor:   color,
                    color:       isHotspot ? '#dc2626' : '#fff',
                    weight:      isHotspot ? 2.5 : 1.5,
                    fillOpacity: 0.88,
                });
                // Pass lat/lng into properties for popup
                f.properties.lat = ll.lat;
                f.properties.lng = ll.lng;
                marker.on('click', () => showPopup(f.properties));
                return marker;
            }
        }).addTo(reportLayer);

        $('#markerCount').text(count + ' titik');
    });
}

function showPopup(props) {
    const statusLabel = STATUS_LABELS[props.status] ?? props.status;
    const colorMap = { reviewed:'#0891b2', in_progress:'#2563eb', cleaned:'#198754' };
    const dotColor = colorMap[props.status] ?? '#6b7280';

    // Badge row
    let badges = `<span class="badge rounded-pill px-3 py-1" style="background:${dotColor};font-size:.78rem;">${statusLabel}</span>`;
    if (props.is_hotspot || props.is_recurrent_hotspot) {
        badges += ' <span class="badge bg-danger rounded-pill px-2 py-1" style="font-size:.78rem;"><i class="bi bi-fire me-1"></i>Hotspot</span>';
    }
    document.getElementById('popupBadge').innerHTML = badges;

    // Body
    document.getElementById('popupDesc').textContent = props.description ?? 'Tidak ada deskripsi.';

    // Date
    const dateEl = document.getElementById('popupDate');
    if (props.created_at) {
        const d = new Date(props.created_at);
        const fmt = isNaN(d) ? props.created_at
            : d.toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
        dateEl.innerHTML = `<i class="bi bi-clock me-1"></i>${fmt}`;
    } else {
        dateEl.textContent = '';
    }

    // Coord
    const coordEl = document.getElementById('popupCoord');
    if (props.lat !== undefined && props.lng !== undefined) {
        coordEl.textContent = `${parseFloat(props.lat).toFixed(6)}, ${parseFloat(props.lng).toFixed(6)}`;
    } else {
        coordEl.textContent = 'Koordinat tidak tersedia';
    }

    // Show overlay + card
    document.getElementById('popupOverlay').style.display = 'block';
    document.getElementById('popupCard').style.display   = 'block';

    // Prevent body scroll on mobile when popup open
    document.body.style.overflow = 'hidden';
}

function closePopup() {
    document.getElementById('popupOverlay').style.display = 'none';
    document.getElementById('popupCard').style.display   = 'none';
    document.body.style.overflow = '';
}

// ── Filter buttons ──────────────────────────────────────────────────────────────
let activeFilter = '';

function applyFilter(status) {
    activeFilter = status;
    document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
    const btnId = status ? 'f' + status.charAt(0).toUpperCase() + status.slice(1) : 'fAll';
    const btn = document.getElementById(btnId);
    if (btn) btn.classList.add('active');
    loadMarkers(status);
}

// ── Legend ──────────────────────────────────────────────────────────────────────
const legend = L.control({ position: 'bottomright' });
legend.onAdd = () => {
    const d = L.DomUtil.create('div', 'legend-box');
    d.innerHTML = '<strong class="d-block mb-2" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;color:#475569;">Status Laporan</strong>' +
        Object.entries(STATUS_LABELS).map(([k, v]) =>
            `<div class="d-flex align-items-center mb-1"><span class="legend-dot" style="background:${STATUS_COLORS[k]}"></span><span>${v}</span></div>`
        ).join('') +
        '<hr class="my-2" style="border-color:#e2e8f0;">' +
        '<div class="d-flex align-items-center"><span class="legend-dot" style="background:#dc2626;box-shadow:0 0 0 3px rgba(220,38,38,.25);"></span><span>Hotspot Berulang</span></div>';
    return d;
};
legend.addTo(map);

// ── Close popup on Escape ───────────────────────────────────────────────────────
document.addEventListener('keydown', e => { if (e.key === 'Escape') closePopup(); });

// ── Init ────────────────────────────────────────────────────────────────────────
loadMarkers('');
</script>
<?php $extraScripts = ob_get_clean(); ?>
