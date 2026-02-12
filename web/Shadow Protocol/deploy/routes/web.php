<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiagnosticsController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImplantController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/implant/{id}', [ImplantController::class, 'show'])
    ->name('implant.show')
    ->where('id', '[a-f0-9-]+');

Route::get('/diagnostics', [DiagnosticsController::class, 'index'])->name('diagnostics');

Route::get('/system/export', [ExportController::class, 'export'])
    ->middleware('signed')
    ->name('system.export');

Route::get('/network', function () {
    return view('network.index');
})->name('network');

Route::get('/reports', function () {
    return view('reports.index');
})->name('reports');

Route::get('/modules', function () {
    return view('modules.index');
})->name('modules');

Route::get('/settings', function () {
    return view('settings.index');
})->name('settings');

Route::get('/users', function () {
    return view('errors.403');
})->name('users');

Route::get('/admin', function () {
    return view('errors.403');
})->name('admin');

Route::get('/admin/{any}', function () {
    return view('errors.403');
})->where('any', '.*');

Route::get('/config', function () {
    return view('errors.403');
});

Route::get('/backup', function () {
    return view('errors.403');
});

Route::get('/restore', function () {
    return view('errors.403');
});

Route::get('/logs', function () {
    return view('errors.403');
});

Route::get('/audit', function () {
    return view('errors.403');
});

Route::get('/keys', function () {
    return view('errors.403');
});

Route::get('/certificates', function () {
    return view('errors.403');
});

Route::get('/tasks', function () {
    return view('tasks.index');
})->name('tasks');

Route::get('/scheduler', function () {
    return view('errors.403');
});

Route::get('/queue', function () {
    return view('errors.403');
});

Route::get('/maintenance', function () {
    return view('errors.403');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function () {
    return redirect('/login')->with('error', 'Invalid credentials');
});

Route::get('/logout', function () {
    return redirect('/login');
})->name('logout');

Route::get('/profile', function () {
    return view('errors.403');
})->name('profile');

Route::get('/help', function () {
    return view('help.index');
})->name('help');

Route::get('/about', function () {
    return view('about.index');
})->name('about');

Route::get('/status', function () {
    return response()->json([
        'status' => 'operational',
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::get('/health', function () {
    return response('OK', 200);
});
