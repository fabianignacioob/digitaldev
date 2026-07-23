<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\SystemStatusService;

class AdminSystemStatusController extends AdminController
{
    public function index(): void
    {
        $status = (new SystemStatusService())->snapshot();
        $this->set(compact('status'));
    }
}
