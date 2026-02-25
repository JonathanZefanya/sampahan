<?php
$statusConfig = [
    'pending'     => ['badge' => 'badge-pending',     'label' => 'Pending',      'ic' => '#ca8a04'],
    'reviewed'    => ['badge' => 'badge-reviewed',    'label' => 'Reviewed',     'ic' => '#0891b2'],
    'in_progress' => ['badge' => 'badge-in_progress', 'label' => 'In Progress',  'ic' => '#2563eb'],
    'cleaned'     => ['badge' => 'badge-cleaned',     'label' => 'Cleaned',      'ic' => '#16a34a'],
    'rejected'    => ['badge' => 'badge-rejected',    'label' => 'Rejected',     'ic' => '#dc2626'],
];

$statCards = [
    ['label' => 'Total',       'val' => $stats['total'],       'icon' => 'bi-file-earmark-text', 'bg' => '#eff6ff', 'ic' => '#2563eb'],
    ['label' => 'Pending',     'val' => $stats['pending'],     'icon' => 'bi-hourglass-split',   'bg' => '#fefce8', 'ic' => '#ca8a04'],
    ['label' => 'In Progress', 'val' => $stats['in_progress'], 'icon' => 'bi-arrow-repeat',      'bg' => '#ecfeff', 'ic' => '#0891b2'],
    ['label' => 'Selesai',     'val' => $stats['cleaned'],     'icon' => 'bi-check-circle-fill', 'bg' => '#f0fdf4', 'ic' => '#16a34a'],
    ['label' => 'Ditolak',     'val' => $stats['rejected'],    'icon' => 'bi-x-circle-fill',     'bg' => '#fef2f2', 'ic' => '#dc2626'],
    ['label' => 'Hotspot',     'val' => $stats['hotspots'],    'icon' => 'bi-fire',              'bg' => '#fff7ed', 'ic' => '#ea580c'],
];
?>
<div class="d-flex flex-wrap align-items-center gap-3 mb-4">
    <div class="me-auto">
        <h2 class="fw-bold mb-0"><i class="bi bi-clipboard2-data text-success me-2"></i>Manajemen Laporan</h2>
        <small class="text-muted">Kelola, filter, dan tindak lanjuti seluruh laporan warga</small>
    </div>
    <a href="<?= base_url('admin/reports') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-clockwise me-1"></i>Reset Filter
    </a>
</div>

<!-- Stat bar -->
<div class="row g-3 mb-4">
    <?php foreach ($statCards as $c): ?>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-2 py-3 px-3">
                <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:36px;height:36px;background:<?= $c['bg'] ?>;">
                    <i class="bi <?= $c['icon'] ?>" style="color:<?= $c['ic'] ?>;font-size:1rem;"></i>
                </div>
                <div>
                    <div class="fw-bold lh-1" style="color:<?= $c['ic'] ?>;font-size:1.25rem;"><?= number_format($c['val']) ?></div>
                    <div class="text-muted" style="font-size:.7rem;"><?= $c['label'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filters + search -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2 px-3">
        <form id="filterForm" method="GET" action="<?= base_url('admin/reports') ?>" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white" id="searchIcon"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchInput" name="q" class="form-control border-start-0" placeholder="Cari nama, email, deskripsi..."
                           value="<?= esc($search) ?>" autocomplete="off">
                </div>
            </div>
            <div class="col-auto">
                <select id="statusFilter" name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <?php foreach ($statusConfig as $key => $sc): ?>
                        <option value="<?= $key ?>" <?= $status === $key ? 'selected' : '' ?>><?= $sc['label'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk action bar (hidden until rows checked) -->
<div id="bulkBar" class="alert alert-primary py-2 px-3 d-flex align-items-center gap-3 mb-3" style="display:none!important;">
    <span class="fw-semibold"><span id="bulkCount">0</span> laporan dipilih</span>
    <div class="d-flex gap-2 ms-auto flex-wrap">
        <select id="bulkStatusSelect" class="form-select form-select-sm" style="width:auto;">
            <?php foreach ($statusConfig as $key => $sc): ?>
                <option value="<?= $key ?>"><?= $sc['label'] ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-sm btn-primary" onclick="bulkAction('status_change')">
            <i class="bi bi-arrow-repeat me-1"></i>Ubah Status
        </button>
        <button class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
            <i class="bi bi-trash me-1"></i>Hapus
        </button>
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="reportsTable">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th class="ps-3" style="width:40px;">
                            <input type="checkbox" id="checkAll" class="form-check-input" title="Pilih semua">
                        </th>
                        <th style="width:50px;">#</th>
                        <th>Pelapor</th>
                        <th class="d-none d-lg-table-cell">Deskripsi</th>
                        <th>Status</th>
                        <th class="d-none d-md-table-cell">Waktu</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($reports)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-inbox display-6 d-block mb-2 opacity-25"></i>
                        Tidak ada laporan ditemukan.
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($reports as $r):
                        $sc = $statusConfig[$r['status']] ?? ['badge' => 'bg-secondary', 'label' => $r['status'], 'ic' => '#6b7280'];
                        $nm = $r['reporter_name'] ?? '–';
                        $colors = ['#2563eb','#16a34a','#ca8a04','#0891b2','#dc2626','#7c3aed'];
                        $avatarBg = $colors[ord($nm[0] ?? 'A') % count($colors)];
                    ?>
                    <tr id="report-row-<?= $r['id'] ?>">
                        <td class="ps-3">
                            <input type="checkbox" class="form-check-input row-check" value="<?= $r['id'] ?>">
                        </td>
                        <td class="text-muted small">#<?= $r['id'] ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                     style="width:32px;height:32px;background:<?= $avatarBg ?>;font-size:.75rem;">
                                    <?= esc(mb_strtoupper(mb_substr($nm, 0, 1))) ?>
                                </div>
                                <div>
                                    <div class="fw-semibold small lh-sm"><?= esc($nm) ?></div>
                                    <div class="text-muted" style="font-size:.7rem;"><?= esc($r['reporter_email'] ?? '') ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="d-none d-lg-table-cell">
                            <span class="text-muted small">
                                <?= esc(mb_substr($r['description'] ?? '–', 0, 55)) ?><?= mb_strlen($r['description'] ?? '') > 55 ? '…' : '' ?>
                            </span>
                            <?php if ($r['is_recurrent_hotspot']): ?>
                                <span class="badge bg-danger ms-1" title="Hotspot Berulang"><i class="bi bi-fire"></i></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $sc['badge'] ?> rounded-pill px-2" id="status-label-<?= $r['id'] ?>">
                                <?= $sc['label'] ?>
                            </span>
                        </td>
                        <td class="d-none d-md-table-cell text-muted small">
                            <?= date('d M Y', strtotime($r['created_at'])) ?>
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-flex gap-1 justify-content-end">
                                <button class="btn btn-sm btn-outline-success" title="Detail"
                                        onclick="openDetail(<?= $r['id'] ?>)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" title="Hapus"
                                        onclick="deleteReport(<?= $r['id'] ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($pager['pages'] > 1): ?>
    <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-2 px-3">
        <small class="text-muted">
            Menampilkan <?= min(($pager['page'] - 1) * $pager['perPage'] + 1, $pager['total']) ?>–<?= min($pager['page'] * $pager['perPage'], $pager['total']) ?>
            dari <?= number_format($pager['total']) ?> laporan
        </small>
        <nav>
            <ul class="pagination pagination-sm mb-0 gap-1">
                <?php for ($p = 1; $p <= $pager['pages']; $p++):
                    $qStr = http_build_query(array_filter(['q' => $search, 'status' => $status, 'page' => $p]));
                ?>
                <li class="page-item <?= $p === $pager['page'] ? 'active' : '' ?>">
                    <a class="page-link rounded" href="<?= base_url('admin/reports?' . $qStr) ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- Detail Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="reportDetailPanel" style="width:420px;max-width:100%;">
    <div class="offcanvas-header border-bottom py-3">
        <h5 class="offcanvas-title fw-bold">
            <i class="bi bi-clipboard2-check text-success me-2"></i>Detail Laporan #<span id="detailId"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0 overflow-auto" id="detailBody">
        <div class="text-center text-muted py-5">Memuat…</div>
    </div>
</div>

<?php ob_start(); ?>
<script>
const CSRF_NAME = '<?= csrf_token() ?>';
const CSRF_HASH = '<?= csrf_hash() ?>';

// ── Real-time filter ──────────────────────────────────────────────────────────
(function () {
    const form        = document.getElementById('filterForm');
    const searchInput = document.getElementById('searchInput');
    const statusSel   = document.getElementById('statusFilter');
    const searchIcon  = document.getElementById('searchIcon');
    let debounceTimer = null;

    function submitFilter() {
        // Reset page param so we always go to page 1 on new filter
        const url = new URL(form.action);
        if (searchInput.value.trim()) {
            url.searchParams.set('q', searchInput.value.trim());
        } else {
            url.searchParams.delete('q');
        }
        if (statusSel.value) {
            url.searchParams.set('status', statusSel.value);
        } else {
            url.searchParams.delete('status');
        }
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    // Debounced search — submits 450 ms after user stops typing
    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        // Show spinner while waiting
        searchIcon.innerHTML = '<span class="spinner-border spinner-border-sm text-muted" style="width:.85rem;height:.85rem;"></span>';
        debounceTimer = setTimeout(function () {
            searchIcon.innerHTML = '<i class="bi bi-search text-muted"></i>';
            submitFilter();
        }, 450);
    });

    // Instant submit on status change
    statusSel.addEventListener('change', function () {
        submitFilter();
    });
})();

// ── Checkbox bulk select ──────────────────────────────────────────────────────
const checkAll  = document.getElementById('checkAll');
const bulkBar   = document.getElementById('bulkBar');
const bulkCount = document.getElementById('bulkCount');

function updateBulkBar() {
    const checked = document.querySelectorAll('.row-check:checked');
    const n = checked.length;
    bulkBar.style.display = n > 0 ? 'flex' : 'none';
    bulkCount.textContent = n;
}

checkAll?.addEventListener('change', () => {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = checkAll.checked);
    updateBulkBar();
});

document.querySelectorAll('.row-check').forEach(cb =>
    cb.addEventListener('change', () => {
        checkAll.checked = [...document.querySelectorAll('.row-check')].every(c => c.checked);
        updateBulkBar();
    })
);

function getCheckedIds() {
    return [...document.querySelectorAll('.row-check:checked')].map(cb => cb.value);
}

// ── Bulk action ───────────────────────────────────────────────────────────────
function bulkAction(action) {
    const ids = getCheckedIds();
    if (!ids.length) return;

    const value  = document.getElementById('bulkStatusSelect').value;
    const label  = action === 'delete'
        ? `Yakin menghapus <strong>${ids.length}</strong> laporan? Tindakan ini tidak dapat dibatalkan.`
        : `Ubah status <strong>${ids.length}</strong> laporan dipilih menjadi <em>${value}</em>?`;

    Swal.fire({
        icon: action === 'delete' ? 'warning' : 'question',
        title: action === 'delete' ? 'Hapus Laporan?' : 'Ubah Status?',
        html: label,
        showCancelButton: true,
        confirmButtonColor: action === 'delete' ? '#dc3545' : '#198754',
        confirmButtonText: action === 'delete' ? '<i class="bi bi-trash"></i> Ya, Hapus' : '<i class="bi bi-check-lg"></i> Ya, Ubah',
        cancelButtonText: 'Batal',
    }).then(result => {
        if (!result.isConfirmed) return;

        const body = new FormData();
        ids.forEach(id => body.append('ids[]', id));
        body.append('action', action);
        if (action === 'status_change') body.append('value', value);
        body.append(CSRF_NAME, CSRF_HASH);

        fetch('<?= base_url('admin/reports/bulk') ?>', { method: 'POST', body })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1800, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan.' }));
    });
}

// ── Open detail panel ─────────────────────────────────────────────────────────
const statusMap = {
    pending:     { label:'Pending',     color:'warning',  next:'Reviewed',       nextKey:'reviewed' },
    reviewed:    { label:'Reviewed',    color:'info',     next:'In Progress',    nextKey:'in_progress' },
    in_progress: { label:'In Progress', color:'primary',  next:'Ditandai Selesai', nextKey:'cleaned' },
    cleaned:     { label:'Selesai',     color:'success',  next: null },
    rejected:    { label:'Ditolak',     color:'danger',   next: null },
};

function openDetail(id) {
    document.getElementById('detailId').textContent = id;
    document.getElementById('detailBody').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-success"></div></div>';
    new bootstrap.Offcanvas(document.getElementById('reportDetailPanel')).show();

    fetch(`/admin/reports/${id}`)
        .then(r => r.json())
        .then(res => {
            if (res.status !== 'success') {
                document.getElementById('detailBody').innerHTML = '<div class="alert alert-danger m-3">Laporan tidak ditemukan.</div>';
                return;
            }
            renderDetail(res.data);
        });
}

function renderDetail(r) {
    const s  = statusMap[r.status] || { label: r.status, color: 'secondary', next: null };
    const nm = r.reporter_name || '–';
    const colors = ['#2563eb','#16a34a','#ca8a04','#0891b2','#dc2626','#7c3aed'];
    const avatarBg = colors[nm.charCodeAt(0) % colors.length];
    const initial  = nm.charAt(0).toUpperCase();

    const statusColors = { pending:{bg:'#fefce8',ic:'#ca8a04'}, reviewed:{bg:'#ecfeff',ic:'#0891b2'},
        in_progress:{bg:'#eff6ff',ic:'#2563eb'}, cleaned:{bg:'#f0fdf4',ic:'#16a34a'}, rejected:{bg:'#fef2f2',ic:'#dc2626'} };
    const sc = statusColors[r.status] || {bg:'#f3f4f6',ic:'#6b7280'};

    const photo = r.photo_path
        ? `<img src="/${r.photo_path}" class="w-100" style="height:200px;object-fit:cover;"
               onerror="this.parentElement.style.display='none'">`
        : `<div class="d-flex align-items-center justify-content-center text-muted" style="height:100px;background:#f9fafb;">
               <i class="bi bi-image opacity-25 fs-1"></i></div>`;

    const hotspot = r.is_recurrent_hotspot == 1
        ? `<div class="mx-3 mb-3"><div class="alert alert-danger py-2 px-3 mb-0 d-flex align-items-center gap-2">
               <i class="bi bi-fire fs-5"></i><div><strong class="d-block">Hotspot Berulang</strong>
               <small>Lokasi ini pernah dilaporkan sebelumnya.</small></div></div></div>` : '';

    const infoRows = [
        { icon:'bi-person-fill',  color:'#2563eb', label:'Pelapor',   val: nm },
        { icon:'bi-geo-alt-fill', color:'#dc2626', label:'Koordinat',
          val: `<a href="https://www.google.com/maps?q=${r.latitude},${r.longitude}" target="_blank" class="text-decoration-none">
                    ${parseFloat(r.latitude).toFixed(7)}, ${parseFloat(r.longitude).toFixed(7)}
                    <i class="bi bi-box-arrow-up-right ms-1 small opacity-75"></i></a>` },
        { icon:'bi-card-text',    color:'#6b7280', label:'Deskripsi', val: r.description || '–' },
        { icon:'bi-clock-fill',   color:'#0891b2', label:'Waktu',     val: r.created_at },
    ].map(row => `<div class="d-flex gap-3 align-items-start px-3 py-2 border-bottom">
        <div class="flex-shrink-0 rounded-2 d-flex align-items-center justify-content-center mt-1"
             style="width:28px;height:28px;background:${sc.bg}">
            <i class="bi ${row.icon}" style="font-size:13px;color:${row.color}"></i></div>
        <div class="small"><div class="text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:.04em;">${row.label}</div>
        <div>${row.val}</div></div></div>`).join('');

    // Status change section (admin can choose any status freely)
    const statusOptions = Object.entries(statusMap)
        .map(([k, v]) => `<option value="${k}" ${k === r.status ? 'selected' : ''}>${v.label}</option>`).join('');

    const actionHtml = `
        <div class="px-3 pt-3 pb-3">
            <div class="fw-semibold small text-muted mb-2" style="text-transform:uppercase;letter-spacing:.04em;">Ubah Status</div>
            <div class="d-flex gap-2 mb-3">
                <select class="form-select form-select-sm" id="adminStatusSelect">${statusOptions}</select>
            </div>
            <div class="mb-3">
                <textarea class="form-control form-control-sm" id="adminStatusNote" rows="2"
                          placeholder="Catatan (opsional)..."></textarea>
            </div>
            <button class="btn btn-success w-100 fw-semibold" onclick="adminUpdateStatus(${r.id})">
                <i class="bi bi-check-circle-fill me-1"></i>Simpan Perubahan Status
            </button>
            <button class="btn btn-outline-danger w-100 mt-2" onclick="deleteReport(${r.id}, true)">
                <i class="bi bi-trash me-1"></i>Hapus Laporan Ini
            </button>
        </div>`;

    document.getElementById('detailBody').innerHTML = `
        <div class="overflow-hidden">${photo}</div>
        <div class="d-flex align-items-center gap-3 px-3 py-3 border-bottom">
            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                 style="width:42px;height:42px;background:${avatarBg};font-size:16px;">${initial}</div>
            <div class="me-auto"><div class="fw-semibold">${nm}</div>
                <small class="text-muted">Laporan #${r.id}</small></div>
            <span class="badge badge-${r.status} rounded-pill px-2 py-1">${s.label}</span>
        </div>
        ${hotspot}
        ${infoRows}
        ${actionHtml}`;
}

function adminUpdateStatus(id) {
    const newStatus = document.getElementById('adminStatusSelect').value;
    const note      = document.getElementById('adminStatusNote').value;

    const body = new FormData();
    body.append('status', newStatus);
    if (note) body.append('note', note);
    body.append(CSRF_NAME, CSRF_HASH);

    fetch(`/admin/reports/${id}/status`, { method: 'POST', body })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                // Update badge in table
                const lbl = document.getElementById(`status-label-${id}`);
                const sl  = statusMap[newStatus];
                if (lbl && sl) {
                    lbl.className = `badge badge-${newStatus} rounded-pill px-2`;
                    lbl.textContent = sl.label;
                }
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                bootstrap.Offcanvas.getInstance(document.getElementById('reportDetailPanel')).hide();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
            }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan.' }));
}

function deleteReport(id, fromPanel = false) {
    Swal.fire({
        icon: 'warning',
        title: 'Hapus Laporan?',
        text: `Laporan #${id} beserta seluruh log statusnya akan dihapus permanen.`,
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
    }).then(result => {
        if (!result.isConfirmed) return;
        const body = new FormData();
        body.append(CSRF_NAME, CSRF_HASH);
        fetch(`/admin/reports/${id}/delete`, { method: 'POST', body })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Dihapus', text: res.message, timer: 1500, showConfirmButton: false })
                        .then(() => {
                            if (fromPanel) bootstrap.Offcanvas.getInstance(document.getElementById('reportDetailPanel')).hide();
                            const row = document.getElementById(`report-row-${id}`);
                            if (row) row.remove();
                        });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan.' }));
    });
}
</script>
<?php $extraScripts = ob_get_clean(); ?>
