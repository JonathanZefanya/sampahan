<?php

namespace App\Controllers\Dinas;

use App\Controllers\BaseController;
use App\Models\ReportModel;
use App\Models\UserModel;
use App\Services\DynamicMailer;

class MapController extends BaseController
{
    private ReportModel $reportModel;

    public function __construct()
    {
        $this->reportModel = new ReportModel();
    }

    // ─── Map view ─────────────────────────────────────────────────────────────

    public function index(): string
    {
        return $this->render('layouts/dinas', 'dinas/map', [
            'mapLat'  => $this->setting('map_center_lat', '-6.2884'),
            'mapLng'  => $this->setting('map_center_long', '106.7135'),
            'mapZoom' => $this->setting('map_default_zoom', '12'),
            'geoJson' => $this->setting('city_boundary_geojson', ''),
        ]);
    }

    // ─── GeoJSON endpoint consumed by Leaflet ────────────────────────────────

    public function geojson()
    {
        $status     = $this->request->getGet('status');
        $collection = $this->reportModel->toGeoJson($status ?: null);

        return $this->response
            ->setContentType('application/json')
            ->setJSON($collection);
    }

    // ─── Report detail popup data ─────────────────────────────────────────────

    public function detail(int $id)
    {
        $report = $this->reportModel
            ->select('reports.*, users.name AS reporter_name, users.email AS reporter_email')
            ->join('users', 'users.id = reports.user_id', 'left')
            ->find($id);

        if (! $report) {
            return $this->jsonError('Laporan tidak ditemukan.', 404);
        }

        return $this->jsonSuccess($report);
    }

    // ─── Status advance (Pending → Reviewed → In Progress → Cleaned) ─────────

    public function advance(int $id)
    {
        $note   = $this->request->getPost('note') ?? null;
        $actorId = (int) $this->authUser['id'];

        $report = $this->reportModel->find($id);
        if (! $report) {
            return $this->jsonError('Laporan tidak ditemukan.', 404);
        }

        $success = $this->reportModel->advanceStatus($id, $actorId, $note);
        if (! $success) {
            return $this->jsonError('Transisi status tidak diizinkan untuk laporan ini.');
        }

        // Reload to get new status
        $updated = $this->reportModel->find($id);

        // If just cleaned → send thank-you email to reporter
        if ($updated['status'] === ReportModel::STATUS_CLEANED) {
            $reporter = (new UserModel())->find($report['user_id']);
            if ($reporter) {
                try {
                    (new DynamicMailer())->sendCleanedNotification(
                        $reporter['email'],
                        $reporter['name'],
                        $id
                    );
                } catch (\Throwable $e) {
                    log_message('error', '[MapController::advance] Mailer: ' . $e->getMessage());
                }
            }
        }

        return $this->jsonSuccess([
            'new_status' => $updated['status'],
            'report_id'  => $id,
        ], 'Status diperbarui.');
    }

    // ─── Manual reject ────────────────────────────────────────────────────────

    public function reject(int $id)
    {
        $note    = $this->request->getPost('note') ?? '';
        $actorId = (int) $this->authUser['id'];

        if (! $this->reportModel->find($id)) {
            return $this->jsonError('Laporan tidak ditemukan.', 404);
        }

        $this->reportModel->reject($id, 'manual', $note, $actorId);

        return $this->jsonSuccess(['report_id' => $id], 'Laporan ditolak.');
    }
}
