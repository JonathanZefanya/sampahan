<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – <?= esc($settings['app_name'] ?? 'SAMPAHAN') ?></title>
    <link rel="icon" href="<?= base_url($settings['app_favicon'] ?? 'uploads/favicon.ico') ?>" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a2e40 0%, #198754 100%);
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
        }
        .auth-card {
            width: 100%; max-width: 420px;
            border-radius: 1rem; overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,.25);
        }
        .auth-header {
            background: #1a2e40; text-align: center; padding: 2rem 1.5rem;
        }
        .auth-header img { height: 60px; object-fit: contain; }
        .auth-body { padding: 2rem; }
        .form-control { font-size: 1.05rem; padding: .75rem 1rem; }
        .btn-login { font-size: 1.1rem; padding: .8rem; border-radius: .6rem; }
    </style>
</head>
<body>

<div class="auth-card bg-white">
    <div class="auth-header">
        <img src="<?= base_url($settings['app_logo'] ?? 'uploads/logo.png') ?>"
             alt="<?= esc($settings['app_name'] ?? 'SAMPAHAN') ?>">
        <h5 class="text-white mt-2 mb-0"><?= esc($settings['app_name'] ?? 'SAMPAHAN') ?></h5>
        <small class="text-secondary"><?= esc($settings['city_name'] ?? '') ?></small>
    </div>

    <div class="auth-body">
        <h4 class="fw-bold mb-1">Masuk ke Sistem</h4>
        <p class="text-muted small mb-4">Gunakan akun yang terdaftar untuk melanjutkan.</p>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    <?php foreach ((array) session()->getFlashdata('errors') as $err): ?>
                        <li><?= esc($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('auth/login') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control"
                           placeholder="email@example.com"
                           value="<?= old('email') ?>" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control"
                           placeholder="••••••••" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePwd">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-3">
                <a href="<?= base_url('auth/forgot-password') ?>" class="small text-success">
                    Lupa password?
                </a>
            </div>

            <button type="submit" class="btn btn-success w-100 btn-login">
                <i class="bi bi-box-arrow-in-right"></i> Masuk
            </button>
        </form>

        <hr class="my-4">
        <p class="text-center text-muted small mb-0">
            Belum punya akun?
            <a href="<?= base_url('auth/register') ?>" class="text-success fw-semibold">Daftar sekarang</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('togglePwd').addEventListener('click', function () {
    const pwd = document.querySelector('input[name="password"]');
    const icon = this.querySelector('i');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        pwd.type = 'password';
        icon.className = 'bi bi-eye';
    }
});
</script>
</body>
</html>
