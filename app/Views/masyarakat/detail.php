<?php
$statusConfig = [
    'pending'     => ['label' => 'Menunggu',     'bg' => '#fefce8', 'ic' => '#ca8a04', 'badge' => 'badge-pending',     'icon' => 'bi-hourglass-split'],
    'in_progress' => ['label' => 'Dalam Proses', 'bg' => '#ecfeff', 'ic' => '#0891b2', 'badge' => 'badge-in_progress', 'icon' => 'bi-arrow-repeat'],
    'cleaned'     => ['label' => 'Selesai',      'bg' => '#f0fdf4', 'ic' => '#16a34a', 'badge' => 'badge-cleaned',     'icon' => 'bi-check-circle-fill'],
    'rejected'    => ['label' => 'Ditolak',      'bg' => '#fef2f2', 'ic' => '#dc2626', 'badge' => 'badge-rejected',    'icon' => 'bi-x-circle-fill'],
];
$st = $statusConfig[$report['status']] ?? ['label' => ucfirst($report['status']), 'bg' => '#f3f4f6', 'ic' => '#6b7280', 'badge' => 'bg-secondary', 'icon' => 'bi-circle'];
?>
<div class="d-flex flex-wrap align-items-center gap-3 mb-4">
    <a href="<?= base_url('masyarakat/history') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <div class="me-auto">
        <h2 class="fw-bold mb-0">Detail Laporan <span class="text-muted">#<?= $report['id'] ?></span></h2>
    </div>
    <span class="badge <?= $st['badge'] ?> rounded-pill fs-6 px-3 py-2">
        <i class="bi <?= $st['icon'] ?> me-1"></i><?= $st['label'] ?>
    </span>
</div>

<div class="row g-4 mb-4">
    <!-- Photo -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm overflow-hidden h-100">
            <?php if ($report['photo_path']): ?>
                <img src="<?= base_url($report['photo_path']) ?>"
                     class="w-100" style="max-height:340px;object-fit:cover;"
                     onerror="this.src='https://placehold.co/640x340/e5e7eb/9ca3af?text=Foto+Tidak+Tersedia'">
            <?php else: ?>
                <div class="d-flex align-items-center justify-content-center text-muted"
                     style="height:220px;background:#f9fafb;">
                    <div class="text-center">
                        <i class="bi bi-image display-3 opacity-25 d-block mb-2"></i>
                        <span class="small">Tidak ada foto</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Info card -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom fw-semibold">
                <i class="bi bi-info-circle text-primary me-2"></i>Informasi Laporan
            </div>
            <div class="card-body">
                <dl class="mb-0">
                    <div class="row py-2 border-bottom">
                        <dt class="col-5 text-muted small fw-semibold">Status</dt>
                        <dd class="col-7 mb-0">
                            <span class="badge <?= $st['badge'] ?> rounded-pill px-2"><?= $st['label'] ?></span>
                        </dd>
                    </div>
                    <div class="row py-2 border-bottom">
                        <dt class="col-5 text-muted small fw-semibold">Tanggal</dt>
                        <dd class="col-7 mb-0 small"><?= date('d M Y, H:i', strtotime($report['created_at'])) ?></dd>
                    </div>
                    <div class="row py-2 border-bottom">
                        <dt class="col-5 text-muted small fw-semibold">Koordinat</dt>
                        <dd class="col-7 mb-0">
                            <a href="https://www.google.com/maps?q=<?= $report['latitude'] ?>,<?= $report['longitude'] ?>"
                               target="_blank" class="small text-decoration-none">
                                <?= number_format((float)$report['latitude'], 6) ?>, <?= number_format((float)$report['longitude'], 6) ?>
                                <i class="bi bi-box-arrow-up-right ms-1 opacity-75"></i>
                            </a>
                        </dd>
                    </div>
                    <div class="row py-2 <?= (!empty($report['admin_note']) || $report['is_recurrent_hotspot']) ? 'border-bottom' : '' ?>">
                        <dt class="col-5 text-muted small fw-semibold">Deskripsi</dt>
                        <dd class="col-7 mb-0 small"><?= nl2br(esc($report['description'] ?? '–')) ?></dd>
                    </div>
                    <?php if ($report['is_recurrent_hotspot']): ?>
                    <div class="row py-2 <?= !empty($report['admin_note']) ? 'border-bottom' : '' ?>">
                        <dt class="col-5 text-muted small fw-semibold">Catatan</dt>
                        <dd class="col-7 mb-0">
                            <span class="badge bg-danger small"><i class="bi bi-fire me-1"></i>Hotspot Berulang</span>
                        </dd>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($report['admin_note'])): ?>
                    <div class="row py-2">
                        <dt class="col-5 text-muted small fw-semibold">Catatan Admin</dt>
                        <dd class="col-7 mb-0">
                            <div class="alert alert-warning py-2 px-3 mb-0 small">
                                <i class="bi bi-chat-square-text me-1"></i><?= esc($report['admin_note']) ?>
                            </div>
                        </dd>
                    </div>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Timeline -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom fw-semibold">
        <i class="bi bi-clock-history text-primary me-2"></i>Riwayat Status
    </div>
    <div class="card-body">
        <?php if (empty($logs)): ?>
            <div class="text-center text-muted py-4">
                <i class="bi bi-clock display-5 d-block mb-2 opacity-25"></i>
                <p class="mb-0 small">Belum ada log perubahan status.</p>
            </div>
        <?php else: ?>
            <div class="ps-1">
            <?php foreach ($logs as $idx => $log):
                $isLast = $idx === array_key_last($logs);
                $ns = $statusConfig[$log['new_status']] ?? ['ic' => '#6b7280', 'icon' => 'bi-circle'];
            ?>
                <div class="d-flex gap-3 pb-<?= $isLast ? '0' : '3' ?>">
                    <div class="flex-shrink-0 d-flex flex-column align-items-center" style="width:28px;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0"
                             style="width:28px;height:28px;background:<?= $ns['ic'] ?>;">
                            <i class="bi <?= $ns['icon'] ?>" style="font-size:11px;"></i>
                        </div>
                        <?php if (!$isLast): ?>
                            <div style="width:2px;flex:1;min-height:16px;background:#e5e7eb;margin-top:2px;"></div>
                        <?php endif; ?>
                    </div>
                    <div class="pb-1">
                        <div class="fw-semibold small">
                            <?= esc($log['old_status'] ?? 'Baru') ?>
                            <i class="bi bi-arrow-right mx-1 opacity-50"></i>
                            <span style="color:<?= $ns['ic'] ?>"><?= esc($log['new_status']) ?></span>
                        </div>
                        <?php if ($log['note']): ?>
                            <p class="mb-0 text-muted small"><?= esc($log['note']) ?></p>
                        <?php endif; ?>
                        <small class="text-muted">
                            <?= date('d M Y H:i', strtotime($log['created_at'])) ?>
                            <?= $log['actor_name'] ? ' &mdash; ' . esc($log['actor_name']) : '' ?>
                        </small>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
