<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CryptoController extends Controller
{
    public function status(Request $request)
    {
        return response()->json([
            'ok' => true,
            'crypto' => [
                'algorithm' => 'AES-256-GCM',
                'key_size' => 256,
                'iv_size' => 96,
                'tag_size' => 128,
                'kdf' => 'HKDF-SHA256',
                'key_rotation_hours' => 72,
                'last_rotation' => now()->subHours(rand(1, 48))->toIso8601String(),
                'next_rotation' => now()->addHours(rand(12, 72))->toIso8601String(),
            ]
        ]);
    }

    public function keys(Request $request)
    {
        return response()->json([
            'error' => 'Key management requires master authentication',
            'code' => 'AUTH_REQUIRED'
        ], 401);
    }

    public function rotate(Request $request)
    {
        return response()->json([
            'error' => 'Key rotation requires master authentication',
            'code' => 'AUTH_REQUIRED'
        ], 401);
    }

    public function certificates(Request $request)
    {
        $certs = [
            ['name' => 'c2-primary', 'type' => 'server', 'expires' => now()->addDays(rand(30, 365))->toDateString(), 'status' => 'valid'],
            ['name' => 'proxy-eu', 'type' => 'server', 'expires' => now()->addDays(rand(30, 365))->toDateString(), 'status' => 'valid'],
            ['name' => 'proxy-na', 'type' => 'server', 'expires' => now()->addDays(rand(30, 365))->toDateString(), 'status' => 'valid'],
            ['name' => 'client-auth', 'type' => 'client', 'expires' => now()->addDays(rand(30, 180))->toDateString(), 'status' => 'valid'],
            ['name' => 'signing-key', 'type' => 'signing', 'expires' => now()->addDays(rand(60, 365))->toDateString(), 'status' => 'valid'],
        ];
        return response()->json(['ok' => true, 'certificates' => $certs]);
    }

    public function verify(Request $request)
    {
        $data = $request->input('data');
        $sig = $request->input('signature');
        
        if (!$data || !$sig) {
            return response()->json(['error' => 'Missing data or signature', 'code' => 'INVALID_REQUEST'], 400);
        }
        
        return response()->json([
            'ok' => true,
            'valid' => (bool)rand(0, 1),
            'algorithm' => 'HMAC-SHA256',
        ]);
    }
}
