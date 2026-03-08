<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CloudCredentialController;
use App\Http\Controllers\Api\V1\DatabaseController;
use App\Http\Controllers\Api\V1\DeploymentController;
use App\Http\Controllers\Api\V1\GitProviderController;
use App\Http\Controllers\Api\V1\LogController;
use App\Http\Controllers\Api\V1\NginxController;
use App\Http\Controllers\Api\V1\QueueWorkerController;
use App\Http\Controllers\Api\V1\ServerController;
use App\Http\Controllers\Api\V1\SiteController;
use App\Http\Controllers\Api\V1\SshKeyController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Webhook endpoint (no auth required)
    Route::post('/webhook/deploy/{token}', [DeploymentController::class, 'webhook']);
});

// Authenticated routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // SSH Keys
    Route::apiResource('ssh-keys', SshKeyController::class)->parameters(['ssh-keys' => 'sshKey']);

    // Cloud Credentials
    Route::apiResource('credentials', CloudCredentialController::class)->parameters(['credentials' => 'credential']);

    // Servers
    Route::apiResource('servers', ServerController::class);
    Route::post('/servers/{server}/reboot', [ServerController::class, 'reboot']);
    Route::post('/servers/{server}/claude-code', [ServerController::class, 'installClaudeCode']);

    // Sites (nested under servers for creation)
    Route::get('/servers/{server}/sites', [SiteController::class, 'index']);
    Route::post('/servers/{server}/sites', [SiteController::class, 'store']);
    Route::get('/sites/{site}', [SiteController::class, 'show']);
    Route::put('/sites/{site}', [SiteController::class, 'update']);
    Route::delete('/sites/{site}', [SiteController::class, 'destroy']);
    Route::post('/sites/{site}/visibility', [SiteController::class, 'toggleVisibility']);
    Route::post('/sites/{site}/ssl', [SiteController::class, 'installSsl']);
    Route::get('/sites/{site}/env', [SiteController::class, 'env']);
    Route::put('/sites/{site}/env', [SiteController::class, 'updateEnv']);

    // Deployments
    Route::get('/sites/{site}/deployments', [DeploymentController::class, 'index']);
    Route::post('/sites/{site}/deployments', [DeploymentController::class, 'store']);
    Route::get('/sites/{site}/deployments/{deployment}', [DeploymentController::class, 'show']);

    // Databases
    Route::get('/servers/{server}/databases', [DatabaseController::class, 'index']);
    Route::post('/servers/{server}/databases', [DatabaseController::class, 'store']);
    Route::delete('/servers/{server}/databases/{database}', [DatabaseController::class, 'destroy']);
    Route::get('/servers/{server}/database-users', [DatabaseController::class, 'users']);
    Route::post('/servers/{server}/database-users', [DatabaseController::class, 'storeUser']);

    // Logs
    Route::get('/sites/{site}/logs/{type}', [LogController::class, 'show']);
    Route::get('/log-types', [LogController::class, 'types']);

    // Nginx
    Route::get('/sites/{site}/nginx', [NginxController::class, 'show']);
    Route::put('/sites/{site}/nginx', [NginxController::class, 'update']);

    // Queue Workers
    Route::apiResource('sites.queue-workers', QueueWorkerController::class)
        ->parameters(['queue-workers' => 'worker'])
        ->shallow();

    // Git Providers
    Route::apiResource('git-providers', GitProviderController::class)
        ->parameters(['git-providers' => 'gitProvider'])
        ->only(['index', 'store', 'destroy']);
    Route::get('/git-providers/{gitProvider}/repos', [GitProviderController::class, 'repositories']);
});
