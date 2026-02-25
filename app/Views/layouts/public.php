<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($settings['app_name'] ?? 'SAMPAHAN') ?> – Sistem Informasi Pengelolaan Sampah</title>
    <link rel="icon" href="<?= base_url($settings['app_favicon'] ?? 'uploads/favicon.ico') ?>" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --sph-primary: #198754;
            --sph-dark:    #1a2e40;
            --sph-accent:  #20c997;
        }
        * { scroll-behavior: smooth; }
        html { background: var(--sph-dark); } /* prevents white flash/gap between dark sections on mobile */
        body { font-family: 'Poppins', sans-serif; font-size: 1rem; color: #2d3748; background: #fff; }
        h1,h2,h3,h4,h5,h6 { font-family: 'Poppins', sans-serif; font-weight: 700; }
        .navbar-brand img { height: 38px; object-fit: contain; }
        .navbar { transition: box-shadow .3s, background .3s; }
        .navbar.scrolled { box-shadow: 0 4px 20px rgba(0,0,0,.12) !important; }
        .card { border: none; border-radius: 1rem; box-shadow: 0 2px 12px rgba(0,0,0,.06); transition: transform .25s, box-shadow .25s; }
        .card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.10); }
        #map { height: 500px; border-radius: 1rem; }
        footer { background: var(--sph-dark); color: #c8d6e5; }

        /* ── Navbar ── */
        .sph-navbar { background: rgba(255,255,255,.95) !important; backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border-bottom: 1px solid rgba(0,0,0,.07) !important; transition: box-shadow .3s, background .3s; }
        .sph-navbar.scrolled { box-shadow: 0 4px 20px rgba(0,0,0,.10) !important; }
        .sph-navbar .navbar-brand .brand-icon { width: 36px; height: 36px; background: linear-gradient(135deg,#198754,#20c997); border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .sph-navbar .navbar-brand .brand-name { font-weight: 800; font-size: 1.1rem; color: #1a2e40; letter-spacing: -.01em; }
        .sph-navbar .nav-link-pub { position: relative; font-weight: 500; color: #4a5568 !important; padding: .45rem .65rem !important; font-size: .92rem; transition: color .2s; }
        .sph-navbar .nav-link-pub::after { content: ''; position: absolute; bottom: 0; left: .65rem; right: .65rem; height: 2.5px; border-radius: 2px; background: var(--sph-primary); transform: scaleX(0); transform-origin: center; transition: transform .25s ease; }
        .sph-navbar .nav-link-pub:hover { color: var(--sph-primary) !important; }
        .sph-navbar .nav-link-pub:hover::after { transform: scaleX(1); }
        .sph-navbar .nav-link-pub.active { color: var(--sph-primary) !important; font-weight: 600; }
        .sph-navbar .nav-link-pub.active::after { transform: scaleX(1); }
        .sph-navbar .btn-nav-login { font-size: .85rem; font-weight: 600; padding: .38rem 1.1rem; border-radius: 8px; }
        .sph-navbar .btn-nav-register { font-size: .85rem; font-weight: 600; padding: .38rem 1.1rem; border-radius: 8px; }
        .sph-navbar .nav-divider { width: 1px; height: 22px; background: #e2e8f0; margin: 0 4px; align-self: center; }
        @media (max-width: 991.98px) {
            .sph-navbar .nav-divider { display: none; }
            .sph-navbar .btn-nav-login, .sph-navbar .btn-nav-register { width: 100%; text-align: center; margin-top: .25rem; }
            .sph-navbar .nav-link-pub::after { display: none; }
            .sph-navbar .nav-link-pub.active { background: rgba(25,135,84,.07); border-radius: 8px; }
        }
        <?= $extraStyle ?? '' ?>
    </style>
    <?= $extraHead ?? '' ?>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg sph-navbar sticky-top" id="mainNavbar">
    <div class="container">

        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none" href="<?= base_url('/') ?>">
            <?php if (!empty($settings['app_logo']) && file_exists(FCPATH . $settings['app_logo'])): ?>
                <img src="<?= base_url($settings['app_logo']) ?>"
                     alt="<?= esc($settings['app_name'] ?? 'SAMPAHAN') ?>" style="height:36px;object-fit:contain;">
            <?php else: ?>
                <div class="brand-icon flex-shrink-0">
                    <i class="bi bi-recycle text-white" style="font-size:1.1rem;"></i>
                </div>
            <?php endif; ?>
            <span class="brand-name"><?= esc($settings['app_name'] ?? 'SAMPAHAN') ?></span>
        </a>

        <!-- Mobile toggler -->
        <button class="navbar-toggler border-0 shadow-none" type="button"
                data-bs-toggle="collapse" data-bs-target="#publicNav" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Nav links -->
        <div class="collapse navbar-collapse" id="publicNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1 py-2 py-lg-0">

                <li class="nav-item">
                    <a class="nav-link-pub <?= current_url() === base_url('/') ? 'active' : '' ?> d-flex align-items-center gap-1 px-2"
                       href="<?= base_url('/') ?>" style="text-decoration: none;">
                        <i class="bi bi-house"></i> Beranda
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link-pub <?= str_contains(current_url(), 'peta-sebaran') ? 'active' : '' ?> d-flex align-items-center gap-1 px-2"
                       href="<?= base_url('peta-sebaran') ?>" style="text-decoration: none;">
                        <i class="bi bi-map"></i> Peta Sebaran
                    </a>
                </li>

                <!-- Divider (desktop only) -->
                <li class="nav-item nav-divider d-none d-lg-block"></li>

                <li class="nav-item">
                    <a class="btn btn-success btn-nav-login d-inline-flex align-items-center gap-1"
                       href="<?= base_url('auth/login') ?>">
                        <i class="bi bi-box-arrow-in-right"></i> Masuk
                    </a>
                </li>

                <li class="nav-item">
                    <a class="btn btn-outline-success btn-nav-register d-inline-flex align-items-center gap-1"
                       href="<?= base_url('auth/register') ?>">
                        <i class="bi bi-person-plus"></i> Daftar
                    </a>
                </li>

            </ul>
        </div>
    </div>
</nav>

<!-- Flash messages -->
<?php if (session()->getFlashdata('success') || session()->getFlashdata('error')): ?>
<div class="container mt-3">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= esc(session()->getFlashdata('success')) ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= esc(session()->getFlashdata('error')) ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Page content -->
<?= $content ?>

<!-- Footer -->
<footer class="py-5 mt-0">
    <div class="container">
        <div class="row align-items-center gy-3">
            <div class="col-md-5">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <img src="<?= base_url($settings['app_logo'] ?? 'uploads/logo.png') ?>" height="32"
                         alt="<?= esc($settings['app_name'] ?? 'SAMPAHAN') ?>" style="opacity:.85;filter:brightness(10)">
                    <span class="fw-bold fs-5 text-white"><?= esc($settings['app_name'] ?? 'SAMPAHAN') ?></span>
                </div>
                <p class="mb-0" style="color:#94a3b8;font-size:.87rem;">
                    Sistem Informasi Pengelolaan Sampah Berbasis Web-GIS<br>
                    <?= esc($settings['city_name'] ?? '') ?>
                </p>
            </div>
            <div class="col-md-4">
                <ul class="list-unstyled mb-0" style="color:#94a3b8;font-size:.87rem;">
                    <li><a href="<?= base_url('/') ?>" class="text-decoration-none" style="color:#94a3b8;"><i class="bi bi-house me-2"></i>Beranda</a></li>
                    <li class="mt-1"><a href="<?= base_url('peta-sebaran') ?>" class="text-decoration-none" style="color:#94a3b8;"><i class="bi bi-map me-2"></i>Peta Sebaran</a></li>
                    <li class="mt-1"><a href="<?= base_url('auth/register') ?>" class="text-decoration-none" style="color:#94a3b8;"><i class="bi bi-person-plus me-2"></i>Daftar Gratis</a></li>
                </ul>
            </div>
            <div class="col-md-3 text-md-end">
                <p class="mb-0" style="color:#64748b;font-size:.8rem;">
                    &copy; <?= date('Y') ?> <?= esc($settings['app_name'] ?? 'SAMPAHAN') ?><br>
                    All rights reserved.
                </p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Scroll-aware navbar
window.addEventListener('scroll', () => {
    document.getElementById('mainNavbar').classList.toggle('scrolled', window.scrollY > 40);
}, { passive: true });

// Auto-close mobile nav on link click
document.querySelectorAll('#publicNav .nav-link-pub, #publicNav .btn').forEach(el => {
    el.addEventListener('click', () => {
        const collapse = document.getElementById('publicNav');
        const bsCollapse = bootstrap.Collapse.getInstance(collapse);
        if (bsCollapse) bsCollapse.hide();
    });
});
</script>
<?= $extraScripts ?? '' ?>
</body>
</html>
