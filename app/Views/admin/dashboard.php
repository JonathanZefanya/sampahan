<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="fw-bold mb-0">Dashboard Admin</h2>
        <small class="text-muted">
            <i class="bi bi-calendar3 me-1"></i><?= date('l, d F Y') ?>
            <?php if (!empty($settings['city_name'])): ?>&nbsp;&mdash;&nbsp;<?= esc($settings['city_name']) ?><?php endif; ?>
        </small>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-people me-1"></i>Kelola User
        </a>
        <a href="<?= base_url('admin/settings') ?>" class="btn btn-success btn-sm">
            <i class="bi bi-gear me-1"></i>Pengaturan
        </a>
    </div>
</div>

<!-- ── Stat Cards ───────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['label' => 'Total Laporan',      'val' => $reportStats['total'],       'icon' => 'bi-file-earmark-text', 'bg' => '#eff6ff', 'ic' => '#2563eb'],
        ['label' => 'Menunggu',           'val' => $reportStats['pending'],     'icon' => 'bi-hourglass-split',   'bg' => '#fefce8', 'ic' => '#ca8a04'],
        ['label' => 'Dalam Proses',       'val' => $reportStats['in_progress'], 'icon' => 'bi-arrow-repeat',      'bg' => '#ecfeff', 'ic' => '#0891b2'],
        ['label' => 'Selesai Bersih',     'val' => $reportStats['cleaned'],     'icon' => 'bi-check-circle',      'bg' => '#f0fdf4', 'ic' => '#16a34a'],
        ['label' => 'Total User',         'val' => $userStats['total'],         'icon' => 'bi-people',            'bg' => '#f5f3ff', 'ic' => '#7c3aed'],
        ['label' => 'User Nonaktif',      'val' => $userStats['inactive'],      'icon' => 'bi-person-x',          'bg' => '#fff1f2', 'ic' => '#e11d48'],
        ['label' => 'Petugas Dinas',      'val' => $userStats['dinas'],         'icon' => 'bi-person-badge',      'bg' => '#f0f9ff', 'ic' => '#0369a1'],
        ['label' => 'Hotspot Berulang',   'val' => $reportStats['hotspots'],    'icon' => 'bi-fire',              'bg' => '#fff7ed', 'ic' => '#ea580c'],
    ];
    foreach ($cards as $c): ?>
    <div class="col-6 col-lg-3">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-3 p-3 flex-shrink-0" style="background:<?= $c['bg'] ?>;">
                    <i class="bi <?= $c['icon'] ?> fs-4" style="color:<?= $c['ic'] ?>;"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold lh-1" style="color:<?= $c['ic'] ?>"><?= number_format($c['val']) ?></div>
                    <div class="text-muted small mt-1"><?= $c['label'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Chart ────────────────────────────────────────────────────────────── -->
<div class="row g-4 mb-4">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-transparent fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-pie-chart text-success"></i> Status Laporan
            </div>
            <div class="card-body d-flex justify-content-center align-items-center">
                <canvas id="statusChart" style="max-height:260px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-transparent fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-bar-chart text-primary"></i> User per Peran
            </div>
            <div class="card-body d-flex justify-content-center align-items-center">
                <canvas id="userChart" style="max-height:260px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ── Recent Reports Table ──────────────────────────────────────────────── -->
<div class="card">
    <div class="card-header bg-transparent fw-semibold d-flex align-items-center gap-2">
        <i class="bi bi-clock-history text-warning"></i> Laporan Terbaru
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>Pelapor</th><th>Status</th>
                        <th>Koordinat</th><th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($recentReports)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada laporan.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentReports as $r): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><?= esc($r['reporter_name'] ?? '–') ?></td>
                        <td>
                            <span class="badge badge-<?= $r['status'] ?> rounded-pill px-2 py-1">
                                <?= ucfirst(str_replace('_', ' ', $r['status'])) ?>
                            </span>
                        </td>
                        <td><small class="text-muted"><?= $r['latitude'] ?>, <?= $r['longitude'] ?></small></td>
                        <td><small><?= date('d M Y H:i', strtotime($r['created_at'])) ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $extraScripts = '' ?>
<script>
// Status donut chart
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Pending','Reviewed','In Progress','Cleaned','Rejected'],
        datasets: [{
            data: [
                <?= $reportStats['pending'] ?>,
                <?= $reportStats['reviewed'] ?>,
                <?= $reportStats['in_progress'] ?>,
                <?= $reportStats['cleaned'] ?>,
                <?= $reportStats['rejected'] ?>
            ],
            backgroundColor: ['#ffc107','#0dcaf0','#0d6efd','#198754','#dc3545'],
        }]
    },
    options: { plugins: { legend: { position: 'bottom' } }, cutout: '65%' }
});

// User bar chart
new Chart(document.getElementById('userChart'), {
    type: 'bar',
    data: {
        labels: ['Admin','Dinas','Masyarakat'],
        datasets: [{
            label: 'Jumlah User',
            data: [<?= $userStats['admin'] ?>, <?= $userStats['dinas'] ?>, <?= $userStats['masyarakat'] ?>],
            backgroundColor: ['#1a2e40','#198754','#0d6efd'],
            borderRadius: 6,
        }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
</script>
