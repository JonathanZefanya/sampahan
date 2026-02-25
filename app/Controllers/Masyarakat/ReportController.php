<?php

namespace App\Controllers\Masyarakat;

use App\Controllers\BaseController;
use App\Models\ReportLogModel;
use App\Models\ReportModel;
use App\Models\SettingModel;
use App\Services\MockImageAnalysisService;

/**
 * ReportController – Masyarakat
 *
 * Handles the full report submission pipeline:
 *   1. Boundary check   (Point-in-Polygon vs city GeoJSON)
 *   2. Duplicate check  (10-metre radius)
 *   3. Image analysis   (mock → pluggable Google Vision)
 *   4. Recurrent Hotspot flagging
 */
class ReportController extends BaseController
{
    private ReportModel   $reportModel;
    private SettingModel  $settingModel;

    public function __construct()
    {
        $this->reportModel  = new ReportModel();
        $this->settingModel = new SettingModel();
    }

    // ─── Upload form ─────────────────────────────────────────────────────────

    public function create(): string
    {
        return $this->render('layouts/masyarakat', 'masyarakat/report_form', [
            'mapLat'  => $this->setting('map_center_lat',  '-6.2884'),
            'mapLng'  => $this->setting('map_center_long', '106.7135'),
            'mapZoom' => $this->setting('map_default_zoom','12'),
        ]);
    }

    // ─── Submit report ────────────────────────────────────────────────────────

    public function store()
    {
        $rules = [
            'latitude'    => 'required|decimal',
            'longitude'   => 'required|decimal',
            'description' => 'permit_empty|max_length[1000]',
            'photo'       => 'uploaded[photo]|is_image[photo]|max_size[photo,5120]', // 5 MB
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $lat  = (float) $this->request->getPost('latitude');
        $lng  = (float) $this->request->getPost('longitude');

        // ── 1. Boundary check ────────────────────────────────────────────────
        $geoJson  = $this->settingModel->get('city_boundary_geojson', '');
        $cityName = $this->settingModel->get('city_name', 'wilayah administrasi');

        if (! $this->reportModel->isInsideBoundary($lat, $lng, $geoJson)) {
            return redirect()->back()->withInput()
                ->with('error', "Lokasi di luar wilayah administrasi {$cityName}.");
        }

        // ── 2. Duplicate check ───────────────────────────────────────────────
        $radius    = (float) $this->settingModel->get('duplicate_radius_meters', 10);
        $duplicate = $this->reportModel->checkDuplicate($lat, $lng, $radius);

        $isRecurrentHotspot = false;

        if ($duplicate['isDuplicate']) {
            return redirect()->back()->withInput()
                ->with('error', 'Laporan di titik ini sedang diproses. Mohon tunggu hingga selesai.');
        }

        if ($duplicate['scenario'] === 'cleaned') {
            $isRecurrentHotspot = true; // Scenario 2: previously cleaned → allow + flag
        }

        // ── 3. Photo upload ──────────────────────────────────────────────────
        $upload = $this->handleUpload('photo', 'reports');
        if (! $upload) {
            return redirect()->back()->withInput()->with('error', 'Upload foto gagal. Coba lagi.');
        }

        // ── 4. Image analysis (mock – pluggable Google Vision) ───────────────
        $analyser = new MockImageAnalysisService();
        $analysis = $analyser->analyzeImage($upload['abs']);

        if (! $analysis->isValid) {
            // Remove uploaded file on rejection
            @unlink($upload['abs']);
            return redirect()->back()->withInput()
                ->with('error', 'Foto tidak terdeteksi sebagai sampah. Harap unggah foto yang sesuai.');
        }

        // ── 5. Persist report ────────────────────────────────────────────────
        $reportId = $this->reportModel->insert([
            'user_id'              => $this->authUser['id'],
            'latitude'             => $lat,
            'longitude'            => $lng,
            'photo_path'           => $upload['path'],
            'description'          => $this->request->getPost('description'),
            'status'               => ReportModel::STATUS_PENDING,
            'is_recurrent_hotspot' => (int) $isRecurrentHotspot,
        ]);

        // Emit initial log entry
        (new ReportLogModel())->log(
            (int) $reportId,
            (int) $this->authUser['id'],
            null,
            ReportModel::STATUS_PENDING,
            'Laporan baru dikirimkan.'
        );

        $msg = $isRecurrentHotspot
            ? 'Laporan berhasil dikirim. Catatan: lokasi ini sebelumnya pernah dilaporkan (Hotspot Berulang).'
            : 'Laporan berhasil dikirim! Tim kami akan segera menangani.';

        return redirect()->to('/masyarakat/history')->with('success', $msg);
    }

    // ─── History list ─────────────────────────────────────────────────────────

    public function history(): string
    {
        $userId  = (int) $this->authUser['id'];
        $reports = $this->reportModel
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return $this->render('layouts/masyarakat', 'masyarakat/history', [
            'reports' => $reports,
        ]);
    }

    // ─── Report detail ────────────────────────────────────────────────────────

    public function detail(int $id): string
    {
        $userId = (int) $this->authUser['id'];
        $report = $this->reportModel->where('user_id', $userId)->find($id);

        if (! $report) {
            return redirect()->to('/masyarakat/history')
                ->with('error', 'Laporan tidak ditemukan.')
                ->send();
        }

        $logs = (new ReportLogModel())->getLogsForReport($id);

        return $this->render('layouts/masyarakat', 'masyarakat/detail', [
            'report' => $report,
            'logs'   => $logs,
        ]);
    }
}
