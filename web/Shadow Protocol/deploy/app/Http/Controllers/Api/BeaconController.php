<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BeaconController extends Controller
{
    public function checkin(Request $request)
    {
        $implantId = $request->input('id');
        if (!$implantId) {
            return response()->json(['error' => 'Missing implant ID', 'code' => 'INVALID_REQUEST'], 400);
        }
        
        return response()->json([
            'ok' => true,
            'ack' => bin2hex(random_bytes(8)),
            'commands' => [],
            'sleep_interval' => rand(30, 300),
            'jitter' => rand(5, 20),
        ]);
    }

    public function result(Request $request)
    {
        $taskId = $request->input('task_id');
        if (!$taskId) {
            return response()->json(['error' => 'Missing task ID', 'code' => 'INVALID_REQUEST'], 400);
        }
        
        return response()->json(['ok' => true, 'received' => true]);
    }

    public function heartbeat(Request $request)
    {
        return response()->json([
            'ok' => true,
            'timestamp' => now()->toIso8601String(),
            'server_time' => time(),
        ]);
    }

    public function register(Request $request)
    {
        return response()->json([
            'error' => 'Registration closed',
            'code' => 'REGISTRATION_DISABLED'
        ], 403);
    }

    public function deregister(Request $request)
    {
        return response()->json([
            'error' => 'Deregistration requires authentication',
            'code' => 'AUTH_REQUIRED'
        ], 401);
    }

    public function config(Request $request)
    {
        return response()->json([
            'ok' => true,
            'config' => [
                'beacon_interval' => 60,
                'jitter_percent' => 15,
                'retry_count' => 3,
                'retry_delay' => 10,
                'killswitch' => false,
            ]
        ]);
    }
}
