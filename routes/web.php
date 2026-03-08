<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Webhook (no auth)
Route::post('/webhook/deploy/{token}', [WebhookController::class, 'deploy']);

// Authenticated
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', DashboardController::class)->name('dashboard');

    // Servers
    Route::get('/servers/create', [ServerController::class, 'create']);
    Route::post('/servers', [ServerController::class, 'store']);
    Route::get('/servers/{server}', [ServerController::class, 'show']);
    Route::delete('/servers/{server}', [ServerController::class, 'destroy']);
    Route::post('/servers/{server}/reboot', [ServerController::class, 'reboot']);
    Route::post('/servers/{server}/claude-code', [ServerController::class, 'installClaudeCode']);

    // Sites
    Route::get('/servers/{server}/sites/create', [SiteController::class, 'create']);
    Route::post('/servers/{server}/sites', [SiteController::class, 'store']);
    Route::get('/sites/{site}', [SiteController::class, 'show']);
    Route::post('/sites/{site}/deploy', [SiteController::class, 'deploy']);
    Route::post('/sites/{site}/visibility', [SiteController::class, 'toggleVisibility']);
    Route::post('/sites/{site}/ssl', [SiteController::class, 'installSsl']);
    Route::get('/sites/{site}/env', [SiteController::class, 'env']);
    Route::put('/sites/{site}/env', [SiteController::class, 'updateEnv']);
    Route::get('/sites/{site}/logs', [SiteController::class, 'logs']);

    // Settings
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::post('/settings/ssh-keys', [SettingsController::class, 'storeSshKey']);
    Route::delete('/settings/ssh-keys/{id}', [SettingsController::class, 'destroySshKey']);
    Route::post('/settings/credentials', [SettingsController::class, 'storeCredential']);
    Route::delete('/settings/credentials/{id}', [SettingsController::class, 'destroyCredential']);
    Route::post('/settings/git-providers', [SettingsController::class, 'storeGitProvider']);
    Route::delete('/settings/git-providers/{id}', [SettingsController::class, 'destroyGitProvider']);
});
