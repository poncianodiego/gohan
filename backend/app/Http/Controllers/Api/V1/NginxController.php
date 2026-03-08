<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NginxController extends Controller
{
    public function show(Request $request, Site $site): JsonResponse
    {
        abort_unless($site->server->user_id === $request->user()->id, 403);

        // In production, fetch via SSH from the server
        $config = $this->generateDefaultConfig($site);

        return response()->json(['data' => ['config' => $config]]);
    }

    public function update(Request $request, Site $site): JsonResponse
    {
        abort_unless($site->server->user_id === $request->user()->id, 403);

        $request->validate([
            'config' => ['required', 'string'],
        ]);

        // Job to write config, test, and reload nginx
        \App\Jobs\UpdateNginxConfig::dispatch($site, $request->input('config'));

        return response()->json(['data' => ['message' => 'Nginx configuration update initiated.']]);
    }

    private function generateDefaultConfig(Site $site): string
    {
        return <<<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name {$site->domain};
    root {$site->directory}{$site->web_directory};

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php{$site->php_version}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX;
    }
}
