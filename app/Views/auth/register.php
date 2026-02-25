<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar – <?= esc($settings['app_name'] ?? 'SAMPAHAN') ?></title>
    <link rel="icon" href="<?= base_url($settings['app_favicon'] ?? 'uploads/favicon.ico') ?>" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1a2e40 0%, #198754 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { width: 100%; max-width: 460px; border-radius: 1rem; overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,.25); }
        .auth-header { background: #1a2e40; text-align: center; padding: 2rem 1.5rem; }
        .auth-header img { height: 55px; object-fit: contain; }
        .form-control { font-size: 1.05rem; padding: .7rem 1rem; }
        .btn-register { font-size: 1.1rem; padding: .75rem; border-radius: .6rem; }
        #passwordStrength { height: 6px; border-radius: 3px; transition: width .3s, background .3s; }
    </style>
</head>
<body>
<div class="auth-card bg-white">
    <div class="auth-header">
        <img src="<?= base_url($settings['app_logo'] ?? 'uploads/logo.png') ?>"
             alt="<?= esc($settings['app_name'] ?? 'SAMPAHAN') ?>">
        <h5 class="text-white mt-2 mb-0">Daftar Akun</h5>
        <small class="text-secondary"><?= esc($settings['app_name'] ?? 'SAMPAHAN') ?></small>
    </div>

    <div class="p-4">
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    <?php foreach ((array) session()->getFlashdata('errors') as $err): ?>
                        <li><?= esc($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <form action="<?= base_url('auth/register') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Lengkap</label>
                <input type="text" name="name" class="form-control"
                       placeholder="Ahmad Budi" value="<?= old('name') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control"
                       placeholder="email@example.com" value="<?= old('email') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" id="password" class="form-control"
                       placeholder="Min. 8 karakter" required>
                <div class="bg-light rounded mt-1">
                    <div id="passwordStrength" style="width:0"></div>
                </div>
                <small id="strengthLabel" class="text-muted"></small>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Konfirmasi Password</label>
                <input type="password" name="password_confirm" class="form-control"
                       placeholder="Ulangi password" required>
            </div>

            <button type="submit" class="btn btn-success w-100 btn-register">
                <i class="bi bi-person-check"></i> Daftar Sekarang
            </button>
        </form>

        <hr class="my-3">
        <p class="text-center text-muted small mb-0">
            Sudah punya akun?
            <a href="<?= base_url('auth/login') ?>" class="text-success fw-semibold">Masuk</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Password strength indicator
document.getElementById('password').addEventListener('input', function () {
    const v = this.value;
    const bar = document.getElementById('passwordStrength');
    const lbl = document.getElementById('strengthLabel');
    let score = 0;
    if (v.length >= 8)  score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    const levels = [
        {w: '0%',   bg: '#dc3545', text: ''},
        {w: '25%',  bg: '#dc3545', text: 'Lemah'},
        {w: '50%',  bg: '#ffc107', text: 'Cukup'},
        {w: '75%',  bg: '#0d6efd', text: 'Kuat'},
        {w: '100%', bg: '#198754', text: 'Sangat Kuat'},
    ];
    const lvl = levels[score] ?? levels[0];
    bar.style.width = lvl.w;
    bar.style.background = lvl.bg;
    lbl.textContent = lvl.text;
    lbl.style.color = lvl.bg;
});
</script>
</body>
</html>
