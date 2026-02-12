<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExfilController extends Controller
{
    public function upload(Request $request)
    {
        return response()->json([
            'error' => 'Upload endpoint requires valid session token',
            'code' => 'TOKEN_REQUIRED'
        ], 401);
    }

    public function download(Request $request)
    {
        return response()->json([
            'error' => 'Download endpoint requires valid session token',
            'code' => 'TOKEN_REQUIRED'
        ], 401);
    }

    public function status(Request $request)
    {
        return response()->json([
            'ok' => true,
            'stats' => [
                'total_uploads' => rand(100, 10000),
                'total_size_mb' => rand(500, 50000),
                'pending_transfers' => rand(0, 50),
                'failed_transfers' => rand(0, 10),
            ]
        ]);
    }

    public function queue(Request $request)
    {
        $items = [];
        for ($i = 0; $i < rand(3, 10); $i++) {
            $items[] = [
                'id' => bin2hex(random_bytes(4)),
                'filename' => 'file_' . rand(1000, 9999) . '.' . ['txt', 'doc', 'pdf', 'zip'][array_rand(['txt', 'doc', 'pdf', 'zip'])],
                'size' => rand(1000, 10000000),
                'status' => ['queued', 'transferring', 'complete'][array_rand(['queued', 'transferring', 'complete'])],
                'progress' => rand(0, 100),
            ];
        }
        return response()->json(['ok' => true, 'queue' => $items]);
    }

    public function cancel(Request $request)
    {
        $transferId = $request->input('id');
        if (!$transferId) {
            return response()->json(['error' => 'Missing transfer ID', 'code' => 'INVALID_REQUEST'], 400);
        }
        return response()->json(['ok' => true, 'cancelled' => true]);
    }

    public function retry(Request $request)
    {
        return response()->json([
            'error' => 'Retry requires authentication',
            'code' => 'AUTH_REQUIRED'
        ], 401);
    }
}
