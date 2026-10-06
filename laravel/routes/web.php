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

    Route::get('/estudiantes', [EstudianteController::class, 'index'])
        ->name('estudiantes.index');

    Route::get('/matriculas', [MatriculaController::class, 'index'])
        ->name('matriculas.index');

    Route::get('/certificados', [CertificadoController::class, 'index'])
        ->name('certificados.index');

    Route::get('/comunicados', [ComunicadoController::class, 'index'])
        ->name('comunicados.index');

    Route::get('/comunicados/nuevo', [ComunicadoController::class, 'create'])
        ->name('comunicados.create');

    Route::post('/comunicados', [ComunicadoController::class, 'store'])
        ->name('comunicados.store');

    Route::get('/seguridad', [SeguridadController::class, 'index'])
        ->name('seguridad.index');

    Route::get('/metricas', [MetricaController::class, 'index'])
        ->name('metricas.index');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});