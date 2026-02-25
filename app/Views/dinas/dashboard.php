<?php
$cards = [
    ['label' => 'Total Laporan', 'val' => $stats['total'],       'icon' => 'bi-file-earmark-text', 'bg' => '#eff6ff', 'ic' => '#2563eb'],
    ['label' => 'Pending',       'val' => $stats['pending'],     'icon' => 'bi-hourglass-split',   'bg' => '#fefce8', 'ic' => '#ca8a04'],
    ['label' => 'Dalam Proses',  'val' => $stats['in_progress'], 'icon' => 'bi-arrow-repeat',      'bg' => '#ecfeff', 'ic' => '#0891b2'],
    ['label' => 'Selesai',       'val' => $stats['cleaned'],     'icon' => 'bi-check-circle',      'bg' => '#f0fdf4', 'ic' => '#16a34a'],
];
?>
<div class="d-flex flex-wrap align-items-center gap-3 mb-4">
    <div class="me-auto">
        <h2 class="fw-bold mb-0"><i class="bi bi-speedometer2 text-success me-2"></i>Dashboard Dinas</h2>
        <small class="text-muted">
            <i class="bi bi-calendar3 me-1"></i><?= date('l, d F Y') ?>
            <?php if (!empty($settings['city_name'])): ?>&nbsp;&mdash;&nbsp;<?= esc($settings['city_name']) ?><?php endif; ?>
        </small>
    </div>
    <a href="<?= base_url('dinas/map') ?>" class="btn btn-success">
        <i class="bi bi-map me-1"></i> Buka Peta Laporan
    </a>
</div>

<div class="row g-3 mb-4">
    <?php foreach ($cards as $c): ?>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-3 p-3 flex-shrink-0" style="background:<?= $c['bg'] ?>">
                    <i class="bi <?= $c['icon'] ?> fs-4" style="color:<?= $c['ic'] ?>"></i>
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

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
        <span class="fw-semibold"><i class="bi bi-list-ul text-success me-2"></i>Laporan Aktif Terbaru</span>
        <a href="<?= base_url('dinas/map') ?>" class="btn btn-sm btn-success">
            <i class="bi bi-map me-1"></i> Peta Lengkap
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Pelapor</th>
                        <th>Status</th>
                        <th class="pe-3">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($recentReports)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-5">
                        <i class="bi bi-inbox display-6 d-block mb-2 opacity-25"></i>
                        Tidak ada laporan aktif.
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($recentReports as $r): ?>
                    <tr>
                        <td class="ps-3 text-muted">#<?= $r['id'] ?></td>
                        <td>
                            <?php $nm = $r['reporter_name'] ?? ''; $colors = ['#2563eb','#16a34a','#ca8a04','#0891b2','#dc2626','#7c3aed']; $bg = $colors[ord($nm[0]??'A') % count($colors)]; ?>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                     style="width:32px;height:32px;background:<?= $bg ?>;font-size:12px;">
                                    <?= esc(mb_strtoupper(mb_substr($nm,0,1))) ?>
                                </div>
                                <?= esc($nm) ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-<?= $r['status'] ?> rounded-pill px-2">
                                <?= ucfirst(str_replace('_', ' ', $r['status'])) ?>
                            </span>
                        </td>
                        <td class="pe-3"><small class="text-muted"><?= date('d M Y H:i', strtotime($r['created_at'])) ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>