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
                        <div id="map" class="mb-3" style="height:380px;min-height:200px;"></div>

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
                                accept=".jpg,.jpeg,.png,.webp"
                                capture="environment" required
                                onchange="previewPhoto(this)">

                            <small class="text-muted">Format: JPG, PNG, WebP</small>
                        </div>

                        <!-- Preview -->
                        <div id="photoPreviewWrap" class="d-none mb-2">
                            <img id="photoPreview" src="#" alt="Preview"
                                class="img-thumbnail"
                                style="max-height:200px;object-fit:cover;border-radius:.6rem;">
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
                        <div class="form-check mt-2">
                            <input type="checkbox" name="agree_terms" id="agreeTerms" class="form-check-input" required>
                            <label for="agreeTerms" class="form-check-label small text-muted">
                                Dengan mengirim laporan, saya menyetujui
                                <a href="#" class="text-success fw-semibold" data-bs-toggle="modal" data-bs-target="#privacyModal">Kebijakan Privasi</a>
                                dan
                                <a href="#" class="text-success fw-semibold" data-bs-toggle="modal" data-bs-target="#termsModal">Syarat &amp; Ketentuan</a>.
                            </label>
                        </div>
                        <br>
                        <small class="text-center text-muted mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Tombol aktif setelah lokasi ditentukan, foto dipilih, dan persetujuan dicentang.
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

<!-- ── Modal: Kebijakan Privasi ─────────────────────────────────────────────── -->
<div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content" style="border-radius:1rem;border:none;">
            <div class="modal-header bg-success bg-opacity-10 border-0" style="border-radius:1rem 1rem 0 0;">
                <h5 class="modal-title fw-bold" id="privacyModalLabel">
                    <i class="bi bi-shield-lock-fill text-success me-2"></i>Kebijakan Privasi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body small lh-lg">
                <p class="text-muted mb-3">
                    Kebijakan ini menjelaskan data apa saja yang dikumpulkan <?= esc($appName) ?> saat Anda
                    mengirim laporan sampah sebagai tamu, dan bagaimana data tersebut digunakan.
                </p>

                <h6 class="fw-bold">1. Data yang Dikumpulkan</h6>
                <ul>
                    <li><strong>Titik lokasi (koordinat)</strong> sampah yang Anda tandai pada peta atau ambil dari GPS perangkat.</li>
                    <li><strong>Foto sampah</strong> yang Anda unggah.</li>
                    <li><strong>Keterangan</strong> tambahan yang Anda tulis (opsional).</li>
                    <li><strong>Nama dan nomor HP/WhatsApp</strong> (opsional, hanya jika Anda mengisinya).</li>
                    <li><strong>Alamat IP</strong> dan waktu pengiriman, untuk pencegahan spam.</li>
                </ul>

                <h6 class="fw-bold">2. Penggunaan Data</h6>
                <p>
                    Data digunakan untuk memverifikasi, menugaskan, dan menindaklanjuti penanganan sampah
                    <?= $cityName ? 'di wilayah ' . esc($cityName) : '' ?>, serta untuk statistik agregat
                    (jumlah laporan, titik rawan berulang) yang tidak mengidentifikasi individu.
                </p>

                <h6 class="fw-bold">3. Data yang Ditampilkan Publik</h6>
                <p>
                    Pada peta publik hanya ditampilkan titik lokasi, foto, keterangan, dan status laporan.
                    <strong>Nama dan nomor HP pelapor tidak pernah ditampilkan publik</strong> dan hanya dapat
                    dilihat oleh petugas serta administrator untuk keperluan konfirmasi.
                </p>

                <h6 class="fw-bold">4. Penyimpanan &amp; Keamanan</h6>
                <p>
                    Data disimpan pada server pengelola dengan akses terbatas pada petugas berwenang.
                    Foto dan koordinat disimpan selama laporan masih dibutuhkan untuk pemantauan dan pelaporan.
                </p>

                <h6 class="fw-bold">5. Berbagi ke Pihak Ketiga</h6>
                <p>
                    Data tidak diperjualbelikan. Data hanya dapat dibagikan kepada instansi terkait penanganan
                    kebersihan atau bila diwajibkan oleh peraturan perundang-undangan.
                </p>

                <h6 class="fw-bold">6. Hak Anda</h6>
                <p>
                    Anda dapat meminta koreksi atau penghapusan data laporan yang Anda kirim dengan menghubungi
                    administrator melalui kanal kontak resmi <?= esc($appName) ?>.
                </p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Saya Mengerti</button>
            </div>
        </div>
    </div>
</div>

<!-- ── Modal: Syarat & Ketentuan ────────────────────────────────────────────── -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content" style="border-radius:1rem;border:none;">
            <div class="modal-header bg-primary bg-opacity-10 border-0" style="border-radius:1rem 1rem 0 0;">
                <h5 class="modal-title fw-bold" id="termsModalLabel">
                    <i class="bi bi-file-earmark-text-fill text-primary me-2"></i>Syarat &amp; Ketentuan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body small lh-lg">
                <p class="text-muted mb-3">
                    Dengan mengirimkan laporan melalui <?= esc($appName) ?>, Anda menyatakan telah membaca
                    dan menyetujui ketentuan berikut.
                </p>

                <h6 class="fw-bold">1. Kebenaran Laporan</h6>
                <p>
                    Laporan harus dibuat sesuai kondisi nyata di lapangan. Foto yang diunggah harus merupakan
                    kondisi terkini pada titik yang dilaporkan, bukan foto rekayasa atau milik pihak lain.
                </p>

                <h6 class="fw-bold">2. Larangan</h6>
                <ul>
                    <li>Mengirim laporan palsu, bercanda, atau berulang untuk titik yang sama.</li>
                    <li>Mengunggah foto yang mengandung unsur pornografi, kekerasan, SARA, atau data pribadi orang lain.</li>
                    <li>Menggunakan sistem untuk spam, uji beban, atau tindakan yang mengganggu layanan.</li>
                </ul>

                <h6 class="fw-bold">3. Wilayah Layanan</h6>
                <p>
                    Laporan hanya diterima untuk titik yang berada di dalam batas wilayah administrasi
                    <?= $cityName ? esc($cityName) : 'yang dilayani' ?>. Laporan di luar wilayah tersebut akan ditolak otomatis.
                </p>

                <h6 class="fw-bold">4. Proses Tindak Lanjut</h6>
                <p>
                    Laporan masuk berstatus <em>menunggu verifikasi</em>. Pengelola berhak menolak, menggabungkan
                    laporan duplikat, atau menyesuaikan prioritas penanganan berdasarkan kondisi lapangan dan
                    ketersediaan petugas. Tidak ada jaminan waktu penyelesaian tertentu.
                </p>

                <h6 class="fw-bold">5. Hak atas Konten</h6>
                <p>
                    Dengan mengunggah foto dan keterangan, Anda memberi izin kepada pengelola untuk menyimpan
                    dan menampilkannya pada peta serta laporan kebersihan, termasuk untuk keperluan dokumentasi
                    dan sosialisasi, tanpa mencantumkan identitas pribadi Anda.
                </p>

                <h6 class="fw-bold">6. Penyalahgunaan</h6>
                <p>
                    Pengelola dapat membatasi atau memblokir akses pengirim yang terbukti menyalahgunakan layanan,
                    termasuk pembatasan otomatis berdasarkan alamat IP.
                </p>

                <h6 class="fw-bold">7. Perubahan Ketentuan</h6>
                <p>
                    Ketentuan ini dapat diperbarui sewaktu-waktu. Versi yang berlaku adalah yang ditampilkan
                    pada halaman ini saat laporan dikirim.
                </p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Saya Setuju</button>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = <<<JS
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    // ── Map init ─────────────────────────────────────────────────────────────
    const defLat  = parseFloat(document.getElementById('latInput').value  || '{$mapLat}');
    const defLng  = parseFloat(document.getElementById('lngInput').value  || '{$mapLng}');
    const defZoom = {$mapZoom};

    const map = L.map('map').setView([defLat, defLng], defZoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a> contributors',
        referrerPolicy: 'strict-origin-when-cross-origin',
        maxZoom: 19,
    }).addTo(map);

    let marker = null;
    let locationLocked = false;
    const gpsBtn = document.getElementById('btnGps');

    function lockLocation() {
        if (locationLocked) {
            return;
        }

        locationLocked = true;
        map.off('click', onMapClick);
        gpsBtn.disabled = true;
        gpsBtn.classList.add('disabled');
        gpsBtn.innerHTML = '<i class="bi bi-lock-fill me-1"></i> Lokasi Terkunci';
    }

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
        badge.innerHTML = `<i class="bi bi-geo-alt-fill me-1"></i>\${lat.toFixed(5)}, \${lng.toFixed(5)}`;
        checkReady();
    }

    // Click on map (first click only)
    function onMapClick(e) {
        if (locationLocked) {
            return;
        }

        setLocation(e.latlng.lat, e.latlng.lng);
        lockLocation();
    }

    map.on('click', onMapClick);

    // GPS button
    gpsBtn.addEventListener('click', function () {
        if (locationLocked) {
            return;
        }

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mendeteksi…';

        navigator.geolocation.getCurrentPosition(
            pos => {
                setLocation(pos.coords.latitude, pos.coords.longitude);
                map.setView([pos.coords.latitude, pos.coords.longitude], 16);
                lockLocation();
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
        lockLocation();
    }

    // ── Photo preview & submit gate ───────────────────────────────────────────
    function checkReady() {
        const hasLoc   = document.getElementById('latInput').value !== '';
        const hasPhoto = document.getElementById('photoInput').files.length > 0;
        const agreed   = document.getElementById('agreeTerms').checked;
        document.getElementById('submitBtn').disabled = !(hasLoc && hasPhoto && agreed);
    }

    document.getElementById('photoInput').addEventListener('change', checkReady);
    document.getElementById('agreeTerms').addEventListener('change', checkReady);

    window.previewPhoto = function (input) {
        const file = input.files[0];

        if (!file) {
            return;
        }

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (!allowedTypes.includes(file.type)) {

            Swal.fire({
                icon: 'error',
                title: 'Format Tidak Didukung',
                text: 'Hanya file JPG, PNG, dan WebP yang diperbolehkan.',
                confirmButtonColor: '#198754'
            });

            input.value = '';

            document.getElementById('photoPreview').src = '#';

            document.getElementById('photoPreviewWrap')
                .classList.add('d-none');

            checkReady();
            return;
        }

        if (file.size > 5 * 1024 * 1024) {

            Swal.fire({
                icon: 'warning',
                title: 'Ukuran Terlalu Besar',
                text: 'Ukuran foto maksimal 5 MB.',
                confirmButtonColor: '#198754'
            });

            input.value = '';

            document.getElementById('photoPreviewWrap')
                .classList.add('d-none');

            checkReady();
            return;
        }

        const reader = new FileReader();

        reader.onload = e => {
            document.getElementById('photoPreview').src = e.target.result;

            document.getElementById('photoPreviewWrap')
                .classList.remove('d-none');
        };

        reader.readAsDataURL(file);

        checkReady();
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
