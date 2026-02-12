<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NodeValidator;
use App\Services\SystemDiagnostics;
use Illuminate\Http\Request;

class NodeStatusController extends Controller
{
    public function __construct(
        private NodeValidator $validator,
        private SystemDiagnostics $diagnostics
    ) {}

    /**
     * Get node status information.
     * 
     * The node_id parameter can be provided via:
     * - Query parameter: ?node_id=xxx
     * - Custom header: X-Node-Ref
     */
    public function status(Request $request)
    {
        // Priority: query param > custom header
        $nodeId = $request->query('node_id') 
            ?? $request->header('X-Node-Ref');

        if (!$nodeId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Node reference required'
            ], 400);
        }

        $nodeData = $this->validator->resolveNodeContext($nodeId);

        return response()->json([
            'status' => 'online',
            'node' => $nodeData,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function telemetry(Request $request)
    {
        $nodeId = $request->query('node_id')
            ?? $request->header('X-Node-Ref');

        if (!$nodeId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Node reference required'
            ], 400);
        }

        $nodeData = $this->validator->resolveNodeContext($nodeId);
        $telemetry = $this->diagnostics->getNodeTelemetry($nodeData['id']);

        return response()->json([
            'telemetry' => $telemetry,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
