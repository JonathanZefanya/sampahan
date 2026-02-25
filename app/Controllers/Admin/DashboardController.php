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
            'userStats'   => $userModel->getStats(),
            'reportStats' => $reportModel->getStats(),
            'recentReports' => (new ReportModel())
                ->select('reports.*, users.name AS reporter_name')
                ->join('users', 'users.id = reports.user_id', 'left')
                ->orderBy('reports.created_at', 'DESC')
                ->limit(10)
                ->findAll(),
        ];

        return $this->render('layouts/admin', 'admin/dashboard', $data);
    }
}
