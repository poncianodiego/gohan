<?php

namespace App\Jobs;

use App\Models\Site;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ToggleSiteVisibility implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Site $site,
        private string $visibility,
    ) {}

    public function handle(): void
    {
        // In production: SSH into server, update Nginx config to add/remove basic auth
        $this->site->update(['visibility' => $this->visibility]);
    }
}
