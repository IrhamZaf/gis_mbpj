<?php

use Illuminate\Support\Facades\Route;

// ── Auth ─────────────────────────────────────────────
Route::get('/login', App\Livewire\Auth\Login::class)->name('login');

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// ── Root redirect based on role ─────────────────────
Route::get('/', function () {
    $user = auth()->user();
    if (!$user) return redirect()->route('login');

    return match ($user->role) {
        'superadmin' => redirect()->route('superadmin.dashboard'),
        'surveyor'   => redirect()->route('surveyor.dashboard'),
        'engineer'   => redirect()->route('engineer.dashboard'),
        default      => redirect()->route('login'),
    };
})->middleware('auth');

// ═══════════════════════════════════════════════════════
// SUPERADMIN
// ═══════════════════════════════════════════════════════
Route::middleware(['auth', 'role:superadmin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/',           App\Livewire\Superadmin\Dashboard::class)->name('dashboard');
        Route::get('/users',      App\Livewire\Superadmin\UserManagement::class)->name('users');
        Route::get('/categories', App\Livewire\Superadmin\CategoryManagement::class)->name('categories');
        Route::get('/reports',    App\Livewire\Superadmin\ReportMonitoring::class)->name('reports');
    });

// ═══════════════════════════════════════════════════════
// SURVEYOR
// ═══════════════════════════════════════════════════════
Route::middleware(['auth', 'role:surveyor'])
    ->prefix('surveyor')
    ->name('surveyor.')
    ->group(function () {
        Route::get('/',                  App\Livewire\Surveyor\Dashboard::class)->name('dashboard');
        Route::get('/reports',           App\Livewire\Surveyor\ReportList::class)->name('reports');
        Route::get('/reports/create',    App\Livewire\Surveyor\ReportCreate::class)->name('reports.create');
        Route::get('/reports/{report}/edit', App\Livewire\Surveyor\ReportEdit::class)->name('reports.edit');
    });

// ═══════════════════════════════════════════════════════
// ENGINEER
// ═══════════════════════════════════════════════════════
Route::middleware(['auth', 'role:engineer'])
    ->prefix('engineer')
    ->name('engineer.')
    ->group(function () {
        Route::get('/',                  App\Livewire\Engineer\Dashboard::class)->name('dashboard');
        Route::get('/reports',           App\Livewire\Engineer\ReportList::class)->name('reports');
        Route::get('/reports/{report}',  App\Livewire\Engineer\ReportView::class)->name('reports.view');
    });
