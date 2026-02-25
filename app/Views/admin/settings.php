<?php
$extraHead = '
<style>
.settings-icon { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center; }
.nav-tabs .nav-link { color:#64748b; font-weight:500; border:none; border-bottom:3px solid transparent; padding:.6rem 1.1rem; }
.nav-tabs .nav-link.active { color:#198754; border-bottom-color:#198754; background:transparent; }
.nav-tabs { border-bottom:2px solid #e2e8f0; }
.card-section-header { background:linear-gradient(135deg,#f8fafc,#f1f5f9); border-bottom:1px solid #e2e8f0; font-weight:600; padding:.85rem 1.25rem; border-radius:.75rem .75rem 0 0; }
</style>
';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0"><i class="bi bi-gear-fill text-success me-2"></i>Pengaturan Sistem</h2>
        <small class="text-muted">Konfigurasi white-label, peta, email, & umum</small>
    </div>
</div>

<ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-appearance" type="button">
            <i class="bi bi-palette me-1"></i>Tampilan
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-map" type="button">
            <i class="bi bi-map me-1"></i>Peta
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-mail" type="button">
            <i class="bi bi-envelope me-1"></i>Email / SMTP
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-general" type="button">
            <i class="bi bi-sliders me-1"></i>Umum
        </button>
    </li>
</ul>

<div class="tab-content">

    <!--  TAMPILAN  -->
    <div class="tab-pane fade show active" id="tab-appearance">

        <form action="<?= base_url('admin/settings') ?>" method="POST" class="mb-4">
            <?= csrf_field() ?>
            <input type="hidden" name="_tab" value="#tab-appearance">
            <div class="card">
                <div class="card-section-header"><i class="bi bi-building text-success me-2"></i>Identitas Aplikasi</div>
                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Aplikasi <span class="text-danger">*</span></label>
                        <input type="text" name="app_name" class="form-control form-control-lg"
                               value="<?= esc($appearance['app_name'] ?? 'SAMPAHAN') ?>" required>
                        <small class="text-muted">Tampil di navbar, email, dan halaman utama.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Kota / Wilayah</label>
                        <input type="text" name="city_name" class="form-control form-control-lg"
                               value="<?= esc($appearance['city_name'] ?? '') ?>">
                        <small class="text-muted">Contoh: Kota Bogor</small>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-save me-1"></i>Simpan Identitas</button>
                </div>
            </div>
        </form>

        <form action="<?= base_url('admin/settings/upload-logo') ?>" method="POST" enctype="multipart/form-data" class="mb-4">
            <?= csrf_field() ?>
            <div class="card">
                <div class="card-section-header"><i class="bi bi-image text-primary me-2"></i>Logo Aplikasi</div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-4 mb-3">
                        <img src="<?= base_url($settings['app_logo'] ?? 'uploads/logo.png') ?>"
                             id="logoPreview" height="60" class="border rounded p-1 bg-white" alt="Logo">
                        <div>
                            <div class="fw-semibold">Logo Saat Ini</div>
                            <small class="text-muted"><?= esc($settings['app_logo'] ?? 'uploads/logo.png') ?></small>
                        </div>
                    </div>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Logo Baru (PNG/JPG/WebP)</label>
                            <input type="file" name="app_logo" class="form-control" accept="image/*" required
                                   onchange="previewLogo(this)">
                            <small class="text-muted">Rekomendasi: 200x60 px, background transparan.</small>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Upload Logo</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <form action="<?= base_url('admin/settings/upload-favicon') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="card">
                <div class="card-section-header"><i class="bi bi-badge-cc text-info me-2"></i>Favicon</div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-4 mb-3">
                        <img src="<?= base_url($settings['app_favicon'] ?? 'uploads/favicon.ico') ?>"
                             height="32" class="border rounded p-1 bg-white" alt="Favicon">
                        <div>
                            <div class="fw-semibold">Favicon Saat Ini</div>
                            <small class="text-muted"><?= esc($settings['app_favicon'] ?? 'uploads/favicon.ico') ?></small>
                        </div>
                    </div>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Favicon Baru (ICO/PNG, 32x32 px)</label>
                            <input type="file" name="app_favicon" class="form-control" accept="image/*" required>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Upload Favicon</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>

    <!--  PETA  -->
    <div class="tab-pane fade" id="tab-map">

        <form action="<?= base_url('admin/settings') ?>" method="POST" class="mb-4">
            <?= csrf_field() ?>
            <input type="hidden" name="_tab" value="#tab-map">
            <div class="card">
                <div class="card-section-header"><i class="bi bi-compass text-primary me-2"></i>Titik Tengah & Zoom Default</div>
                <div class="card-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Latitude Pusat</label>
                        <input type="text" name="map_center_lat" class="form-control"
                               value="<?= esc($map['map_center_lat'] ?? '-6.2884') ?>" placeholder="-6.2884">
                        <small class="text-muted">Gunakan titik sebagai pemisah desimal.</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Longitude Pusat</label>
                        <input type="text" name="map_center_long" class="form-control"
                               value="<?= esc($map['map_center_long'] ?? '106.7135') ?>" placeholder="106.7135">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Zoom Default</label>
                        <input type="number" name="map_default_zoom" class="form-control"
                               value="<?= esc($map['map_default_zoom'] ?? '12') ?>" min="5" max="18">
                        <small class="text-muted">Nilai 5-18 (12 = rekomendasi kota).</small>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-save me-1"></i>Simpan Konfigurasi Peta</button>
                </div>
            </div>
        </form>

        <form action="<?= base_url('admin/settings/upload-geojson') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="card">
                <div class="card-section-header">
                    <i class="bi bi-geo-alt text-danger me-2"></i>Batas Wilayah (GeoJSON)
                    <span class="badge bg-info ms-2 fw-normal">Validasi lokasi & masking peta</span>
                </div>
                <div class="card-body">
                    <div class="alert alert-info d-flex gap-2 align-items-start py-2 mb-3">
                        <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                        <span>Upload file <code>.geojson</code>/<code>.json</code> <strong>atau</strong> paste isi GeoJSON langsung. Jika keduanya diisi, file akan diprioritaskan.</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Upload File GeoJSON</label>
                        <input type="file" name="geojson_file" class="form-control" accept=".geojson,.json">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Atau Paste GeoJSON</label>
                        <textarea name="city_boundary_geojson" class="form-control font-monospace" rows="10"
                                  placeholder='{"type":"FeatureCollection","features":[]}'
                                  style="font-size:.8rem;"><?= esc($map['city_boundary_geojson'] ?? '') ?></textarea>
                    </div>
                    <?php if (!empty($map['city_boundary_geojson'])): ?>
                    <div class="alert alert-success py-2 d-flex gap-2 align-items-center">
                        <i class="bi bi-check-circle-fill"></i>
                        GeoJSON aktif tersimpan &mdash; <strong><?= number_format(strlen($map['city_boundary_geojson'])) ?></strong> karakter.
                    </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-transparent">
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-geo-alt me-1"></i>Simpan Batas Wilayah</button>
                </div>
            </div>
        </form>

    </div>

    <!--  EMAIL / SMTP  -->
    <div class="tab-pane fade" id="tab-mail">
        <form action="<?= base_url('admin/settings') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="_tab" value="#tab-mail">
            <div class="card">
                <div class="card-section-header">
                    <i class="bi bi-envelope-at text-warning me-2"></i>Konfigurasi SMTP
                    <span class="badge bg-warning text-dark ms-2 fw-normal">Dikonfigurasi via DB</span>
                </div>
                <div class="card-body row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-control"
                               value="<?= esc($mail['smtp_host'] ?? '') ?>" placeholder="smtp.gmail.com">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">SMTP Port</label>
                        <input type="number" name="smtp_port" class="form-control"
                               value="<?= esc($mail['smtp_port'] ?? '587') ?>" placeholder="587">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Username / Email Pengirim</label>
                        <input type="email" name="smtp_user" class="form-control"
                               value="<?= esc($mail['smtp_user'] ?? '') ?>" placeholder="noreply@domain.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Password SMTP</label>
                        <div class="input-group">
                            <input type="password" name="smtp_pass" id="smtpPassInput" class="form-control"
                                   value="<?= esc($mail['smtp_pass'] ?? '') ?>" placeholder="App password / SMTP password">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePass()">
                                <i class="bi bi-eye" id="passEyeIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Enkripsi</label>
                        <select name="smtp_crypto" class="form-select">
                            <option value="tls"  <?= ($mail['smtp_crypto'] ?? 'tls') === 'tls'  ? 'selected' : '' ?>>TLS (Rekomendasi)</option>
                            <option value="ssl"  <?= ($mail['smtp_crypto'] ?? '') === 'ssl'  ? 'selected' : '' ?>>SSL</option>
                            <option value="none" <?= ($mail['smtp_crypto'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Nama Pengirim</label>
                        <input type="text" name="smtp_from_name" class="form-control"
                               value="<?= esc($mail['smtp_from_name'] ?? '') ?>" placeholder="SAMPAHAN System">
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-save me-1"></i>Simpan Konfigurasi Email</button>
                </div>
            </div>
        </form>
    </div>

    <!--  UMUM  -->
    <div class="tab-pane fade" id="tab-general">
        <form action="<?= base_url('admin/settings') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="_tab" value="#tab-general">
            <div class="card">
                <div class="card-section-header"><i class="bi bi-toggles text-secondary me-2"></i>Pengaturan Umum</div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 h-100">
                            <div class="d-flex align-items-start gap-3">
                                <div class="settings-icon bg-primary bg-opacity-10">
                                    <i class="bi bi-envelope-check text-primary fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold mb-1">Verifikasi Email</div>
                                    <small class="text-muted d-block mb-2">Jika aktif, akun masyarakat baru harus verifikasi email sebelum login.</small>
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="enable_email_verification" value="0">
                                        <input class="form-check-input" type="checkbox"
                                               name="enable_email_verification" id="emailVerifSwitch" value="1"
                                               <?= ($general['enable_email_verification'] ?? '0') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-semibold text-success" for="emailVerifSwitch">Aktifkan</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 h-100">
                            <div class="d-flex align-items-start gap-3">
                                <div class="settings-icon bg-danger bg-opacity-10">
                                    <i class="bi bi-geo-alt text-danger fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold mb-1">Radius Deteksi Duplikat</div>
                                    <small class="text-muted d-block mb-2">Laporan dalam radius ini dianggap duplikat dari laporan aktif yang sudah ada.</small>
                                    <div class="input-group" style="max-width:180px;">
                                        <input type="number" name="duplicate_radius_meters" class="form-control"
                                               min="1" max="500" step="1"
                                               value="<?= esc($general['duplicate_radius_meters'] ?? '10') ?>">
                                        <span class="input-group-text">meter</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 h-100">
                            <div class="d-flex align-items-start gap-3">
                                <div class="settings-icon bg-success bg-opacity-10">
                                    <i class="bi bi-clock text-success fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold mb-1">Timezone Sistem</div>
                                    <small class="text-muted d-block mb-2">Digunakan untuk tampilan waktu di seluruh aplikasi (notifikasi, timestamp laporan, dll).</small>
                                    <select name="app_timezone" class="form-select form-select-sm">
                                        <?php
                                        $currentTz = $general['app_timezone'] ?? 'Asia/Jakarta';
                                        $tzGroups = [
                                            'Asia (Indonesia)' => [
                                                'Asia/Jakarta'    => 'WIB — Asia/Jakarta',
                                                'Asia/Makassar'   => 'WITA — Asia/Makassar',
                                                'Asia/Jayapura'   => 'WIT — Asia/Jayapura',
                                            ],
                                            'Asia (Lainnya)' => [
                                                'Asia/Singapore'  => 'Asia/Singapore',
                                                'Asia/Kuala_Lumpur' => 'Asia/Kuala_Lumpur',
                                                'Asia/Bangkok'    => 'Asia/Bangkok',
                                                'Asia/Tokyo'      => 'Asia/Tokyo',
                                                'Asia/Dubai'      => 'Asia/Dubai',
                                            ],
                                            'UTC' => [
                                                'UTC'             => 'UTC',
                                            ],
                                            'Lainnya' => [
                                                'Europe/London'   => 'Europe/London',
                                                'America/New_York'=> 'America/New_York',
                                                'America/Los_Angeles' => 'America/Los_Angeles',
                                            ],
                                        ];
                                        foreach ($tzGroups as $groupLabel => $tzList): ?>
                                            <optgroup label="<?= esc($groupLabel) ?>">
                                                <?php foreach ($tzList as $tz => $label): ?>
                                                    <option value="<?= $tz ?>" <?= $currentTz === $tz ? 'selected' : '' ?>><?= $label ?></option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-save me-1"></i>Simpan Pengaturan</button>
                </div>
            </div>
        </form>
    </div>

</div>

<script>
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('logoPreview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
function togglePass() {
    const inp = document.getElementById('smtpPassInput');
    const ico = document.getElementById('passEyeIcon');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    ico.className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
(function() {
    const hash = window.location.hash;
    if (hash) {
        const btn = document.querySelector(`[data-bs-target="${hash}"]`);
        if (btn) bootstrap.Tab.getOrCreateInstance(btn).show();
    }
})();
</script>
