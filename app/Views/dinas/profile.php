<?php
$name    = $user['name']    ?? '';
$email   = $user['email']   ?? '';
$role    = $user['role']    ?? 'dinas';
$initial = mb_strtoupper(mb_substr($name, 0, 1));
$joined  = isset($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : '-';
?>
<div class="mb-4">
    <h2 class="fw-bold mb-0"><i class="bi bi-person-circle text-success me-2"></i>Profil Saya</h2>
    <p class="text-muted small mb-0">Kelola informasi akun dan keamanan Anda.</p>
</div>
<div class="row g-4">
  <div class="col-12 col-md-4 col-lg-3">
    <div class="card border-0 shadow-sm text-center">
      <div class="card-body d-flex flex-column align-items-center py-4">
        <div class="rounded-circle d-flex align-items-center justify-content-center mb-3"
             style="width:88px;height:88px;background:linear-gradient(135deg,#198754,#0d6efd);color:#fff;font-size:2.2rem;font-weight:700;">
            <?= esc($initial) ?>
        </div>
        <h5 class="fw-bold mb-1"><?= esc($name) ?></h5>
        <p class="text-muted small mb-2"><?= esc($email) ?></p>
        <span class="badge bg-success mb-3">Dinas</span>
        <hr class="w-100 my-2">
        <div class="w-100 text-start">
          <div class="d-flex justify-content-between small text-muted mb-1">
            <span><i class="bi bi-calendar3 me-1"></i>Bergabung</span>
            <span class="fw-semibold text-dark"><?= $joined ?></span>
          </div>
          <div class="d-flex justify-content-between small text-muted">
            <span><i class="bi bi-shield-check me-1"></i>Status</span>
            <span class="badge bg-success">Aktif</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-8 col-lg-9">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3 px-4">
        <ul class="nav nav-tabs card-header-tabs" id="profileTabs">
          <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabInfo">
              <i class="bi bi-person me-1"></i>Informasi
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabPassword">
              <i class="bi bi-lock me-1"></i>Keamanan
            </button>
          </li>
        </ul>
      </div>
      <div class="card-body p-4 tab-content">
        <div class="tab-pane fade show active" id="tabInfo">
          <?php if (session('success')): ?>
          <div class="alert alert-success alert-dismissible fade show py-2">
            <i class="bi bi-check-circle me-1"></i><?= session('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          <?php endif; ?>
          <?php if (session('error')): ?>
          <div class="alert alert-danger alert-dismissible fade show py-2">
            <i class="bi bi-exclamation-circle me-1"></i><?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
          <?php endif; ?>
          <form action="<?= base_url('dinas/profile') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="_update" value="info">
            <div class="row g-3">
              <div class="col-12 col-sm-6">
                <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?= esc(old('name', $name)) ?>" required>
              </div>
              <div class="col-12 col-sm-6">
                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="<?= esc(old('email', $email)) ?>" required>
              </div>
            </div>
            <div class="mt-4">
              <button type="submit" class="btn btn-success px-4"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
            </div>
          </form>
        </div>
        <div class="tab-pane fade" id="tabPassword">
          <form action="<?= base_url('dinas/profile') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="_update" value="password">
            <div class="mb-3">
              <label class="form-label fw-semibold">Password Saat Ini</label>
              <div class="input-group">
                <input type="password" id="pwdCurrent" name="current_password" class="form-control" placeholder="Password lama" required>
                <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('pwdCurrent','iconCurrent')"><i class="bi bi-eye" id="iconCurrent"></i></button>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Password Baru</label>
              <div class="input-group">
                <input type="password" id="pwdNew" name="password" class="form-control" placeholder="Min. 8 karakter" minlength="8" required>
                <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('pwdNew','iconNew')"><i class="bi bi-eye" id="iconNew"></i></button>
              </div>
            </div>
            <div class="mb-4">
              <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
              <div class="input-group">
                <input type="password" id="pwdConfirm" name="password_confirm" class="form-control" placeholder="Ulangi password baru" minlength="8" required>
                <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('pwdConfirm','iconConfirm')"><i class="bi bi-eye" id="iconConfirm"></i></button>
              </div>
            </div>
            <button type="submit" class="btn btn-success px-4"><i class="bi bi-shield-lock me-1"></i> Ubah Password</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php ob_start(); ?>
<script>
function togglePwd(f,i){const el=document.getElementById(f),ic=document.getElementById(i);if(el.type==='password'){el.type='text';ic.className='bi bi-eye-slash';}else{el.type='password';ic.className='bi bi-eye';}}
</script>
<?php $extraScripts = ob_get_clean(); ?>