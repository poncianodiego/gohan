<?php

namespace App\Jobs;

use App\Events\SiteSslIssued;
use App\Models\Site;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InstallSsl implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct(private Site $site) {}

    public function handle(): void
    {
        // In production: SSH into server, run certbot
        $this->site->update([
            'ssl_enabled' => true,
            'ssl_expires_at' => now()->addMonths(3),
        ]);

        event(new SiteSslIssued($this->site));
    }
}
