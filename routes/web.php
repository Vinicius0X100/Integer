<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\EnsureUserIsAdmin;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    
    // Password Confirmation Routes
    Route::get('password/confirm', [ConfirmPasswordController::class, 'show'])->name('password.confirm');
    Route::post('password/confirm', [ConfirmPasswordController::class, 'store']);

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // System Health
    Route::get('/system-health', [App\Http\Controllers\SystemHealthController::class, 'index'])->name('system_health.index');
    Route::get('/system-health/metrics', [App\Http\Controllers\SystemHealthController::class, 'metrics'])->name('system_health.metrics');
    Route::get('/system-health/pdf', [App\Http\Controllers\SystemHealthController::class, 'generatePdf'])->name('system_health.pdf');

    // Rotas de Usuários
    Route::post('users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk_action');
    Route::post('users/generate-pdf', [UserController::class, 'generatePdf'])->name('users.pdf');
    Route::resource('users', UserController::class);

    // Rotas de SisMatriz Ticket
    Route::post('sismatriz/bulk-action', [\App\Http\Controllers\SisMatrizUserController::class, 'bulkAction'])->name('sismatriz.bulk_action');
    Route::post('sismatriz/generate-pdf', [\App\Http\Controllers\SisMatrizUserController::class, 'generatePdf'])->name('sismatriz.pdf');
    Route::resource('sismatriz', \App\Http\Controllers\SisMatrizUserController::class);

    // Rotas de SisMatriz Principal
    Route::post('sismatriz-main/bulk-action', [\App\Http\Controllers\SisMatrizMainUserController::class, 'bulkAction'])->name('sismatriz-main.bulk_action');
    Route::post('sismatriz-main/generate-pdf', [\App\Http\Controllers\SisMatrizMainUserController::class, 'generatePdf'])->name('sismatriz-main.pdf');
    Route::resource('sismatriz-main', \App\Http\Controllers\SisMatrizMainUserController::class);

    // Rotas de Clientes (Protegidas)
    Route::middleware([EnsureUserIsAdmin::class, 'password.confirm'])->group(function () {
        Route::post('clientes/bulk-action', [ClienteController::class, 'bulkAction'])->name('clientes.bulk_action');
        Route::any('clientes/generate-pdf', [ClienteController::class, 'generatePdf'])->name('clientes.pdf');
        Route::resource('clientes', ClienteController::class);
        
        Route::post('servicos/bulk-action', [ServicoController::class, 'bulkAction'])->name('servicos.bulk_action');
        Route::any('servicos/generate-pdf', [ServicoController::class, 'generatePdf'])->name('servicos.pdf');
        Route::resource('servicos', ServicoController::class);
    });
});
