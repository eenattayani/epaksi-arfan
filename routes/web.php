<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFileController;
use App\Http\Controllers\ProjectPhotoController;
use App\Http\Controllers\ProjectMapController;
use App\Http\Controllers\UserController;

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {

    // ── Dashboard & Map Explorer ─────────────────────
    // Semua role bisa akses
    Route::get('/', [DashboardController::class, 'index'])
         ->name('dashboard');
    Route::get('map-explorer', [DashboardController::class, 'mapExplorer'])
         ->name('map.explorer');

    // ── Projects — Lihat (semua role) ───────────────
    Route::get('projects',         [ProjectController::class, 'index'])
         ->name('projects.index');
    Route::get('projects/{project}',[ProjectController::class, 'show'])
         ->name('projects.show');
    Route::get('projects/{project}/map', [ProjectMapController::class, 'show'])
         ->name('projects.map');

    // ── Projects — Create & Edit (admin & editor) ───
    Route::middleware(['role:admin,editor'])->group(function () {
        Route::get('projects/create',          [ProjectController::class, 'create'])
             ->name('projects.create');
        Route::post('projects',                [ProjectController::class, 'store'])
             ->name('projects.store');
        Route::get('projects/{project}/edit',  [ProjectController::class, 'edit'])
             ->name('projects.edit');
        Route::put('projects/{project}',       [ProjectController::class, 'update'])
             ->name('projects.update');
    });

    // ── Projects — Delete (admin only) ──────────────
    Route::middleware(['role:admin'])->group(function () {
        Route::delete('projects/{project}', [ProjectController::class, 'destroy'])
             ->name('projects.destroy');
    });

    // ── Files — View & Download (semua role) ────────
    Route::get('projects/{project}/files/{file}/view',
         [ProjectFileController::class, 'view'])
         ->name('projects.files.view');

    // ── Files — Upload & Manage (admin & editor) ────
    Route::middleware(['role:admin,editor'])
         ->prefix('projects/{project}/files')
         ->name('projects.files.')
         ->group(function () {
             Route::post('/',               [ProjectFileController::class, 'store'])
                  ->name('store');
             Route::delete('/{file}',       [ProjectFileController::class, 'destroy'])
                  ->name('destroy');
             Route::patch('/{file}/toggle', [ProjectFileController::class, 'toggle'])
                  ->name('toggle');
             Route::patch('/{file}/color',  [ProjectFileController::class, 'color'])
                  ->name('color');
         });

    // ── Photos — Upload & Delete (admin & editor) ───
    Route::middleware(['role:admin,editor'])
         ->prefix('projects/{project}/photos')
         ->name('projects.photos.')
         ->group(function () {
             Route::post('/',          [ProjectPhotoController::class, 'store'])
                  ->name('store');
             Route::delete('/{photo}', [ProjectPhotoController::class, 'destroy'])
                  ->name('destroy');
         });

    // ── User Management (admin only) ────────────────
    Route::middleware(['role:admin'])
         ->prefix('users')
         ->name('users.')
         ->group(function () {
             Route::get('/',              [UserController::class, 'index'])
                  ->name('index');
             Route::get('/create',        [UserController::class, 'create'])
                  ->name('create');
             Route::post('/',             [UserController::class, 'store'])
                  ->name('store');
             Route::get('/{user}/edit',   [UserController::class, 'edit'])
                  ->name('edit');
             Route::put('/{user}',        [UserController::class, 'update'])
                  ->name('update');
             Route::delete('/{user}',     [UserController::class, 'destroy'])
                  ->name('destroy');
         });

});