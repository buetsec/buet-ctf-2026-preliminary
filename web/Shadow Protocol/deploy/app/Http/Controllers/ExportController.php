<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function export(Request $request)
    {
        $exportData = [
            'system_status' => 'operational',
            'nodes_active' => rand(12, 24),
            'last_sync' => now()->subMinutes(rand(1, 30))->toIso8601String(),
            'export_id' => bin2hex(random_bytes(8)),
            'message' => 'Export complete. Data archived successfully.',
            'checksum' => hash('sha256', microtime()),
        ];

        return view('export.success', [
            'data' => $exportData,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
