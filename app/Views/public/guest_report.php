<?php
$appName  = $settings['app_name']  ?? 'SAMPAHAN';
$cityName = $settings['city_name'] ?? '';

$extraHead = '
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
';

$extraStyle = '
#map { height: 380px; border-radius: .75rem; border: 1px solid #dee2e6; }
.leaflet-container { border-radius: .75rem; }
.loc-badge { background:#e8f5e9; color:#198754; border-radius:999px; padding:.25rem .75rem; font-size:.8rem; font-weight:600; }
.loc-badge.no-loc { background:#fff3cd; color:#856404; }
.form-card { border-radius:1rem; border:1px solid #e2e8f0; background:#fff; padding:2rem; box-shadow:0 4px 24px rgba(0,0,0,.06); }
';
?>

<section class="py-5" style="background:linear-gradient(135deg,#f0fdf4 0%,#eff6ff 100%);min-height:calc(100vh - 72px);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <!-- Header -->
                <div class="text-center mb-4">
                    <span class="badge bg-success mb-2" style="border-radius:999px;padding:.4rem 1rem;font-size:.8rem;letter-spacing:.5px;text-transform:uppercase;">
                        <i class="bi bi-flag-fill me-1"></i> Laporkan Sampah
                    </span>
                    <h2 class="fw-bold mb-1">Laporkan Sampah di Sekitar Anda</h2>
                    <p class="text-muted"><?= $cityName ? "Wilayah {$cityName}" : $appName ?> &mdash; Tidak perlu akun, laporan Anda langsung diterima.</p>
                </div>

                <!-- Flash messages -->
                <?php if (session()->has('success')): ?>
                <div class="alert alert-success alert-dismissible d-flex gap-2 align-items-center mb-4" role="alert">
                    <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                    <div><?= esc(session('success')) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (session()->has('error')): ?>
                <div class="alert alert-danger alert-dismissible d-flex gap-2 align-items-center mb-4" role="alert">
                    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                    <div><?= esc(session('error')) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (session()->has('errors')): ?>
                <div class="alert alert-danger mb-4">
                    <strong><i class="bi bi-exclamation-triangle me-1"></i>Terdapat kesalahan:</strong>
                    <ul class="mb-0 mt-1 ps-3">
                        <?php foreach ((array)session('errors') as $err): ?>
                        <li><?= esc($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form action="<?= base_url('laporkan-sampah') ?>" method="POST"
                      enctype="multipart/form-data" id="guestReportForm" novalidate>
                    <?= csrf_field() ?>

                    <div class="form-card mb-4">
                        <!-- ── Section: Lokasi ─────────────────────────────── -->
                        <h6 class="fw-bold mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Lokasi Sampah</h6>
                        <p class="text-muted small mb-3">Klik tombol GPS atau klik langsung pada peta untuk menandai lokasi sampah.</p>

                        <!-- GPS button + badge -->
                        <div class="d-flex gap-2 align-items-center mb-3 flex-wrap">
                            <button type="button" id="btnGps" class="btn btn-outline-success btn-sm">
                                <i class="bi bi-crosshair me-1"></i> Gunakan Lokasi Saya
                            </button>
                            <span id="locBadge" class="loc-badge no-loc">
                                <i class="bi bi-question-circle me-1"></i> Belum ada lokasi
                            </span>
                        </div>

                        <!-- Map -->
                        <div id="map" class="mb-3"></div>

                        <!-- Hidden coordinate inputs -->
                        <input type="hidden" name="latitude"  id="latInput"  value="<?= old('latitude') ?>">
                        <input type="hidden" name="longitude" id="lngInput"  value="<?= old('longitude') ?>">
                    </div>

                    <div class="form-card mb-4">
                        <!-- ── Section: Foto ───────────────────────────────── -->
                        <h6 class="fw-bold mb-1"><i class="bi bi-camera-fill text-primary me-1"></i> Foto Sampah</h6>
                        <p class="text-muted small mb-3">Ambil atau upload foto terkini kondisi sampah (maks. 5 MB).</p>

                        <div class="mb-3">
                            <input type="file" name="photo" id="photoInput" class="form-control"
                                   accept="image/*" capture="environment" required
                                   onchange="previewPhoto(this)">
                            <small class="text-muted">Format: JPG, PNG, WebP</small>
                        </div>

                        <!-- Preview -->
                        <div id="photoPreviewWrap" class="d-none mb-2">
                            <img id="photoPreview" src="#" alt="Preview"
                                 class="img-thumbnail" style="max-height:200px;object-fit:cover;border-radius:.6rem;">
                        </div>
                    </div>

                    <div class="form-card mb-4">
                        <!-- ── Section: Deskripsi ─────────────────────────── -->
                        <h6 class="fw-bold mb-1"><i class="bi bi-chat-left-text text-warning me-1"></i> Keterangan (Opsional)</h6>
                        <div class="mb-3">
                            <textarea name="description" id="description" class="form-control"
                                      rows="3" maxlength="1000"
                                      placeholder="Contoh: Tumpukan sampah besar di pinggir jalan, berhari-hari tidak diangkut."
                            ><?= old('description') ?></textarea>
                            <small class="text-muted"><span id="charCount">0</span>/1000 karakter</small>
                        </div>
                    </div>

                    <div class="form-card mb-4">
                        <!-- ── Section: Identitas (opsional) ─────────────── -->
                        <h6 class="fw-bold mb-1"><i class="bi bi-person-fill text-secondary me-1"></i> Identitas Pelapor <span class="text-muted fw-normal">(Opsional)</span></h6>
                        <p class="text-muted small mb-3">Tidak wajib diisi. Nama dan nomor HP tidak akan ditampilkan publik.</p>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama</label>
                                <input type="text" name="guest_name" class="form-control"
                                       maxlength="150" placeholder="Nama Anda"
                                       value="<?= old('guest_name') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. HP / WhatsApp</label>
                                <input type="text" name="guest_phone" class="form-control"
                                       maxlength="50" placeholder="08xx-xxxx-xxxx"
                                       value="<?= old('guest_phone') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- ── Captcha ──────────────────────────────────────────── -->
                    <?php if ($captcha->isEnabled()): ?>
                    <div class="form-card mb-4">
                        <h6 class="fw-bold mb-2"><i class="bi bi-shield-check text-info me-1"></i> Verifikasi Keamanan</h6>
                        <?= $captcha->widgetHtml($captchaQuestion ?? '') ?>
                    </div>
                    <?php endif; ?>

                    <!-- ── Submit ───────────────────────────────────────────── -->
                    <div class="d-grid">
                        <button type="submit" id="submitBtn" class="btn btn-success btn-lg" disabled>
                            <i class="bi bi-send-fill me-2"></i> Kirim Laporan
                        </button>
                        <small class="text-center text-muted mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Tombol aktif setelah lokasi ditentukan dan foto dipilih.
                        </small>
                    </div>
                </form>

                <!-- Login hint -->
                <div class="text-center mt-4">
                    <small class="text-muted">
                        Punya akun? <a href="<?= base_url('auth/login') ?>" class="text-success fw-semibold">Masuk</a>
                        untuk melacak riwayat laporan Anda.
                    </small>
                </div>

            </div>
        </div>
    </div>
</section>

<?php
$extraScripts = <<<'JS'
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    // ── Map init ─────────────────────────────────────────────────────────────
    const defLat  = parseFloat(document.getElementById('latInput').value  || '<?= $mapLat ?>');
    const defLng  = parseFloat(document.getElementById('lngInput').value  || '<?= $mapLng ?>');
    const defZoom = <?= (int)$mapZoom ?>;

    const map = L.map('map').setView([defLat, defLng], defZoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map);

    let marker = null;

    function setLocation(lat, lng) {
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng], {
            icon: L.icon({
                iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34],
                shadowSize: [41, 41],
            }),
        }).addTo(map);

        document.getElementById('latInput').value = lat.toFixed(7);
        document.getElementById('lngInput').value = lng.toFixed(7);

        const badge = document.getElementById('locBadge');
        badge.className = 'loc-badge';
        badge.innerHTML = `<i class="bi bi-geo-alt-fill me-1"></i>${lat.toFixed(5)}, ${lng.toFixed(5)}`;
        checkReady();
    }

    // Click on map
    map.on('click', e => setLocation(e.latlng.lat, e.latlng.lng));

    // GPS button
    document.getElementById('btnGps').addEventListener('click', function () {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mendeteksi…';

        navigator.geolocation.getCurrentPosition(
            pos => {
                setLocation(pos.coords.latitude, pos.coords.longitude);
                map.setView([pos.coords.latitude, pos.coords.longitude], 16);
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-crosshair me-1"></i> Gunakan Lokasi Saya';
            },
            () => {
                alert('Tidak dapat mengakses lokasi. Pastikan izin lokasi diaktifkan.');
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-crosshair me-1"></i> Gunakan Lokasi Saya';
            },
            { timeout: 10000 }
        );
    });

    // Pre-fill from old() if set
    const prefilledLat = document.getElementById('latInput').value;
    const prefilledLng = document.getElementById('lngInput').value;
    if (prefilledLat && prefilledLng) {
        setLocation(parseFloat(prefilledLat), parseFloat(prefilledLng));
        map.setView([parseFloat(prefilledLat), parseFloat(prefilledLng)], 16);
    }

    // ── Photo preview & submit gate ───────────────────────────────────────────
    function checkReady() {
        const hasLoc   = document.getElementById('latInput').value !== '';
        const hasPhoto = document.getElementById('photoInput').files.length > 0;
        document.getElementById('submitBtn').disabled = !(hasLoc && hasPhoto);
    }

    document.getElementById('photoInput').addEventListener('change', checkReady);

    window.previewPhoto = function (input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('photoPreview').src = e.target.result;
                document.getElementById('photoPreviewWrap').classList.remove('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    // ── Description character counter ────────────────────────────────────────
    const descEl = document.getElementById('description');
    descEl.addEventListener('input', () => {
        document.getElementById('charCount').textContent = descEl.value.length;
    });
    document.getElementById('charCount').textContent = descEl.value.length;
})();
</script>
JS;
?>
