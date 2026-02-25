<?php
$statusConfig = [
    'pending'     => ['label' => 'Menunggu',     'badge' => 'badge-pending',     'icon' => 'bi-hourglass-split'],
    'in_progress' => ['label' => 'Dalam Proses', 'badge' => 'badge-in_progress', 'icon' => 'bi-arrow-repeat'],
    'cleaned'     => ['label' => 'Selesai',      'badge' => 'badge-cleaned',     'icon' => 'bi-check-circle-fill'],
    'rejected'    => ['label' => 'Ditolak',      'badge' => 'badge-rejected',    'icon' => 'bi-x-circle-fill'],
];
?>
<div class="d-flex flex-wrap align-items-center gap-3 mb-4">
    <div class="me-auto">
        <h2 class="fw-bold mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Riwayat Laporan</h2>
        <small class="text-muted">Semua laporan yang telah Anda buat</small>
    </div>
    <a href="<?= base_url('masyarakat/report') ?>" class="btn btn-success">
        <i class="bi bi-plus-circle-fill me-1"></i> Buat Laporan Baru
    </a>
</div>

<?php if (empty($reports)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox display-3 d-block mb-3 text-muted opacity-25"></i>
            <h5 class="fw-semibold mb-1">Belum Ada Laporan</h5>
            <p class="text-muted mb-4">Mulai laporkan sampah di sekitar Anda dan bantu jaga kebersihan lingkungan!</p>
            <a href="<?= base_url('masyarakat/report') ?>" class="btn btn-success px-4">
                <i class="bi bi-camera-fill me-2"></i> Buat Laporan Pertama
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3">
    <?php foreach ($reports as $r):
        $sc = $statusConfig[$r['status']] ?? ['label' => ucfirst($r['status']), 'badge' => 'bg-secondary', 'icon' => 'bi-circle'];
        $desc = mb_substr($r['description'] ?? '', 0, 80);
    ?>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <?php if ($r['photo_path']): ?>
                    <img src="<?= base_url($r['photo_path']) ?>"
                         class="card-img-top" style="height:160px;object-fit:cover;"
                         onerror="this.style.display='none'">
                <?php else: ?>
                    <div class="card-img-top d-flex align-items-center justify-content-center text-muted"
                         style="height:100px;background:#f9fafb;">
                        <i class="bi bi-image opacity-25 fs-1"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body pb-2">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge <?= $sc['badge'] ?> rounded-pill px-2">
                            <i class="bi <?= $sc['icon'] ?> me-1"></i><?= $sc['label'] ?>
                        </span>
                        <?php if ($r['is_recurrent_hotspot']): ?>
                            <span class="badge bg-danger"><i class="bi bi-fire"></i></span>
                        <?php endif; ?>
                    </div>
                    <p class="card-text text-muted small mb-2">
                        <?= esc($desc ?: 'Tidak ada deskripsi.') ?><?= strlen($r['description'] ?? '') > 80 ? '…' : '' ?>
                    </p>
                    <div class="text-muted small">
                        <i class="bi bi-calendar3 me-1"></i><?= date('d M Y, H:i', strtotime($r['created_at'])) ?>
                    </div>
                    <?php if (!empty($r['admin_note']) && $r['status'] === 'rejected'): ?>
                        <div class="alert alert-warning py-1 mt-2 mb-0 small">
                            <i class="bi bi-exclamation-triangle me-1"></i><?= esc($r['admin_note']) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-transparent border-top-0 pt-0 pb-3 px-3">
                    <a href="<?= base_url('masyarakat/history/' . $r['id']) ?>"
                       class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-eye me-1"></i> Lihat Detail
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>

<a href="<?= base_url('masyarakat/report') ?>" class="fab-report d-lg-none" title="Laporkan Sampah">
    <i class="bi bi-plus-lg"></i>
</a>
