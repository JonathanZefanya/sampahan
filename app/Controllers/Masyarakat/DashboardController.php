<?php

namespace App\Controllers\Masyarakat;

use App\Controllers\BaseController;
use App\Models\ReportModel;
use App\Models\UserModel;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $userId = (int) $this->authUser['id'];

        return $this->render('layouts/masyarakat', 'masyarakat/dashboard', [
            'stats' => (new ReportModel())->getStats($userId),
            'user'  => (new UserModel())->find($userId),
        ]);
    }
}
