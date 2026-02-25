<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password – <?= esc($settings['app_name'] ?? 'SAMPAHAN') ?></title>
    <link rel="icon" href="<?= base_url($settings['app_favicon'] ?? 'uploads/favicon.ico') ?>" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg,#1a2e40 0%,#198754 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .card { max-width:420px; width:100%; border-radius:1rem; box-shadow:0 8px 32px rgba(0,0,0,.25); }
        .form-control { font-size:1.05rem; padding:.75rem; }
    </style>
</head>
<body>
<div class="card p-4">
    <div class="text-center mb-4">
        <img src="<?= base_url($settings['app_logo'] ?? 'uploads/logo.png') ?>" height="50" alt="">
        <h5 class="fw-bold mt-2">Lupa Password</h5>
        <p class="text-muted small">Masukkan email terdaftar. Link reset akan dikirimkan.</p>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <form action="<?= base_url('auth/forgot-password') ?>" method="POST">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label fw-semibold">Alamat Email</label>
            <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
        </div>
        <button type="submit" class="btn btn-success w-100 py-2 fs-5">
            Kirim Link Reset
        </button>
    </form>

    <div class="text-center mt-3">
        <a href="<?= base_url('auth/login') ?>" class="small text-success">← Kembali ke Login</a>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
