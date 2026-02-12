<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateIgnitionSignature
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('_ignition/execute-solution')) {
            if (!$request->hasValidSignature()) {
                return response()->json([
                    'error' => 'Invalid signature',
                    'message' => 'Access denied'
                ], 403);
            }
        }

        return $next($request);
    }
}
