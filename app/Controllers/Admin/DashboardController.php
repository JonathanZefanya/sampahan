<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ReportModel;
use App\Models\UserModel;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $userModel   = new UserModel();
        $reportModel = new ReportModel();

        $data = [
            'userStats'    => $userModel->getStats(),
            'reportStats'  => $reportModel->getStats(),
            'availableYears' => $reportModel->getAvailableYears(),
            'recentReports' => (new ReportModel())
                ->select('reports.*, users.name AS reporter_name')
                ->join('users', 'users.id = reports.user_id', 'left')
                ->orderBy('reports.created_at', 'DESC')
                ->limit(10)
                ->findAll(),
        ];

        return $this->render('layouts/admin', 'admin/dashboard', $data);
    }

    /**
     * AJAX: return stats JSON filtered by year and/or month.
     * GET /admin/api/dashboard-stats?year=2025&month=03
     */
    public function statsApi()
    {
        $reportModel = new ReportModel();
        $userModel   = new UserModel();

        $year  = $this->request->getGet('year')  ?? '';
        $month = $this->request->getGet('month') ?? '';

        $reportStats = $reportModel->getStats(null, $year ?: null, $month ?: null);
        $userStats   = $userModel->getStats();

        // Recent reports filtered by period
        $builder = (new ReportModel())
            ->select('reports.*, users.name AS reporter_name')
            ->join('users', 'users.id = reports.user_id', 'left')
            ->orderBy('reports.created_at', 'DESC')
            ->limit(10);

        if ($year) {
            $builder->where('YEAR(reports.created_at)', $year);
            if ($month) {
                $builder->where('MONTH(reports.created_at)', ltrim($month, '0') ?: '1');
            }
        }

        $recentReports = $builder->findAll();

        // Format recent rows for JSON
        $rows = array_map(fn($r) => [
            'id'            => $r['id'],
            'reporter_name' => $r['reporter_name'] ?? '–',
            'status'        => $r['status'],
            'latitude'      => $r['latitude'],
            'longitude'     => $r['longitude'],
            'created_at'    => date('d M Y H:i', strtotime($r['created_at'])),
        ], $recentReports);

        return $this->response->setJSON([
            'status'       => 'success',
            'reportStats'  => $reportStats,
            'userStats'    => $userStats,
            'recentReports'=> $rows,
        ]);
    }
}
