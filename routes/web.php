<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFileController;
use App\Http\Controllers\ProjectPhotoController;
use App\Http\Controllers\ProjectMapController;

// ── Public routes (no login needed) ───────────────────
// Auth routes (login, logout, password reset)
require __DIR__.'/auth.php';

// ── Protected routes (must be logged in) ──────────────
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])
         ->name('dashboard');

    // Projects
    Route::resource('projects', ProjectController::class);

    // Map viewer
    Route::get('projects/{project}/map', [ProjectMapController::class, 'show'])
         ->name('projects.map');

    // Map Explorer
    Route::get('map-explorer', [DashboardController::class, 'mapExplorer'])
         ->name('map.explorer');

    // GIS File routes
    Route::prefix('projects/{project}/files')
         ->name('projects.files.')
         ->group(function () {
             Route::post('/',               [ProjectFileController::class, 'store'])   ->name('store');
             Route::delete('/{file}',       [ProjectFileController::class, 'destroy']) ->name('destroy');
             Route::patch('/{file}/toggle', [ProjectFileController::class, 'toggle'])  ->name('toggle');
             Route::patch('/{file}/color',  [ProjectFileController::class, 'color'])   ->name('color');
         });

    // Photo routes
    Route::prefix('projects/{project}/photos')
         ->name('projects.photos.')
         ->group(function () {
             Route::post('/',          [ProjectPhotoController::class, 'store'])   ->name('store');
             Route::delete('/{photo}', [ProjectPhotoController::class, 'destroy']) ->name('destroy');
         });

});