<?php
$isEdit  = !empty($user);
$roleBadge = ['admin'=>'dark','dinas'=>'success','masyarakat'=>'primary'];
?>

<!-- Header -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h2 class="fw-bold mb-0">
            <?= $isEdit ? 'Edit User' : 'Tambah User Baru' ?>
        </h2>
        <?php if ($isEdit): ?>
        <p class="text-muted small mb-0"><?= esc($user['name']) ?></p>
        <?php endif; ?>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-person-badge me-2 text-success"></i>
                    <?= $isEdit ? 'Informasi Akun' : 'Data User Baru' ?>
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="<?= $isEdit ? base_url('admin/users/' . $user['id']) : base_url('admin/users') ?>"
                      method="POST">
                    <?= csrf_field() ?>

                    <!-- Name -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>"
                               value="<?= old('name', $user['name'] ?? '') ?>" required placeholder="Nama lengkap">
                        <?php if (session('errors.name')): ?>
                            <div class="invalid-feedback"><?= session('errors.name') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>"
                               value="<?= old('email', $user['email'] ?? '') ?>" required placeholder="email@contoh.com">
                        <?php if (session('errors.email')): ?>
                            <div class="invalid-feedback"><?= session('errors.email') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Password<?= $isEdit ? ' <span class="text-muted fw-normal small">(kosongkan jika tidak diubah)</span>' : ' <span class="text-danger">*</span>' ?>
                        </label>
                        <div class="input-group">
                            <input type="password" id="pwdField" name="password"
                                   class="form-control <?= session('errors.password') ? 'is-invalid' : '' ?>"
                                   <?= $isEdit ? '' : 'required' ?> placeholder="Min. 8 karakter" minlength="8">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePwd()"
                                    id="pwdToggle" title="Lihat password">
                                <i class="bi bi-eye" id="pwdIcon"></i>
                            </button>
                        </div>
                        <?php if (session('errors.password')): ?>
                            <div class="text-danger small mt-1"><?= session('errors.password') ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Role -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Peran (Role) <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <?php foreach (['admin' => 'Pemerintah', 'dinas' => 'Dinas', 'masyarakat' => 'Masyarakat'] as $val => $lbl): ?>
                            <option value="<?= $val ?>"
                                    <?= old('role', $user['role'] ?? 'masyarakat') === $val ? 'selected' : '' ?>>
                                <?= $lbl ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($isEdit): ?>
                    <!-- Active toggle -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Status Akun</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                   id="isActiveSwitch" <?= ($user['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <input type="hidden" name="is_active_present" value="1">
                            <label class="form-check-label" for="isActiveSwitch">Akun aktif</label>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Actions -->
                    <div class="d-flex gap-2 pt-2">
                        <button type="submit" class="btn btn-success px-4">
                            <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah User' ?>
                        </button>
                        <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary px-4">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
function togglePwd() {
    const f = document.getElementById('pwdField');
    const i = document.getElementById('pwdIcon');
    if (f.type === 'password') { f.type = 'text'; i.className = 'bi bi-eye-slash'; }
    else                       { f.type = 'password'; i.className = 'bi bi-eye'; }
}
</script>
<?php $extraScripts = ob_get_clean(); ?>