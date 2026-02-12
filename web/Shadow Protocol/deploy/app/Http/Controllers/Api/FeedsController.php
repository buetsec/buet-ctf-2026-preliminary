<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ImplantRegistry;
use Illuminate\Http\Request;

class FeedsController extends Controller
{
    public function __construct(
        private ImplantRegistry $registry
    ) {}

    public function health()
    {
        return response()->json([
            'ok' => true,
            'ts' => now()->toIso8601String(),
            'build' => [
                'version' => '3.2.1-stable',
                'hash' => 'a7f3c2e1',
            ],
        ]);
    }

    public function stats()
    {
        return response()->json([
            'ok' => true,
            'ts' => now()->toIso8601String(),
            'stats' => $this->registry->getSystemStats(),
        ]);
    }

    public function implants()
    {
        return response()->json([
            'ok' => true,
            'ts' => now()->toIso8601String(),
            'implants' => $this->registry->getActiveImplants(),
        ]);
    }

    public function opsLog(Request $request)
    {
        $limit = (int) $request->query('limit', 10);
        if ($limit < 1) $limit = 1;
        if ($limit > 25) $limit = 25;

        $rows = array_slice($this->registry->getOperationsLog(), 0, $limit);

        return response()->json([
            'ok' => true,
            'ts' => now()->toIso8601String(),
            'logs' => $rows,
        ]);
    }

    public function implantTelemetry(string $id)
    {
        // Strict-ish UUID-like check; never throw.
        if (!preg_match('/^[a-f0-9-]{10,}$/i', $id)) {
            return response()->json([
                'ok' => false,
                'error' => 'Not found',
            ], 404);
        }

        $implant = $this->registry->findImplant($id);
        if (!$implant) {
            return response()->json([
                'ok' => false,
                'error' => 'Not found',
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'ts' => now()->toIso8601String(),
            'telemetry' => $this->registry->getTelemetryHistory($id),
        ]);
    }
}

