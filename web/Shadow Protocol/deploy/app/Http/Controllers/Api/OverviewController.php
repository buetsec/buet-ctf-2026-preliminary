<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ImplantRegistry;
use Illuminate\Http\Request;

class OverviewController extends Controller
{
    public function __construct(
        private ImplantRegistry $registry
    ) {}

    /**
     * Safe dashboard feed endpoint.
     */
    public function overview(Request $request)
    {
        $limit = (int) $request->query('log_limit', 10);
        if ($limit < 1) {
            $limit = 1;
        }
        if ($limit > 25) {
            $limit = 25;
        }

        $implants = $this->registry->getActiveImplants();
        $stats = $this->registry->getSystemStats();
        $logs = array_slice($this->registry->getOperationsLog(), 0, $limit);

        return response()->json([
            'ok' => true,
            'ts' => now()->toIso8601String(),
            'stats' => $stats,
            'implants' => $implants,
            'logs' => $logs,
        ]);
    }
}

