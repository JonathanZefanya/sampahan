<?php

namespace App\Controllers\Dinas;

use App\Controllers\BaseController;
use App\Models\ReportModel;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $reportModel = new ReportModel();

        return $this->render('layouts/dinas', 'dinas/dashboard', [
            'stats'          => $reportModel->getStats(),
            'availableYears' => $reportModel->getAvailableYears(),
            'recentReports'  => $reportModel
                ->select('reports.*, users.name AS reporter_name')
                ->join('users', 'users.id = reports.user_id', 'left')
                ->whereIn('status', [ReportModel::STATUS_PENDING, ReportModel::STATUS_REVIEWED, ReportModel::STATUS_IN_PROGRESS])
                ->orderBy('created_at', 'DESC')
                ->limit(8)
                ->findAll(),
        ]);
    }

    /**
     * AJAX: return stats JSON filtered by year and/or month.
     * GET /dinas/api/dashboard-stats?year=2025&month=03
     */
    public function statsApi()
    {
        $reportModel = new ReportModel();

        $year  = $this->request->getGet('year')  ?? '';
        $month = $this->request->getGet('month') ?? '';

        $stats = $reportModel->getStats(null, $year ?: null, $month ?: null);

        // Recent reports for the period
        $builder = (new ReportModel())
            ->select('reports.*, users.name AS reporter_name')
            ->join('users', 'users.id = reports.user_id', 'left')
            ->whereIn('reports.status', [
                ReportModel::STATUS_PENDING,
                ReportModel::STATUS_REVIEWED,
                ReportModel::STATUS_IN_PROGRESS,
            ])
            ->orderBy('reports.created_at', 'DESC')
            ->limit(8);

        if ($year) {
            $builder->where('YEAR(reports.created_at)', $year);
            if ($month) {
                $builder->where('MONTH(reports.created_at)', ltrim($month, '0') ?: '1');
            }
        }

        $recentReports = array_map(fn($r) => [
            'id'            => $r['id'],
            'reporter_name' => $r['reporter_name'] ?? '–',
            'status'        => $r['status'],
            'created_at'    => date('d M Y H:i', strtotime($r['created_at'])),
        ], $builder->findAll());

        return $this->response->setJSON([
            'status'        => 'success',
            'stats'         => $stats,
            'recentReports' => $recentReports,
        ]);
    }
}
