<?php

namespace App\Jobs;

use App\Models\Server;
use App\Services\DigitalOceanProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteServer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Server $server) {}

    public function handle(): void
    {
        if ($this->server->provider_server_id) {
            $provider = new DigitalOceanProvider($this->server->cloudCredential->token);
            $provider->deleteServer($this->server->provider_server_id);
        }

        $this->server->delete();
    }
}
