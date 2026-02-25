<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ReportModel;
use App\Models\ReportLogModel;
use App\Models\UserModel;

/**
 * Admin ReportController
 *
 * Full management of all waste reports:
 *  - Paginated + filterable list with bulk selection
 *  - Individual status change
 *  - Bulk status change / bulk delete
 *  - AJAX detail endpoint (reuses dinas JSON)
 */
class ReportController extends BaseController
{
    private ReportModel    $reportModel;
    private ReportLogModel $logModel;

    public function __construct()
    {
        $this->reportModel = new ReportModel();
        $this->logModel    = new ReportLogModel();
    }

    // ── List ─────────────────────────────────────────────────────────────────

    public function index(): string
    {
        $perPage = 20;
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $status  = $this->request->getGet('status') ?? '';
        $search  = trim($this->request->getGet('q') ?? '');

        $builder = $this->reportModel
            ->select('reports.*, users.name AS reporter_name, users.email AS reporter_email')
            ->join('users', 'users.id = reports.user_id', 'left')
            ->orderBy('reports.created_at', 'DESC');

        if ($status) {
            $builder->where('reports.status', $status);
        }

        if ($search) {
            $builder->groupStart()
                ->like('users.name', $search)
                ->orLike('users.email', $search)
                ->orLike('reports.description', $search)
                ->groupEnd();
        }

        $total   = (clone $builder)->countAllResults(false);
        $reports = $builder->findAll($perPage, ($page - 1) * $perPage);

        $pager = [
            'page'     => $page,
            'perPage'  => $perPage,
            'total'    => $total,
            'pages'    => (int) ceil($total / $perPage),
        ];

        return $this->render('layouts/admin', 'admin/reports/index', [
            'reports' => $reports,
            'pager'   => $pager,
            'stats'   => $this->reportModel->getStats(),
            'status'  => $status,
            'search'  => $search,
        ]);
    }

    // ── AJAX detail (reuses the same endpoint as dinas) ──────────────────────

    public function detail(int $id)
    {
        $report = $this->reportModel
            ->select('reports.*, users.name AS reporter_name')
            ->join('users', 'users.id = reports.user_id', 'left')
            ->find($id);

        if (! $report) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Laporan tidak ditemukan.']);
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $report]);
    }

    // ── Individual status update ──────────────────────────────────────────────

    public function updateStatus(int $id)
    {
        $report = $this->reportModel->find($id);
        if (! $report) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Laporan tidak ditemukan.']);
        }

        $newStatus = $this->request->getPost('status');
        $note      = $this->request->getPost('note') ?? null;
        $actorId   = $this->authUser['id'];

        $allowed = ['pending', 'reviewed', 'in_progress', 'cleaned', 'rejected'];
        if (! in_array($newStatus, $allowed, true)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Status tidak valid.']);
        }

        $updateData = ['status' => $newStatus];
        if ($note) {
            $updateData['admin_note'] = $note;
        }
        if ($newStatus === 'rejected') {
            $updateData['rejection_reason'] = 'manual';
        }

        $this->reportModel->update($id, $updateData);
        $this->logModel->log($id, $actorId, $report['status'], $newStatus, $note);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Status berhasil diperbarui.',
            'data'    => ['new_status' => $newStatus],
        ]);
    }

    // ── Bulk operations ──────────────────────────────────────────────────────

    public function bulk()
    {
        $ids    = $this->request->getPost('ids');        // comma-separated or array
        $action = $this->request->getPost('action');     // 'status_change' | 'delete'
        $value  = $this->request->getPost('value') ?? null; // new status for status_change
        $actorId = $this->authUser['id'];

        if (empty($ids)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak ada laporan yang dipilih.']);
        }

        $idList = is_array($ids)
            ? array_map('intval', $ids)
            : array_map('intval', explode(',', $ids));

        $idList = array_filter($idList, fn($i) => $i > 0);
        if (empty($idList)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak valid.']);
        }

        $affected = 0;

        if ($action === 'delete') {
            // Delete logs first (FK constraint)
            $this->reportModel->db->table('report_logs')
                ->whereIn('report_id', $idList)->delete();
            $affected = $this->reportModel->whereIn('id', $idList)->delete();

        } elseif ($action === 'status_change') {
            $allowed = ['pending', 'reviewed', 'in_progress', 'cleaned', 'rejected'];
            if (! in_array($value, $allowed, true)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Status tidak valid.']);
            }

            $reports = $this->reportModel->whereIn('id', $idList)->findAll();
            foreach ($reports as $r) {
                $updateData = ['status' => $value];
                if ($value === 'rejected') {
                    $updateData['rejection_reason'] = 'manual';
                }
                $this->reportModel->update($r['id'], $updateData);
                $this->logModel->log($r['id'], $actorId, $r['status'], $value, 'Bulk update oleh admin.');
                $affected++;
            }
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Aksi tidak dikenal.']);
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => "{$affected} laporan berhasil diproses.",
            'affected' => $affected,
        ]);
    }

    // ── Delete single ────────────────────────────────────────────────────────

    public function delete(int $id)
    {
        $report = $this->reportModel->find($id);
        if (! $report) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Laporan tidak ditemukan.']);
        }

        $this->reportModel->db->table('report_logs')->where('report_id', $id)->delete();
        $this->reportModel->delete($id);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Laporan dihapus.']);
    }
}
