<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= base_url('masyarakat/dashboard') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h2 class="fw-bold mb-0"><i class="bi bi-camera text-success"></i> Laporkan Sampah</h2>
        <small class="text-muted">Foto + lokasi GPS akan terkirim otomatis.</small>
    </div>
</div>

<!-- GPS mini-map preview -->
<div id="pickMap" style="height:250px; border-radius:.75rem; margin-bottom:1rem; z-index:0;"></div>
<div class="alert alert-info py-2 d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-geo-alt-fill text-primary fs-5"></i>
    <div>
        GPS: <strong id="coordDisplay">Mendeteksi lokasi...</strong>
        <button type="button" id="btnRefreshGps" class="btn btn-sm btn-outline-primary ms-2">
            <i class="bi bi-arrow-clockwise"></i> Perbarui Lokasi
        </button>
    </div>
</div>

<div class="card" style="max-width:640px;">
    <div class="card-body">
        <form action="<?= base_url('masyarakat/report') ?>" method="POST" enctype="multipart/form-data" id="reportForm">
            <?= csrf_field() ?>
            <input type="hidden" name="latitude"  id="lat" required>
            <input type="hidden" name="longitude" id="lng" required>

            <div class="mb-4">
                <label class="form-label fw-semibold fs-5">Foto Sampah <span class="text-danger">*</span></label>
                <input type="file" name="photo" id="photoInput" class="form-control form-control-lg"
                       accept="image/*" capture="environment" required>
                <div class="mt-2" id="photoPreviewWrap" style="display:none;">
                    <img id="photoPreview" src="" class="img-thumbnail" style="max-height:200px;">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold fs-5">Deskripsi</label>
                <textarea name="description" class="form-control form-control-lg" rows="3"
                          placeholder="Contoh: Tumpukan sampah di depan gang 5..."></textarea>
            </div>

            <button type="submit" class="btn btn-success btn-xlg w-100" id="btnSubmit" disabled>
                <i class="bi bi-send-fill me-2"></i>Kirim Laporan
            </button>
            <p id="gpsWarning" class="text-danger small mt-2 text-center">
                Menunggu GPS... Izinkan akses lokasi di browser Anda.
            </p>
        </form>
    </div>
</div>

<!-- FAB -->
<a href="<?= base_url('masyarakat/report') ?>" class="fab-report d-lg-none">
    <i class="bi bi-plus-lg"></i>
</a>

<?php ob_start(); ?>
<script>
const pickMap    = L.map('pickMap').setView([<?= $mapLat ?>, <?= $mapLng ?>], <?= $mapZoom ?>);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(pickMap);

let userMarker = null;

function setLocation(lat, lng) {
    document.getElementById('lat').value = lat;
    document.getElementById('lng').value = lng;
    document.getElementById('coordDisplay').textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    document.getElementById('gpsWarning').style.display = 'none';
    document.getElementById('btnSubmit').disabled = false;

    if (userMarker) pickMap.removeLayer(userMarker);
    userMarker = L.marker([lat, lng]).addTo(pickMap);
    pickMap.setView([lat, lng], 16);
}

function getGPS() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            pos => setLocation(pos.coords.latitude, pos.coords.longitude),
            err => {
                document.getElementById('coordDisplay').textContent = 'Gagal mendapatkan GPS.';
                console.warn('GPS error:', err.message);
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    } else {
        document.getElementById('coordDisplay').textContent = 'Browser tidak mendukung GPS.';
    }
}

getGPS();
document.getElementById('btnRefreshGps').addEventListener('click', getGPS);

// Allow clicking the mini-map to override GPS
pickMap.on('click', function (e) {
    setLocation(e.latlng.lat, e.latlng.lng);
});

// Photo preview
document.getElementById('photoInput').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('photoPreview').src = e.target.result;
        document.getElementById('photoPreviewWrap').style.display = 'block';
    };
    reader.readAsDataURL(file);
});
</script>
<?php $extraScripts = ob_get_clean(); ?>
