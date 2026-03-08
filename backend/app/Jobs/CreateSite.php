<?php

namespace App\Jobs;

use App\Models\Site;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateSite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public function __construct(
        private Site $site,
        private bool $freshLaravel = false,
    ) {}

    public function handle(): void
    {
        // In production, SSH into server and:
        // 1. Create site directory
        // 2. Configure Nginx vhost
        // 3. Set up HTTP Basic Auth (private by default)
        // 4. Either install fresh Laravel or clone from repo
        // 5. Run deployment script

        $this->site->update(['status' => 'active']);
    }

    public function nginxVhostConfig(): string
    {
        $site = $this->site;

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
