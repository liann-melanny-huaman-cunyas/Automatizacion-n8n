<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\MatriculaController;
use App\Http\Controllers\CertificadoController;
use App\Http\Controllers\ComunicadoController;
use App\Http\Controllers\SeguridadController;
use App\Http\Controllers\MetricaController;

use App\Http\Middleware\CeficAuth;

Route::get('/', function () {
    return session()->has('usuario_id')
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.process');

Route::middleware([CeficAuth::class])->group(function () {

    Route::get('/inicio', [DashboardController::class, 'index'])
        ->name('dashboard');

    // ===== CRUD con modales (sin páginas create/show/edit) =====
    Route::resource('estudiantes', EstudianteController::class)
        ->except(['create', 'show', 'edit']);

    Route::resource('matriculas', MatriculaController::class)
        ->except(['create', 'show', 'edit']);

    Route::resource('certificados', CertificadoController::class)
        ->except(['create', 'show', 'edit']);

    Route::get('/comunicados/nuevo', [ComunicadoController::class, 'create'])
        ->name('comunicados.create');

    Route::resource('comunicados', ComunicadoController::class)
        ->except(['create', 'show', 'edit']);

    // ===== Otros módulos =====
    Route::get('/seguridad', [SeguridadController::class, 'index'])
        ->name('seguridad.index');

    Route::get('/metricas', [MetricaController::class, 'index'])
        ->name('metricas.index');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});