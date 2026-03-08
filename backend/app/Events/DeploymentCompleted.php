<?php

namespace App\Events;

use App\Models\Deployment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeploymentCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Deployment $deployment) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("user.{$this->deployment->site->server->user_id}")];
    }

    public function broadcastAs(): string
    {
        return 'deployment.completed';
    }
}
