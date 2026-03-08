<?php

namespace App\Jobs;

use App\Events\DeploymentCompleted;
use App\Events\DeploymentFailed;
use App\Events\DeploymentStarted;
use App\Models\Deployment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunDeployment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public function __construct(private Deployment $deployment) {}

    public function handle(): void
    {
        $this->deployment->update([
            'status' => 'running',
            'started_at' => now(),
        ]);

        event(new DeploymentStarted($this->deployment));

        try {
            // In production, SSH into the server and run the deploy script
            // The deploy script is stored on the site model
            $site = $this->deployment->site;

            // Simulate deployment
            $this->deployment->update([
                'status' => 'completed',
                'ended_at' => now(),
                'output' => 'Deployment completed successfully.',
            ]);

            event(new DeploymentCompleted($this->deployment));
        } catch (\Exception $e) {
            Log::error("Deployment failed: {$e->getMessage()}", [
                'deployment_id' => $this->deployment->id,
            ]);

            $this->deployment->update([
                'status' => 'failed',
                'ended_at' => now(),
                'output' => $e->getMessage(),
            ]);

            event(new DeploymentFailed($this->deployment));
        }
    }
}
