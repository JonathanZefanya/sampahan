<?php
$appName  = $settings['app_name']  ?? 'SAMPAHAN';
$cityName = $settings['city_name'] ?? '';
$cards = [
    ['label'=>'Laporan Saya',   'val'=>$stats['total'],       'icon'=>'bi-file-earmark-text','bg'=>'#eff6ff','ic'=>'#2563eb'],
    ['label'=>'Menunggu',       'val'=>$stats['pending'],     'icon'=>'bi-hourglass-split',  'bg'=>'#fefce8','ic'=>'#ca8a04'],
    ['label'=>'Dalam Proses',   'val'=>$stats['in_progress'], 'icon'=>'bi-arrow-repeat',     'bg'=>'#ecfeff','ic'=>'#0891b2'],
    ['label'=>'Selesai Bersih', 'val'=>$stats['cleaned'],     'icon'=>'bi-check-circle-fill','bg'=>'#f0fdf4','ic'=>'#16a34a'],
];
?>
<div class="d-flex flex-wrap align-items-center gap-3 mb-4">
    <div class="me-auto">
        <h2 class="fw-bold mb-0">Halo, <?= esc($authUser['name'] ?? '') ?>! &#x1F44B;</h2>
        <small class="text-muted"><?= esc($appName) ?><?= $cityName ? ' &mdash; ' . esc($cityName) : '' ?></small>
    </div>
    <a href="<?= base_url('masyarakat/report') ?>" class="btn btn-success">
        <i class="bi bi-plus-circle-fill me-1"></i> Laporkan Sampah
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
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-4">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                     style="width:64px;height:64px;background:#f0fdf4;">
                    <i class="bi bi-camera-fill text-success fs-3"></i>
                </div>
                <h5 class="fw-bold mb-1">Laporkan Sampah Baru</h5>
                <p class="text-muted small mb-3">Foto + GPS otomatis. Bantu lingkungan sekitar!</p>
                <a href="<?= base_url('masyarakat/report') ?>" class="btn btn-success w-100">
                    <i class="bi bi-plus-circle me-1"></i> Buat Laporan
                </a>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-4">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                     style="width:64px;height:64px;background:#eff6ff;">
                    <i class="bi bi-clock-history text-primary fs-3"></i>
                </div>
                <h5 class="fw-bold mb-1">Riwayat Laporan</h5>
                <p class="text-muted small mb-3">Pantau status laporan yang pernah Anda buat.</p>
                <a href="<?= base_url('masyarakat/history') ?>" class="btn btn-outline-primary w-100">
                    <i class="bi bi-list-ul me-1"></i> Lihat Riwayat
                </a>
            </div>
        </div>
    </div>
</div>
<?php if (($stats['total'] ?? 0) > 0):
    $pct = $stats['total'] > 0 ? round($stats['cleaned'] / $stats['total'] * 100) : 0; ?>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold small">Tingkat Penyelesaian</span>
            <span class="fw-bold text-success"><?= $pct ?>%</span>
        </div>
        <div class="progress" style="height:8px;border-radius:8px;">
            <div class="progress-bar bg-success" style="width:<?= $pct ?>%;border-radius:8px;"></div>
        </div>
        <div class="d-flex gap-3 mt-2">
            <small class="text-muted"><span class="text-warning fw-semibold"><?= $stats['pending'] ?></span> menunggu</small>
            <small class="text-muted"><span class="text-primary fw-semibold"><?= $stats['in_progress'] ?></span> diproses</small>
            <small class="text-muted"><span class="text-success fw-semibold"><?= $stats['cleaned'] ?></span> selesai</small>
        </div>
    </div>
</div>
<?php endif; ?>
<a href="<?= base_url('masyarakat/report') ?>" class="fab-report d-lg-none" title="Laporkan Sampah">
    <i class="bi bi-plus-lg"></i>
</a>