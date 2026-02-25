<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($settings['app_name'] ?? 'SAMPAHAN') ?></title>

    <link rel="icon" href="<?= base_url($settings['app_favicon'] ?? 'uploads/favicon.ico') ?>" type="image/x-icon">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        :root {
            --sph-primary:   #198754;
            --sph-secondary: #0d6efd;
            --sph-danger:    #dc3545;
            --sph-sidebar-w: 260px;
            --sph-sidebar-bg:#1a2e40;
        }
        body { font-size: 1rem; background: #f0f4f8; }

        /* ── Sidebar ────────────────────────────────────────────────────── */
        .sph-sidebar {
            width: var(--sph-sidebar-w);
            min-height: 100vh;
            background: var(--sph-sidebar-bg);
            position: fixed;
            top: 0; left: 0;
            z-index: 1030;
            transition: transform .25s ease;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 20px rgba(0,0,0,.15);
        }
        .sph-brand {
            padding: 1.1rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
            background: rgba(0,0,0,.15);
        }
        .sph-brand img { height: 38px; object-fit: contain; }
        .sph-brand .brand-name { font-size:.95rem; font-weight:700; color:#fff; line-height:1.2; }
        .sph-brand .brand-sub  { font-size:.7rem; color:rgba(255,255,255,.45); }
        .sph-nav { padding: .75rem .5rem; flex: 1; overflow-y: auto; }
        .sph-nav-label {
            font-size:.65rem; font-weight:700; color:rgba(255,255,255,.3);
            text-transform:uppercase; letter-spacing:1.2px;
            padding:.5rem .75rem .25rem;
        }
        .sph-sidebar .nav-link {
            color: rgba(255,255,255,.65);
            font-size: .9rem;
            font-weight: 500;
            padding: .6rem .9rem;
            border-radius: .6rem;
            margin: 1px 0;
            display: flex;
            align-items: center;
            gap: .65rem;
            transition: background .18s, color .18s;
        }
        .sph-sidebar .nav-link .bi { font-size: 1.05rem; flex-shrink: 0; width: 20px; text-align: center; }
        .sph-sidebar .nav-link:hover { background: rgba(255,255,255,.08); color: #fff; }
        .sph-sidebar .nav-link.active {
            background: rgba(25,135,84,.25);
            color: #4ade80;
            border-left: 3px solid #4ade80;
            padding-left: calc(.9rem - 3px);
        }
        .sph-sidebar-footer {
            padding: .75rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,.08);
            font-size: .72rem;
            color: rgba(255,255,255,.35);
        }

        /* ── Main content ───────────────────────────────────────────────── */
        .sph-main { margin-left: var(--sph-sidebar-w); min-height: 100vh; background: #f0f4f8; display: flex; flex-direction: column; }

        /* ── Topbar ─────────────────────────────────────────────────────── */
        .sph-navbar {
            background: #fff;
            border-bottom: 1px solid #e5eaf0;
            padding: .6rem 1.25rem;
            box-shadow: 0 1px 6px rgba(0,0,0,.06);
        }
        .sph-user-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: linear-gradient(135deg,#198754,#0d6efd);
            color: #fff; font-size: .85rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        /* ── Cards ──────────────────────────────────────────────────────── */
        .card {
            border: 1px solid #e5eaf0;
            border-radius: .85rem;
            box-shadow: 0 1px 6px rgba(0,0,0,.05);
            background: #fff;
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid #e5eaf0;
            padding: .85rem 1.25rem;
            border-radius: .85rem .85rem 0 0 !important;
        }
        .card-footer {
            background: #f8fafc;
            border-top: 1px solid #e5eaf0;
            padding: .75rem 1.25rem;
            border-radius: 0 0 .85rem .85rem !important;
        }
        .stat-card { border-left: 3px solid var(--sph-primary) !important; }

        /* ── Big buttons (Elderly-friendly) ─────────────────────────────── */
        .btn-xlg { padding: .9rem 2rem; font-size: 1.1rem; border-radius: .7rem; font-weight: 600; }
        .fab-report {
            position: fixed; bottom: 1.5rem; right: 1.5rem;
            width: 64px; height: 64px; border-radius: 50%;
            background: var(--sph-primary); color: #fff;
            font-size: 1.7rem; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 6px 20px rgba(25,135,84,.4); z-index: 1050; text-decoration: none;
            transition: transform .2s;
        }
        .fab-report:hover { transform: scale(1.08); color: #fff; }

        /* ── Status badges ──────────────────────────────────────────────── */
        .badge-pending     { background: #fff8e1; color: #b45309; border: 1px solid #fde68a; }
        .badge-reviewed    { background: #e0f7fa; color: #0369a1; border: 1px solid #b3e5fc; }
        .badge-in_progress { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-cleaned     { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-rejected    { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }

        /* ── Tables ─────────────────────────────────────────────────────── */
        .table { font-size: .9rem; }
        .table th { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: #64748b; }
        .table-hover tbody tr:hover { background: #f8fafc; }

        /* ── Responsive ─────────────────────────────────────────────────── */
        @media (max-width: 991.98px) {
            .sph-sidebar { transform: translateX(-100%); }
            .sph-sidebar.show { transform: translateX(0); }
            .sph-main { margin-left: 0; }
        }
    </style>

    <?= $extraHead ?? '' ?>
</head>
<body>

<!-- ═══════════════════════════════════ SIDEBAR ═════════════════════════════ -->
<aside class="sph-sidebar" id="sidebar">
    <div class="sph-brand d-flex align-items-center gap-2">
        <img src="<?= base_url($settings['app_logo'] ?? 'uploads/logo.png') ?>"
             alt="<?= esc($settings['app_name'] ?? 'SAMPAHAN') ?>">
        <div>
            <div class="brand-name"><?= esc($settings['app_name'] ?? 'SAMPAHAN') ?></div>
            <?php if (!empty($settings['city_name'])): ?>
            <div class="brand-sub"><?= esc($settings['city_name']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <nav class="sph-nav" id="sidebarNav">
        <?= $sidebarNav ?? '' ?>
    </nav>

    <div class="sph-sidebar-footer">
        &copy; <?= date('Y') ?> <?= esc($settings['app_name'] ?? 'SAMPAHAN') ?>
    </div>
</aside>

<!-- ═══════════════════════════════════ MAIN ════════════════════════════════ -->
<div class="sph-main d-flex flex-column">

    <!-- Topbar -->
    <nav class="sph-navbar d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-link d-lg-none p-0 text-dark" id="sidebarToggle">
                <i class="bi bi-list fs-3"></i>
            </button>
            <span class="text-muted small d-none d-lg-inline" style="font-size:.82rem;">
                <?= esc($settings['city_name'] ?? '') ?>
            </span>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <div class="sph-user-avatar"><?= strtoupper(substr($authUser['name'] ?? 'A', 0, 1)) ?></div>
                <div class="d-none d-md-block">
                    <div class="fw-semibold lh-1" style="font-size:.88rem;"><?= esc($authUser['name'] ?? '') ?></div>
                    <div class="text-muted" style="font-size:.72rem;text-transform:capitalize;"><?= esc($authUser['role'] ?? '') ?></div>
                </div>
            </div>
            <a href="<?= base_url('auth/logout') ?>" class="btn btn-outline-danger btn-sm px-3" title="Logout">
                <i class="bi bi-box-arrow-right"></i> <span class="d-none d-md-inline">Logout</span>
            </a>
        </div>
    </nav>

    <!-- Flash messages -->
    <div class="px-4 pt-3">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= esc(session()->getFlashdata('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    <?php foreach ((array) session()->getFlashdata('errors') as $err): ?>
                        <li><?= esc($err) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Page content injected here -->
    <main class="p-4 flex-grow-1">
        <?= $content ?>
    </main>

    <footer class="text-center text-muted small py-3 border-top bg-white">
        &copy; <?= date('Y') ?> <?= esc($settings['app_name'] ?? 'SAMPAHAN') ?> &mdash; <?= esc($settings['city_name'] ?? '') ?>
    </footer>
</div>

<!-- Sidebar overlay for mobile -->
<div class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-lg-none"
     id="sidebarOverlay" style="z-index:1029; display:none!important;"></div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
// ── Mobile sidebar toggle ────────────────────────────────────────────────────
const sidebar  = document.getElementById('sidebar');
const overlay  = document.getElementById('sidebarOverlay');
const toggler  = document.getElementById('sidebarToggle');

toggler?.addEventListener('click', () => {
    sidebar.classList.toggle('show');
    overlay.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
});
overlay?.addEventListener('click', () => {
    sidebar.classList.remove('show');
    overlay.style.display = 'none';
});
</script>

<?= $extraScripts ?? '' ?>
</body>
</html>
