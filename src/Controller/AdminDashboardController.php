<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\AdminStatisticsService;

class AdminDashboardController extends AdminController
{
    public function index(): void
    {
        $stats = (new AdminStatisticsService())->dashboard();
        $this->set(compact('stats'));
    }
}
