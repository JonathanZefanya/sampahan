<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\ReportLogModel;
use App\Models\ReportModel;
use App\Models\SettingModel;
use App\Services\CaptchaService;
use App\Services\MockImageAnalysisService;

/**
 * GuestReportController
 *
 * Allows unauthenticated (guest) visitors to submit a trash report.
 * Identical pipeline to Masyarakat\ReportController::store() but without
 * a required session, and with an optional captcha gate.
 */
class GuestReportController extends BaseController
{
    private ReportModel    $reportModel;
    private SettingModel   $settingModel;
    private CaptchaService $captcha;

    public function __construct()
    {
        $this->reportModel  = new ReportModel();
        $this->settingModel = new SettingModel();
        $this->captcha      = new CaptchaService();
    }

    // ─── Show form ────────────────────────────────────────────────────────────

    public function create(): string
    {
        if (! $this->captcha->isEnabled()) {
            return redirect()->to(base_url())
                ->with('error', 'Laporan tamu tidak tersedia saat ini. Silakan login atau hubungi administrator.');
        }

        return $this->render('layouts/public', 'public/guest_report', [
            'mapLat'         => $this->setting('map_center_lat',  '-6.2884'),
            'mapLng'         => $this->setting('map_center_long', '106.7135'),
            'mapZoom'        => $this->setting('map_default_zoom', '12'),
            'captcha'        => $this->captcha,
            'captchaQuestion'=> $this->captcha->generateChallenge(), // math question for selfhosted
        ]);
    }

    // ─── Process submission ───────────────────────────────────────────────────

    public function store()
    {
        // ── 0. Captcha must be configured to allow guest submissions ─────────
        if (! $this->captcha->isEnabled()) {
            return redirect()->to(base_url())
                ->with('error', 'Laporan tamu tidak tersedia saat ini.');
        }

        // ── 1. Captcha verification ──────────────────────────────────────────
        if ($this->captcha->isEnabled()) {
            // selfhosted = plain text answer; external = provider token field
            if ($this->captcha->getProvider() === 'selfhosted') {
                $token = $this->request->getPost('captcha_answer') ?? '';
            } else {
                $token = $this->request->getPost('g-recaptcha-response')
                      ?? $this->request->getPost('cf-turnstile-response')
                      ?? $this->request->getPost('captcha_token')
                      ?? '';
            }

            if (! $this->captcha->verify($token, $this->request->getIPAddress())) {
                return redirect()->back()->withInput()
                    ->with('error', 'Verifikasi captcha gagal. Pastikan jawaban benar dan coba lagi.');
            }
        }

        // ── 2. Input validation ──────────────────────────────────────────────
        $rules = [
            'latitude'    => 'required|decimal',
            'longitude'   => 'required|decimal',
            'description' => 'permit_empty|max_length[1000]',
            'guest_name'  => 'permit_empty|max_length[150]',
            'guest_phone' => 'permit_empty|max_length[50]',
            'photo'       => 'uploaded[photo]|is_image[photo]|max_size[photo,5120]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $lat = (float) $this->request->getPost('latitude');
        $lng = (float) $this->request->getPost('longitude');

        // ── 3. Boundary check ────────────────────────────────────────────────
        $geoJson  = $this->settingModel->get('city_boundary_geojson', '');
        $cityName = $this->settingModel->get('city_name', 'wilayah administrasi');

        if (! $this->reportModel->isInsideBoundary($lat, $lng, $geoJson)) {
            return redirect()->back()->withInput()
                ->with('error', "Lokasi di luar wilayah administrasi {$cityName}.");
        }

        // ── 4. Duplicate check ───────────────────────────────────────────────
        $radius    = (float) $this->settingModel->get('duplicate_radius_meters', 10);
        $duplicate = $this->reportModel->checkDuplicate($lat, $lng, $radius);

        $isRecurrentHotspot = false;

        if ($duplicate['isDuplicate']) {
            return redirect()->back()->withInput()
                ->with('error', 'Laporan di titik ini sedang diproses. Mohon tunggu hingga selesai.');
        }

        if ($duplicate['scenario'] === 'cleaned') {
            $isRecurrentHotspot = true;
        }

        // ── 5. Photo upload ──────────────────────────────────────────────────
        $upload = $this->handleUpload('photo', 'reports');
        if (! $upload) {
            return redirect()->back()->withInput()
                ->with('error', 'Upload foto gagal. Coba lagi.');
        }

        // ── 6. Image analysis ────────────────────────────────────────────────
        $analyser = new MockImageAnalysisService();
        $analysis = $analyser->analyzeImage($upload['abs']);

        if (! $analysis->isValid) {
            @unlink($upload['abs']);
            return redirect()->back()->withInput()
                ->with('error', 'Foto tidak terdeteksi sebagai sampah. Harap unggah foto yang sesuai.');
        }

        // ── 7. Persist report ─────────────────────────────────────────────────
        $reportId = $this->reportModel->insert([
            'user_id'              => null,   // guest – no account
            'guest_name'           => $this->request->getPost('guest_name') ?: null,
            'guest_phone'          => $this->request->getPost('guest_phone') ?: null,
            'latitude'             => $lat,
            'longitude'            => $lng,
            'photo_path'           => $upload['path'],
            'description'          => $this->request->getPost('description'),
            'status'               => ReportModel::STATUS_PENDING,
            'is_recurrent_hotspot' => (int) $isRecurrentHotspot,
        ]);

        // ── 8. Emit initial log ───────────────────────────────────────────────
        (new ReportLogModel())->log(
            (int) $reportId,
            null,  // no actor (guest, system)
            null,
            ReportModel::STATUS_PENDING,
            'Laporan oleh tamu (guest).'
        );

        $msg = $isRecurrentHotspot
            ? 'Laporan berhasil dikirim. Catatan: lokasi ini sebelumnya pernah dilaporkan (Hotspot Berulang).'
            : 'Terima kasih! Laporan sampah Anda berhasil dikirim dan akan segera ditangani.';

        return redirect()->to('/laporkan-sampah')->with('success', $msg);
    }
}
