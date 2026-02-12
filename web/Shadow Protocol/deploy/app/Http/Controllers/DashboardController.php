<?php

namespace App\Http\Controllers;

use App\Services\ImplantRegistry;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private ImplantRegistry $registry
    ) {}

    public function index()
    {
        $implants = $this->registry->getActiveImplants();
        $stats = $this->registry->getSystemStats();
        $logs = $this->registry->getOperationsLog();

        return view('dashboard.index', [
            'implants' => $implants,
            'stats' => $stats,
            'logs' => $logs,
        ]);
    }
}
