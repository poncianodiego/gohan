<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogController extends Controller
{
    private const LOG_TYPES = [
        'nginx-access' => '/var/log/nginx/{domain}-access.log',
        'nginx-error' => '/var/log/nginx/{domain}-error.log',
        'laravel' => '{site_path}/storage/logs/laravel.log',
        'php-fpm' => '/var/log/php{php_version}-fpm.log',
        'queue' => '{site_path}/storage/logs/worker.log',
    ];

    public function show(Request $request, Site $site, string $type): JsonResponse
    {
        abort_unless($site->server->user_id === $request->user()->id, 403);
        abort_unless(array_key_exists($type, self::LOG_TYPES), 404, 'Invalid log type.');

        // In production, this would SSH into the server and tail the log
        // For now, return the log path info
        $path = str_replace(
            ['{domain}', '{site_path}', '{php_version}'],
            [$site->domain, $site->directory, $site->php_version],
            self::LOG_TYPES[$type]
        );

        return response()->json([
            'data' => [
                'type' => $type,
                'path' => $path,
                'lines' => [],
                'message' => 'Connect via WebSocket for real-time log streaming.',
            ],
        ]);
    }

    public function types(): JsonResponse
    {
        return response()->json([
            'data' => array_keys(self::LOG_TYPES),
        ]);
    }
}
