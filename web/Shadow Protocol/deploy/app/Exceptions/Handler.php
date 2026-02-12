<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        //
    }

    public function render($request, Throwable $e): Response
    {
        $path = ltrim($request->path(), '/');

        if (str_starts_with($path, '_ignition/')) {
            return parent::render($request, $e);
        }

        if ($path === 'api/node/status' && config('app.debug')) {
            return parent::render($request, $e);
        }

        // API: never leak internals (all other API routes)
        if ($request->is('api/*')) {
            $status = 500;
            $msg = 'Internal error';

            if (method_exists($e, 'getStatusCode')) {
                $status = (int) $e->getStatusCode();
                $msg = $status === 404 ? 'Not found' : 'Request failed';
            }

            return response()->json([
                'ok' => false,
                'error' => $msg,
            ], $status);
        }

        // Web: generic error pages
        if (method_exists($e, 'getStatusCode') && (int) $e->getStatusCode() === 404) {
            return response()->view('errors.404', [], 404);
        }

        if (method_exists($e, 'getStatusCode') && (int) $e->getStatusCode() === 403) {
            return response()->view('errors.403', [], 403);
        }

        return response()->view('errors.500', [], 500);
    }
}
