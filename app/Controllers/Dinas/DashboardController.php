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
            'stats' => $reportModel->getStats(),
            'recentReports' => $reportModel
                ->select('reports.*, users.name AS reporter_name')
                ->join('users', 'users.id = reports.user_id', 'left')
                ->whereIn('status', [ReportModel::STATUS_PENDING, ReportModel::STATUS_REVIEWED, ReportModel::STATUS_IN_PROGRESS])
                ->orderBy('created_at', 'DESC')
                ->limit(8)
                ->findAll(),
        ]);
    }
}
