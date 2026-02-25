<!-- Full-bleed map view – no container padding override needed -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold mb-0"><i class="bi bi-map text-primary"></i> Peta Sebaran Laporan</h2>
    <div class="d-flex gap-2">
        <select id="statusFilter" class="form-select form-select-sm w-auto">
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="reviewed">Reviewed</option>
            <option value="in_progress">In Progress</option>
            <option value="cleaned">Cleaned</option>
        </select>
        <button class="btn btn-sm btn-outline-secondary" id="btnRefresh">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>
</div>

<!-- Leaflet Map container -->
<div id="dinasMap" style="height: calc(100vh - 220px); min-height:450px; border-radius:.75rem; z-index:0;"></div>

<!-- Bottom Sheet / Detail Panel -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="reportPanel" style="width:400px; max-width:100%;">
    <div class="offcanvas-header border-bottom py-3">
        <h5 class="offcanvas-title fw-bold"><i class="bi bi-clipboard2-check text-success me-2"></i>Detail Laporan #<span id="panelId"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0 overflow-auto" id="panelBody">
        <div class="text-center text-muted py-5">Klik marker pada peta.</div>
    </div>
</div>

<?php ob_start(); ?>
<script>
// ── Leaflet map initialisation ────────────────────────────────────────────────
const MAP_LAT   = <?= json_encode((float) $mapLat) ?>;
const MAP_LNG   = <?= json_encode((float) $mapLng) ?>;
const MAP_ZOOM  = <?= json_encode((int) $mapZoom) ?>;
const GEO_JSON  = <?= $geoJson ? json_encode($geoJson) : 'null' ?>;
const CSRF_NAME = '<?= csrf_token() ?>';
const CSRF_HASH = '<?= csrf_hash() ?>';

const map = L.map('dinasMap').setView([MAP_LAT, MAP_LNG], MAP_ZOOM);

// Base tile layer
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
}).addTo(map);

// ── Inverted Polygon Mask ("World Minus City") ────────────────────────────────
// The world polygon is a huge rectangle; the city polygon creates a "hole"
// that shows the real tiles only inside the city and greys out everything else.
if (GEO_JSON) {
    const cityGeo = JSON.parse(GEO_JSON);

    // Build a GeoJSON feature with the city polygon as a HOLE in a world rectangle
    // (Leaflet uses the even-odd fill rule via SVG, so an inner ring creates a hole)
    const worldRing  = [[-90,-180],[-90,180],[90,180],[90,-180],[-90,-180]];

    // Extract first polygon coordinates from the supplied GeoJSON
    let cityCoords = null;
    if (cityGeo.type === 'FeatureCollection' && cityGeo.features.length) {
        const geo = cityGeo.features[0].geometry;
        cityCoords = geo.type === 'Polygon'
            ? geo.coordinates[0]
            : geo.coordinates[0][0];  // MultiPolygon first ring
    } else if (cityGeo.type === 'Feature') {
        const geo = cityGeo.geometry;
        cityCoords = geo.type === 'Polygon' ? geo.coordinates[0] : geo.coordinates[0][0];
    } else if (cityGeo.type === 'Polygon') {
        cityCoords = cityGeo.coordinates[0];
    }

    if (cityCoords) {
        // GeoJSON uses [lng, lat]; Leaflet uses [lat, lng]
        const cityRing = cityCoords.map(c => [c[1], c[0]]);

        L.polygon(
            [worldRing, cityRing],       // outer ring = world, inner ring = city (hole)
            {
                fillColor:   '#1a2e40',
                fillOpacity: 0.55,
                stroke:      true,
                color:       '#198754',
                weight:      2.5,
                opacity:     0.9,
                dashArray:   '6,4',
            }
        ).addTo(map);

        // Also draw the city boundary as a highlighted outline
        L.geoJSON(cityGeo, {
            style: { color: '#198754', weight: 2, fill: false }
        }).addTo(map);
    }
}

// ── Marker icons by status ────────────────────────────────────────────────────
const iconColors = {
    pending:     '#ffc107',
    reviewed:    '#0dcaf0',
    in_progress: '#0d6efd',
    cleaned:     '#198754',
    rejected:    '#dc3545',
};

function makeIcon(status, isHotspot = false) {
    const bg    = iconColors[status] || '#6c757d';
    const size  = isHotspot ? 16 : 12;
    const html  = `<div style="
        width:${size}px; height:${size}px; border-radius:50%;
        background:${bg}; border:2px solid #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,.4);
        ${isHotspot ? 'outline:3px solid #dc3545;' : ''}
    "></div>`;
    return L.divIcon({ html, className: '', iconSize: [size, size], iconAnchor: [size/2, size/2] });
}

// ── GeoJSON layer for reports ─────────────────────────────────────────────────
let reportLayer = null;

function loadReports(statusFilter = '') {
    const url = `/dinas/api/reports/geojson${statusFilter ? '?status=' + statusFilter : ''}`;
    $.getJSON(url, function (data) {
        if (reportLayer) map.removeLayer(reportLayer);

        reportLayer = L.geoJSON(data, {
            pointToLayer: (feature, latlng) => {
                return L.marker(latlng, {
                    icon: makeIcon(feature.properties.status, feature.properties.is_hotspot)
                });
            },
            onEachFeature: (feature, layer) => {
                layer.on('click', () => openReportPanel(feature.properties.id));
            }
        }).addTo(map);
    });
}

loadReports();

$('#statusFilter').on('change', function () { loadReports(this.value); });
$('#btnRefresh').on('click', function () { loadReports($('#statusFilter').val()); });

// ── Report Detail Panel ───────────────────────────────────────────────────────
function openReportPanel(reportId) {
    $('#panelId').text(reportId);
    $('#panelBody').html('<div class="text-center py-5"><div class="spinner-border text-success"></div></div>');
    const offcanvas = new bootstrap.Offcanvas(document.getElementById('reportPanel'));
    offcanvas.show();

    $.getJSON(`/dinas/reports/${reportId}`, function (res) {
        if (res.status !== 'success') {
            $('#panelBody').html('<div class="alert alert-danger">Laporan tidak ditemukan.</div>');
            return;
        }
        const r = res.data;
        const statusMap = {
            pending: {label:'Pending', color:'warning', next:'Reviewed'},
            reviewed: {label:'Reviewed', color:'info', next:'In Progress'},
            in_progress: {label:'In Progress', color:'primary', next:'Ditandai Selesai'},
            cleaned: {label:'Cleaned', color:'success', next: null},
            rejected: {label:'Rejected', color:'danger', next: null},
        };
        const s = statusMap[r.status] || {label: r.status, color:'secondary', next: null};

        let advanceBtn = '';
        if (s.next) {
            advanceBtn = `
            <div class="mb-3">
                <label class="form-label small text-muted fw-semibold">Catatan (opsional)</label>
                <textarea class="form-control" id="advanceNote" rows="2"
                          placeholder="Tambahkan catatan untuk pelapor..."></textarea>
            </div>
            <button class="btn btn-success w-100 py-2 fw-semibold" onclick="advanceReport(${r.id})">
                <i class="bi bi-arrow-right-circle-fill me-2"></i>Ubah ke: ${s.next}
            </button>`;
        }

        let rejectBtn = '';
        if (!['cleaned','rejected'].includes(r.status)) {
            rejectBtn = `
            <div class="mt-3 pt-3 border-top">
                <label class="form-label small text-muted fw-semibold">Tolak Laporan</label>
                <input type="text" id="rejectNote" class="form-control mb-2"
                       placeholder="Alasan penolakan (wajib)...">
                <button class="btn btn-outline-danger w-100" onclick="rejectReport(${r.id})">
                    <i class="bi bi-x-circle me-1"></i> Tolak Laporan
                </button>
            </div>`;
        }

        // Build avatar initials
        const nm       = r.reporter_name || '?';
        const avatarColors = ['#2563eb','#16a34a','#ca8a04','#0891b2','#dc2626','#7c3aed'];
        const avatarBg = avatarColors[nm.charCodeAt(0) % avatarColors.length];
        const initial  = nm.charAt(0).toUpperCase();

        const statusColors = {
            pending:     {bg:'#fefce8', ic:'#ca8a04'},
            reviewed:    {bg:'#ecfeff', ic:'#0891b2'},
            in_progress: {bg:'#eff6ff', ic:'#2563eb'},
            cleaned:     {bg:'#f0fdf4', ic:'#16a34a'},
            rejected:    {bg:'#fef2f2', ic:'#dc2626'},
        };
        const sc = statusColors[r.status] || {bg:'#f3f4f6', ic:'#6b7280'};

        const photoHtml = r.photo_path
            ? `<img src="/${r.photo_path}" class="w-100" style="height:200px;object-fit:cover;"
                   onerror="this.parentElement.style.display='none'">`
            : `<div class="d-flex align-items-center justify-content-center text-muted"
                    style="height:120px;background:#f9fafb;">
                 <div class="text-center"><i class="bi bi-image display-5 opacity-25 d-block"></i><small>Tidak ada foto</small></div>
               </div>`;

        const hotspotBadge = r.is_recurrent_hotspot == 1
            ? `<div class="mx-3 mb-3">
                 <div class="alert alert-danger py-2 px-3 mb-0 d-flex align-items-center gap-2">
                   <i class="bi bi-fire fs-5"></i>
                   <div><strong class="d-block">Hotspot Berulang</strong>
                     <small>Lokasi ini pernah dilaporkan sebelumnya.</small></div>
                 </div>
               </div>` : '';

        const infoRows = [
            { icon:'bi-person-fill',     color:'#2563eb', label:'Pelapor',   val: nm },
            { icon:'bi-geo-alt-fill',    color:'#dc2626', label:'Koordinat', val:
                `<a href="https://www.google.com/maps?q=${r.latitude},${r.longitude}" target="_blank" class="text-decoration-none">
                   ${parseFloat(r.latitude).toFixed(7)}, ${parseFloat(r.longitude).toFixed(7)}
                   <i class="bi bi-box-arrow-up-right ms-1 small opacity-75"></i></a>` },
            { icon:'bi-card-text',       color:'#6b7280', label:'Deskripsi', val: r.description || '–' },
            { icon:'bi-clock-fill',      color:'#0891b2', label:'Waktu',     val: r.created_at },
        ].map(row =>
            `<div class="d-flex gap-3 align-items-start px-3 py-2 border-bottom">
               <div class="flex-shrink-0 rounded-2 d-flex align-items-center justify-content-center mt-1"
                    style="width:28px;height:28px;background:${sc.bg}">
                 <i class="bi ${row.icon}" style="font-size:13px;color:${row.color}"></i>
               </div>
               <div class="small">
                 <div class="text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:.04em;">${row.label}</div>
                 <div>${row.val}</div>
               </div>
             </div>`
        ).join('');

        const actionSection = (advanceBtn || rejectBtn) ? `
            <div class="px-3 pt-3">${advanceBtn}${rejectBtn}</div>` : '';

        $('#panelBody').html(`
            <div class="overflow-hidden" style="border-radius:0;">${photoHtml}</div>

            <div class="d-flex align-items-center gap-3 px-3 py-3 border-bottom">
                <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                     style="width:42px;height:42px;background:${avatarBg};font-size:16px;">${initial}</div>
                <div class="me-auto">
                    <div class="fw-semibold">${nm}</div>
                    <small class="text-muted">Laporan #${r.id}</small>
                </div>
                <span class="badge badge-${r.status} rounded-pill px-2 py-1">${s.label}</span>
            </div>

            ${hotspotBadge}
            ${infoRows}
            ${actionSection}
        `);
    });
}

function advanceReport(id) {
    const note = $('#advanceNote').val();
    $.post(`/dinas/reports/${id}/advance`, {
        note, [CSRF_NAME]: CSRF_HASH
    }, function (res) {
        if (res.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Status Diperbarui',
                text: `Status berhasil diubah ke: ${res.data.new_status}`,
                timer: 2000,
                showConfirmButton: false,
            }).then(() => {
                bootstrap.Offcanvas.getInstance(document.getElementById('reportPanel')).hide();
                loadReports($('#statusFilter').val());
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
        }
    }, 'json');
}

function rejectReport(id) {
    const note = $('#rejectNote').val();
    if (!note) {
        Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Isi alasan penolakan terlebih dahulu.' });
        return;
    }
    Swal.fire({
        icon: 'question',
        title: 'Tolak Laporan?',
        text: 'Laporan akan ditandai sebagai ditolak dan pelapor akan diberitahu.',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Tolak',
        cancelButtonText: 'Batal',
    }).then(result => {
        if (!result.isConfirmed) return;
        $.post(`/dinas/reports/${id}/reject`, {
            note, [CSRF_NAME]: CSRF_HASH
        }, function (res) {
            Swal.fire({
                icon: res.status === 'success' ? 'success' : 'error',
                title: res.status === 'success' ? 'Laporan Ditolak' : 'Gagal',
                text: res.message,
                timer: 2000,
                showConfirmButton: false,
            }).then(() => {
                bootstrap.Offcanvas.getInstance(document.getElementById('reportPanel')).hide();
                loadReports($('#statusFilter').val());
            });
        }, 'json');
    });
}

// ── Legend ────────────────────────────────────────────────────────────────────
const legend = L.control({ position: 'bottomleft' });
legend.onAdd = function () {
    const div = L.DomUtil.create('div', 'leaflet-control bg-white rounded shadow p-2');
    div.innerHTML = `<strong class="d-block mb-1 small">Legenda</strong>` +
        Object.entries(iconColors).map(([s, c]) =>
            `<div class="d-flex align-items-center gap-2 mb-1">
                <span style="width:12px;height:12px;border-radius:50%;background:${c};display:inline-block;border:1.5px solid #fff;"></span>
                <small>${s.replace('_',' ')}</small>
             </div>`
        ).join('') +
        `<div class="d-flex align-items-center gap-2 mt-1">
            <span style="width:14px;height:14px;border-radius:50%;background:#dc3545;outline:2px solid #dc3545;display:inline-block;border:2px solid #fff;"></span>
            <small>Hotspot Berulang</small>
         </div>`;
    return div;
};
legend.addTo(map);
</script>
<?php $extraScripts = ob_get_clean(); ?>
