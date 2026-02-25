<?php
$appName  = $settings['app_name']  ?? 'SAMPAHAN';
$cityName = $settings['city_name'] ?? '';

// Inject extra styles into layout
$extraStyle = '
.hero-section {
    position:relative; min-height:100vh;
    display:flex; align-items:center;
    background:linear-gradient(135deg,#0f1f2e 0%,#0d3b25 60%,#198754 100%);
    overflow:hidden; color:#fff;
}
.hero-blob { position:absolute; border-radius:50%; filter:blur(80px); opacity:.18; pointer-events:none; }
.hero-blob-1 { width:520px; height:520px; background:#198754; top:-120px; right:-80px; }
.hero-blob-2 { width:380px; height:380px; background:#0d6efd; bottom:-60px; left:-60px; }
.hero-badge {
    display:inline-flex; align-items:center; gap:.5rem;
    background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.2);
    border-radius:999px; padding:.4rem 1rem; font-size:.82rem; font-weight:500;
    backdrop-filter:blur(6px); letter-spacing:.5px;
}
.hero-title  { font-size:clamp(2.2rem,5vw,3.8rem); font-weight:800; line-height:1.15; letter-spacing:-.5px; }
.hero-sub    { font-size:1.05rem; opacity:.85; line-height:1.8; max-width:520px; }
.btn-hero-primary {
    background:#198754; color:#fff; border:none; padding:.9rem 2.2rem;
    border-radius:999px; font-weight:600; font-size:1rem;
    transition:transform .2s,box-shadow .2s; text-decoration:none;
    box-shadow:0 6px 24px rgba(25,135,84,.4);
}
.btn-hero-primary:hover { transform:translateY(-2px); box-shadow:0 10px 32px rgba(25,135,84,.55); color:#fff; }
.btn-hero-outline {
    background:transparent; color:#fff; border:2px solid rgba(255,255,255,.4);
    padding:.85rem 2rem; border-radius:999px; font-weight:600;
    font-size:1rem; transition:background .2s; text-decoration:none;
}
.btn-hero-outline:hover { background:rgba(255,255,255,.1); color:#fff; }
/* Hero floating card */
.hero-stats-card {
    background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.15);
    border-radius:1.5rem; backdrop-filter:blur(14px); padding:1.75rem;
}
.hero-stat-num  { font-size:2rem; font-weight:800; line-height:1; }
.hero-stat-lbl  { font-size:.78rem; opacity:.75; font-weight:500; }
.hero-stat-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; }
/* Section pill */
.section-pill {
    display:inline-block; background:#e8f5e9; color:#198754;
    border-radius:999px; padding:.3rem .9rem; font-size:.78rem;
    font-weight:600; letter-spacing:.8px; text-transform:uppercase; margin-bottom:.5rem;
}
/* Stat cards */
.stat-card { border-radius:1.25rem !important; }
.stat-icon-wrap { width:56px; height:56px; border-radius:16px; display:flex; align-items:center; justify-content:center; }
.stat-num { font-size:1.9rem; font-weight:800; }
/* Steps */
.step-card { border-radius:1.5rem !important; transition:transform .3s; }
.step-card:hover { transform:translateY(-6px); }
.step-num {
    width:34px; height:34px; border-radius:50%; background:#1a2e40; color:#fff;
    font-size:.75rem; font-weight:700; display:flex; align-items:center; justify-content:center;
    position:absolute; top:-10px; left:-10px; box-shadow:0 4px 10px rgba(0,0,0,.2);
}
.step-icon-wrap { width:72px; height:72px; border-radius:20px; display:flex; align-items:center; justify-content:center; font-size:2rem; margin:0 auto 1rem; }
/* Donut center */
.donut-center { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); text-align:center; pointer-events:none; }
/* Feature item */
.feature-item { border-left:3px solid #e8f5e9; transition:border-color .2s; }
.feature-item:hover { border-left-color:#198754; }
/* Map */
#landingMap { height:460px; border-radius:1.25rem; border:4px solid #e8f5e9; box-shadow:0 8px 32px rgba(25,135,84,.12); }
/* CTA */
.cta-section { background:linear-gradient(135deg,#0f1f2e 0%,#0d3b25 50%,#198754 100%); position:relative; overflow:hidden; }
.cta-section::before { content:""; position:absolute; inset:0; background:url("data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'.025\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/svg%3E"); }
/* Animations */
.reveal { opacity:0; transform:translateY(28px); transition:opacity .65s ease,transform .65s ease; }
.reveal.visible { opacity:1; transform:none; }
/* ── Mobile & iPhone responsive ── */
@media (max-width:767.98px) {
    .hero-section { min-height:100svh; min-height:100vh; }
    .hero-blob-1 { width:280px; height:280px; right:-60px; top:-60px; }
    .hero-blob-2 { width:220px; height:220px; }
    .hero-sub { max-width:100%; font-size:.97rem; }
    .btn-hero-primary,.btn-hero-outline { width:100%; justify-content:center; text-align:center; display:flex; align-items:center; }
    .hero-stats-card { padding:1.1rem 1rem; }
    .hero-stat-num { font-size:1.45rem; }
    .hero-stat-lbl { font-size:.72rem; }
    .hero-stat-icon { width:36px; height:36px; border-radius:10px; }
    #landingMap { height:300px; }
    .stat-num { font-size:1.5rem; }
    .stat-icon-wrap { width:46px; height:46px; }
    .step-card { padding:1.25rem 1rem !important; }
    .step-icon-wrap { width:58px; height:58px; font-size:1.6rem; }
    .donut-center > div:first-child { font-size:1.4rem !important; }
    .cta-section .fs-5 { font-size:1rem !important; }
    .cta-section .btn { width:100%; justify-content:center; }
}
@media (max-width:575.98px) {
    .hero-title { font-size:clamp(1.75rem,7vw,2.4rem); }
    .hero-stats-card .row > .col-6 { padding:.3rem .4rem; }
    #landingMap { height:260px; border-radius:.75rem; }
    .stat-card { padding:.75rem !important; }
    .feature-item { padding:.75rem !important; }
}
';
?>

<section class="hero-section">
    <div class="hero-blob hero-blob-1"></div>
    <div class="hero-blob hero-blob-2"></div>

    <div class="container py-5">
        <div class="row align-items-center g-5">

            <!-- Left: text -->
            <div class="col-lg-6 text-center text-lg-start">
                <span class="hero-badge mb-3">
                    <i class="bi bi-geo-alt-fill text-success"></i>
                    <?= esc($cityName ?: 'Platform GIS Sampah') ?>
                </span>
                <h1 class="hero-title mt-3 mb-3">
                    <?= esc($appName) ?><br>
                    <span style="color:#4ade80;">Kota Bersih,</span><br>
                    Mulai dari Kita.
                </h1>
                <p class="hero-sub mb-4">
                    Laporkan sampah di sekitar Anda, pantau proses penanganan secara
                    <em>real-time</em>, dan bersama-sama wujudkan lingkungan yang lebih bersih.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                    <a href="<?= base_url('auth/register') ?>" class="btn-hero-primary">
                        <i class="bi bi-person-plus me-2"></i>Daftar Gratis
                    </a>
                    <a href="<?= base_url('peta-sebaran') ?>" class="btn-hero-outline">
                        <i class="bi bi-map me-2"></i>Lihat Peta
                    </a>
                </div>
                <div class="d-flex flex-wrap gap-4 justify-content-center justify-content-lg-start mt-4 pt-1">
                    <span style="font-size:.82rem;opacity:.6;">
                        <i class="bi bi-shield-check me-1 text-success"></i>Terverifikasi GPS
                    </span>
                    <span style="font-size:.82rem;opacity:.6;">
                        <i class="bi bi-lightning-charge me-1 text-warning"></i>Respons Cepat
                    </span>
                    <span style="font-size:.82rem;opacity:.6;">
                        <i class="bi bi-phone me-1 text-info"></i>Mobile-Friendly
                    </span>
                </div>
            </div>

            <!-- Right: floating stats card -->
            <div class="col-lg-6 d-flex justify-content-center justify-content-lg-end">
                <div class="hero-stats-card" style="width:min(380px,100%);">
                    <p class="mb-3 fw-semibold" style="opacity:.75;font-size:.8rem;text-transform:uppercase;letter-spacing:1px;">
                        Ringkasan Real-Time
                    </p>
                    <div class="row g-3">
                        <?php
                        $heroStats = [
                            ['icon'=>'bi-file-earmark-text-fill','bg'=>'rgba(13,110,253,.2)',  'color'=>'#6ea8fe','label'=>'Total Laporan','key'=>'total'],
                            ['icon'=>'bi-eye-fill',              'bg'=>'rgba(13,202,240,.2)',  'color'=>'#0dcaf0','label'=>'Ditinjau',    'key'=>'reviewed'],
                            ['icon'=>'bi-arrow-repeat',          'bg'=>'rgba(37,99,235,.2)',   'color'=>'#60a5fa','label'=>'Diproses',    'key'=>'in_progress'],
                            ['icon'=>'bi-check-circle-fill',     'bg'=>'rgba(25,135,84,.25)', 'color'=>'#4ade80','label'=>'Selesai',     'key'=>'cleaned'],
                        ];
                        foreach ($heroStats as $hs): ?>
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="hero-stat-icon" style="background:<?= $hs['bg'] ?>;">
                                    <i class="bi <?= $hs['icon'] ?>" style="color:<?= $hs['color'] ?>;font-size:1.2rem;"></i>
                                </div>
                                <div>
                                    <div class="hero-stat-num counter" data-target="<?= $stats[$hs['key']] ?>">0</div>
                                    <div class="hero-stat-lbl"><?= $hs['label'] ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php $pct = $stats['total'] > 0 ? round($stats['cleaned'] / $stats['total'] * 100) : 0; ?>
                    <div class="mt-4 pt-2 border-top border-white border-opacity-10">
                        <div class="d-flex justify-content-between mb-1" style="font-size:.78rem;opacity:.8;">
                            <span>Tingkat Penyelesaian</span>
                            <span class="fw-bold"><?= $pct ?>%</span>
                        </div>
                        <div class="progress" style="height:7px;background:rgba(255,255,255,.12);border-radius:99px;">
                            <div class="progress-bar bg-success" style="width:<?= $pct ?>%;border-radius:99px;"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /row -->
    </div>

    <!-- Wave divider -->
    <div style="position:absolute;bottom:-1px;left:0;right:0;line-height:0;">
        <svg viewBox="0 0 1440 70" preserveAspectRatio="none" style="width:100%;height:55px;">
            <path d="M0,35 C360,70 1080,0 1440,35 L1440,70 L0,70 Z" fill="#f8fafc"/>
        </svg>
    </div>
</section>

<section class="py-5" style="background:#f8fafc;">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <span class="section-pill">Statistik</span>
            <h2 class="fw-bold mt-1">Data Pengelolaan Sampah</h2>
            <p class="text-muted" style="max-width:460px;margin:auto;">
                Transparansi data secara publik diperbaharui setiap laporan masuk.
            </p>
        </div>
        <div class="row g-4">
            <?php
            $statCards = [
                ['key'=>'total',       'label'=>'Total Laporan',    'sub'=>'Sepanjang waktu',          'icon'=>'bi-file-earmark-text-fill','bg'=>'#eff6ff','ic'=>'#3b82f6'],
                ['key'=>'reviewed',    'label'=>'Ditinjau',         'sub'=>'Diverifikasi petugas',     'icon'=>'bi-eye-fill',              'bg'=>'#ecfeff','ic'=>'#0891b2'],
                ['key'=>'in_progress', 'label'=>'Diproses',         'sub'=>'Sedang ditangani petugas', 'icon'=>'bi-arrow-repeat',          'bg'=>'#eff6ff','ic'=>'#2563eb'],
                ['key'=>'cleaned',     'label'=>'Selesai Bersih',   'sub'=>'Area berhasil dibersihkan','icon'=>'bi-check-circle-fill',     'bg'=>'#f0fdf4','ic'=>'#16a34a'],
                ['key'=>'hotspots',    'label'=>'Hotspot Berulang', 'sub'=>'Perlu perhatian khusus',   'icon'=>'bi-fire',                 'bg'=>'#fff7ed','ic'=>'#ea580c'],
            ];
            foreach ($statCards as $i => $sc): ?>
            <div class="col-6 col-md-4 col-lg reveal" style="transition-delay:<?= $i * .08 ?>s;">
                <div class="card stat-card h-100 text-center p-3">
                    <div class="stat-icon-wrap mx-auto mb-3" style="background:<?= $sc['bg'] ?>;">
                        <i class="bi <?= $sc['icon'] ?>" style="color:<?= $sc['ic'] ?>;font-size:1.4rem;"></i>
                    </div>
                    <div class="stat-num fw-bold counter" data-target="<?= $stats[$sc['key']] ?>"
                         style="color:<?= $sc['ic'] ?>;">0</div>
                    <div class="fw-semibold small mt-1"><?= $sc['label'] ?></div>
                    <div class="text-muted" style="font-size:.72rem;"><?= $sc['sub'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5" style="background:#fff;">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <span class="section-pill">Cara Kerja</span>
            <h2 class="fw-bold mt-1">Hanya 4 Langkah Mudah</h2>
            <p class="text-muted" style="max-width:440px;margin:auto;">
                Dari foto hingga area bersih semua terpantau di satu platform.
            </p>
        </div>
        <div class="row g-4">
            <?php $steps = [
                ['num'=>'01','icon'=>'bi-camera-fill',       'bg'=>'#f0fdf4','ic'=>'#16a34a','title'=>'Foto Sampah',      'desc'=>'Ambil foto sampah & GPS otomatis mendeteksi lokasi Anda secara akurat.'],
                ['num'=>'02','icon'=>'bi-send-check-fill',   'bg'=>'#eff6ff','ic'=>'#2563eb','title'=>'Kirim Laporan',     'desc'=>'Sistem memvalidasi batas wilayah & duplikasi sebelum menyimpan laporan.'],
                ['num'=>'03','icon'=>'bi-people-fill',       'bg'=>'#fffbeb','ic'=>'#d97706','title'=>'Petugas Bergerak',  'desc'=>'Tim Dinas mendapat notifikasi dan segera menangani laporan ke lokasi.'],
                ['num'=>'04','icon'=>'bi-patch-check-fill',  'bg'=>'#f0fdf4','ic'=>'#16a34a','title'=>'Area Bersih!',     'desc'=>'Pelapor mendapat notifikasi email saat area berhasil dibersihkan.'],
            ];
            foreach ($steps as $i => $s): ?>
            <div class="col-sm-6 col-lg-3 reveal" style="transition-delay:<?= $i * .1 ?>s;">
                <div class="card step-card h-100 p-4 position-relative">
                    <div class="step-num"><?= $s['num'] ?></div>
                    <div class="step-icon-wrap" style="background:<?= $s['bg'] ?>;">
                        <i class="bi <?= $s['icon'] ?>" style="color:<?= $s['ic'] ?>;"></i>
                    </div>
                    <h5 class="fw-bold mb-2"><?= $s['title'] ?></h5>
                    <p class="text-muted small mb-0"><?= $s['desc'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5" style="background:#f8fafc;">
    <div class="container">
        <div class="row g-5 align-items-center">

            <!-- Donut chart -->
            <div class="col-lg-5 reveal">
                <span class="section-pill">Visualisasi</span>
                <h2 class="fw-bold mt-1 mb-4">Distribusi Status Laporan</h2>
                <div class="card p-4">
                    <div style="position:relative;max-width:260px;margin:auto;">
                        <canvas id="landingStatusChart"></canvas>
                        <div class="donut-center">
                            <?php $visibleTotal = ($stats['reviewed'] ?? 0) + ($stats['in_progress'] ?? 0) + ($stats['cleaned'] ?? 0); ?>
                            <div class="fw-bold" style="font-size:1.8rem;color:#1a2e40;"
                                 id="donutTotal"><?= number_format($visibleTotal) ?></div>
                            <div class="text-muted" style="font-size:.72rem;">Ditangani</div>
                        </div>
                    </div>
                    <div class="row g-2 mt-3">
                        <?php
                        $legends = [
                            ['label'=>'Ditinjau',  'color'=>'#0891b2','key'=>'reviewed'],
                            ['label'=>'Diproses',  'color'=>'#2563eb','key'=>'in_progress'],
                            ['label'=>'Selesai',   'color'=>'#198754','key'=>'cleaned'],
                        ];
                        foreach ($legends as $lg): ?>
                        <div class="col-6 d-flex align-items-center gap-2" style="font-size:.79rem;">
                            <span style="width:11px;height:11px;border-radius:3px;background:<?= $lg['color'] ?>;flex-shrink:0;"></span>
                            <span class="text-muted"><?= $lg['label'] ?>
                                <strong style="color:#2d3748;"><?= number_format($stats[$lg['key']]) ?></strong>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Feature list -->
            <div class="col-lg-7 reveal" style="transition-delay:.15s;">
                <span class="section-pill">Keunggulan</span>
                <h2 class="fw-bold mt-1 mb-4">Mengapa <?= esc($appName) ?>?</h2>
                <?php $features = [
                    ['icon'=>'bi-geo-alt-fill',       'color'=>'#16a34a','title'=>'Validasi Batas Wilayah',    'desc'=>'Laporan divalidasi masuk wilayah kota menggunakan algoritma point-in-polygon GeoJSON.'],
                    ['icon'=>'bi-shield-check-fill',  'color'=>'#2563eb','title'=>'Deteksi Duplikasi 10 Meter','desc'=>'Sistem mencegah laporan ganda dalam radius 10 m untuk efisiensi penanganan di lapangan.'],
                    ['icon'=>'bi-fire',               'color'=>'#ea580c','title'=>'Pendeteksi Hotspot',        'desc'=>'Area berulang kali dilaporkan ditandai sebagai hotspot prioritas secara otomatis.'],
                    ['icon'=>'bi-envelope-check-fill','color'=>'#7c3aed','title'=>'Notifikasi Email Otomatis', 'desc'=>'Pelapor mendapat email konfirmasi & pemberitahuan saat laporan selesai ditangani.'],
                    ['icon'=>'bi-phone-fill',         'color'=>'#0891b2','title'=>'Mobile-First & GPS Otomatis','desc'=>'Tombol besar, GPS satu sentuh  dirancang ramah untuk pengguna lanjut usia.'],
                    ['icon'=>'bi-gear-fill',          'color'=>'#374151','title'=>'White-Label Penuh',          'desc'=>'Logo, kota, SMTP, & batas GIS dikonfigurasi via Admin panel  tanpa perlu coding.'],
                ];
                foreach ($features as $f): ?>
                <div class="feature-item d-flex gap-3 mb-3 p-3 rounded-3 bg-white">
                    <i class="bi <?= $f['icon'] ?> mt-1 flex-shrink-0"
                       style="color:<?= $f['color'] ?>;font-size:1.4rem;"></i>
                    <div>
                        <div class="fw-semibold mb-1"><?= $f['title'] ?></div>
                        <p class="text-muted small mb-0"><?= $f['desc'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
</section>

<section class="py-5" style="background:#fff;">
    <div class="container">
        <div class="text-center mb-4 reveal">
            <span class="section-pill">Peta Sebaran</span>
            <h2 class="fw-bold mt-1">Lihat Sebaran Laporan</h2>
            <p class="text-muted" style="max-width:440px;margin:auto;">
                Semua laporan terpetakan secara real-time. Klik marker untuk detail.
            </p>
        </div>
        <div class="position-relative reveal" style="transition-delay:.1s;">
            <div id="landingMap"></div>
            <!-- Inline legend -->
            <div style="position:absolute;top:12px;right:12px;z-index:999;
                        background:rgba(255,255,255,.93);border-radius:.75rem;
                        padding:.65rem .9rem;box-shadow:0 4px 16px rgba(0,0,0,.1);backdrop-filter:blur(6px);">
                <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-bottom:.4rem;">Status</div>
                <?php foreach ([
                    ['#0891b2','Ditinjau'],
                    ['#2563eb','Diproses'],
                    ['#198754','Selesai'],
                    ['#dc2626','Hotspot'],
                ] as [$c,$l]): ?>
                <div class="d-flex align-items-center gap-2 mb-1" style="font-size:.76rem;">
                    <span style="width:9px;height:9px;border-radius:50%;background:<?= $c ?>;flex-shrink:0;"></span>
                    <span><?= $l ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="<?= base_url('peta-sebaran') ?>"
               class="btn btn-success btn-lg px-5 rounded-pill"
               style="box-shadow:0 6px 20px rgba(25,135,84,.4);">
                <i class="bi bi-arrows-fullscreen me-2"></i>Buka Peta Penuh
            </a>
        </div>
    </div>
</section>

<section class="cta-section py-5 text-center text-white">
    <div class="container position-relative py-3" style="z-index:1;">
        <div class="reveal">
            <div style="font-size:3rem;margin-bottom:1rem;"></div>
            <h2 class="fw-bold mb-3" style="font-size:2rem;">
                Bergabunglah Sebagai Pahlawan Lingkungan
            </h2>
            <p class="mb-4" style="opacity:.85;max-width:500px;margin:0 auto 2rem;">
                Setiap laporan yang Anda kirim membantu kota menjadi lebih bersih dan nyaman untuk semua.
            </p>
            <div class="d-flex gap-3 flex-wrap justify-content-center">
                <a href="<?= base_url('auth/register') ?>"
                   class="btn btn-light fw-semibold px-5 rounded-pill fs-5"
                   style="color:#1a2e40;box-shadow:0 6px 20px rgba(255,255,255,.3);">
                    <i class="bi bi-person-plus me-2"></i>Daftar Sekarang Gratis
                </a>
                <a href="<?= base_url('auth/login') ?>"
                   class="btn rounded-pill px-4 fw-semibold fs-5"
                   style="border:2px solid rgba(255,255,255,.4);color:#fff;">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sudah Punya Akun
                </a>
            </div>
        </div>
    </div>
</section>

<?php ob_start(); ?>
<script>
const revealObserver = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.12 });
document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

function animateCounter(el, target, duration = 1400) {
    let start = null;
    (function step(ts) {
        if (!start) start = ts;
        const p = Math.min((ts - start) / duration, 1);
        const ease = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.floor(ease * target).toLocaleString('id-ID');
        if (p < 1) requestAnimationFrame(step);
    })(performance.now());
}
const cntObs = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if (e.isIntersecting && !e.target.dataset.done) {
            e.target.dataset.done = '1';
            animateCounter(e.target, parseInt(e.target.dataset.target, 10));
        }
    });
}, { threshold: 0.5 });
document.querySelectorAll('.counter').forEach(el => cntObs.observe(el));

new Chart(document.getElementById('landingStatusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Ditinjau','Diproses','Selesai'],
        datasets: [{
            data: [
                <?= $stats['reviewed'] ?? 0 ?>,
                <?= $stats['in_progress'] ?>,
                <?= $stats['cleaned'] ?>
            ],
            backgroundColor: ['#0891b2','#2563eb','#198754'],
            borderWidth: 3, borderColor: '#fff', hoverOffset: 8,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        cutout: '68%',
        animation: { animateRotate: true, duration: 1200 },
    }
});

const lMap = L.map('landingMap', { scrollWheelZoom: false })
              .setView([<?= $mapLat ?>, <?= $mapLng ?>], <?= $mapZoom ?>);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>'
}).addTo(lMap);

<?php if (!empty($geoJson)): ?>
(function() {
    const raw   = <?= $geoJson ?>;
    const world = [[-90,-180],[-90,180],[90,180],[90,-180],[-90,-180]];
    const geom  = raw.type === 'FeatureCollection' ? raw.features[0]?.geometry
                : raw.type === 'Feature' ? raw.geometry : raw;
    if (!geom) return;
    const rings = geom.type === 'Polygon' ? [geom.coordinates[0]]
                                          : geom.coordinates.map(p => p[0]);
    rings.forEach(r => L.polygon([world, r.map(c => [c[1],c[0]])], {
        fillColor:'#1a2e40', fillOpacity:.45, color:'#198754', weight:2.5
    }).addTo(lMap));
})();
<?php endif; ?>

$.getJSON('<?= base_url('api/reports/geojson') ?>', function(data) {
    const sc = { reviewed:'#0891b2', in_progress:'#2563eb', cleaned:'#198754' };
    const sl = { reviewed:'Ditinjau', in_progress:'Diproses', cleaned:'Selesai' };
    L.geoJSON(data, {
        pointToLayer: (f, ll) => L.circleMarker(ll, {
            radius:      f.properties.is_hotspot || f.properties.is_recurrent_hotspot ? 10 : 7,
            fillColor:   sc[f.properties.status] ?? '#888',
            color:       f.properties.is_hotspot || f.properties.is_recurrent_hotspot ? '#dc2626' : '#fff',
            weight:      f.properties.is_hotspot || f.properties.is_recurrent_hotspot ? 2.5 : 1.5,
            fillOpacity: 0.88,
        }),
        onEachFeature: (f, l) => {
            const label = sl[f.properties.status] ?? f.properties.status;
            l.bindPopup(
                `<span class="badge rounded-pill" style="background:${sc[f.properties.status]??'#888'};font-size:.75rem;">${label}</span>` +
                (f.properties.is_hotspot || f.properties.is_recurrent_hotspot ? ' <span class="badge bg-danger rounded-pill" style="font-size:.75rem;">&#x1F525; Hotspot</span>' : '') +
                `<br><small class="text-muted mt-1 d-block">${f.properties.description ?? ''}</small>`);
        }
    }).addTo(lMap);
});
</script>
<?php $extraScripts = ob_get_clean(); ?>
