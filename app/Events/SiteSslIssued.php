<?php

namespace App\Events;

use App\Models\Site;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SiteSslIssued implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Site $site) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("user.{$this->site->server->user_id}")];
    }

    public function broadcastAs(): string
    {
        return 'site.ssl.issued';
    }
}
