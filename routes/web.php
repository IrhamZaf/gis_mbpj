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
        'ta'         => redirect()->route('ta.dashboard'),
        'director'   => redirect()->route('director.dashboard'),
        default      => redirect()->route('login'),
    };
})->middleware('auth');

// ── Shared Authenticated Routes ──────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/attachments/{attachment}/download', [App\Http\Controllers\AttachmentController::class, 'download'])->name('attachment.download');
    Route::get('/attachments/{attachment}/view', [App\Http\Controllers\AttachmentController::class, 'view'])->name('attachment.view');
    Route::get('/reports/{report}/site-visit-pdf', [App\Http\Controllers\SiteVisitPdfController::class, 'download'])->name('reports.site-visit-pdf');
});

// ═══════════════════════════════════════════════════════
// SUPERADMIN
// ═══════════════════════════════════════════════════════
Route::middleware(['auth', 'role:superadmin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/',           App\Livewire\Superadmin\Dashboard::class)->name('dashboard');
        Route::get('/units',      App\Livewire\Superadmin\UnitManagement::class)->name('units');
        Route::get('/users',      App\Livewire\Superadmin\UserManagement::class)->name('users');
        Route::get('/categories', App\Livewire\Superadmin\CategoryManagement::class)->name('categories');
        Route::get('/reports',    App\Livewire\Superadmin\ReportMonitoring::class)->name('reports');
        Route::get('/map',        App\Livewire\Shared\InteractiveMap::class)->name('map');
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
        Route::get('/reports/{report}',      App\Livewire\Surveyor\ReportView::class)->name('reports.view');
        Route::get('/reports/{report}/edit', App\Livewire\Surveyor\ReportEdit::class)->name('reports.edit');
        Route::get('/map',                   App\Livewire\Shared\InteractiveMap::class)->name('map');
    });

// ═══════════════════════════════════════════════════════
// TA
// ═══════════════════════════════════════════════════════
Route::middleware(['auth', 'role:ta'])
    ->prefix('ta')
    ->name('ta.')
    ->group(function () {
        Route::get('/', App\Livewire\Ta\Dashboard::class)->name('dashboard');
        Route::get('/reports', App\Livewire\Ta\ReportList::class)->name('reports');
        Route::get('/reports/{report}/site-visit', App\Livewire\Ta\SiteVisitForm::class)->name('site-visits.form');
        Route::get('/map', App\Livewire\Shared\InteractiveMap::class)->name('map');
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
        Route::get('/map',               App\Livewire\Shared\InteractiveMap::class)->name('map');
    });

// ═══════════════════════════════════════════════════════
// DIRECTOR / PENGARAH
// ═══════════════════════════════════════════════════════
Route::middleware(['auth', 'role:director'])
    ->prefix('director')
    ->name('director.')
    ->group(function () {
        Route::get('/', App\Livewire\Director\Dashboard::class)->name('dashboard');
        Route::get('/reports', App\Livewire\Director\ReportList::class)->name('reports');
        Route::get('/reports/{report}', App\Livewire\Director\ReportView::class)->name('reports.view');
        Route::get('/map', App\Livewire\Shared\InteractiveMap::class)->name('map');
    });

// ═══════════════════════════════════════════════════════
// ENGINEERING HUB (Superadmin + Pengarah)
// ═══════════════════════════════════════════════════════
Route::middleware(['auth', 'role:superadmin,director'])
    ->prefix('engineering')
    ->name('engineering.')
    ->group(function () {
        Route::get('/', App\Livewire\Engineering\UnitHub::class)->name('hub');
        Route::get('/units/{unit:code}', App\Livewire\Engineering\UnitDashboard::class)->name('unit');
    });
