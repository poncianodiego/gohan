<?php

namespace App\Jobs;

use App\Events\ServerProvisioned;
use App\Events\ServerUnreachable;
use App\Models\Server;
use App\Services\DigitalOceanProvider;
use App\Services\ServerProvisionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProvisionServer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 600;

    public function __construct(
        private Server $server,
        private array $sshKeyIds = [],
    ) {}

    public function handle(ServerProvisionService $provisionService): void
    {
        try {
            $credential = $this->server->cloudCredential;
            $provider = new DigitalOceanProvider($credential->token);

            // Create droplet
            $droplet = $provider->createServer([
                'name' => $this->server->name,
                'region' => $this->server->region,
                'size' => $this->server->size,
                'image' => $this->server->os,
                'ssh_keys' => $this->getDigitalOceanSshKeys($provider),
                'monitoring' => true,
            ]);

            $this->server->update([
                'provider_server_id' => (string) $droplet['id'],
            ]);

            // Poll for IP address
            $attempts = 0;
            while ($attempts < 30) {
                sleep(10);
                $dropletData = $provider->getServer((string) $droplet['id']);

                if (!empty($dropletData['networks']['v4'])) {
                    foreach ($dropletData['networks']['v4'] as $network) {
                        if ($network['type'] === 'public') {
                            $this->server->update(['ip_address' => $network['ip_address']]);
                        }
                        if ($network['type'] === 'private') {
                            $this->server->update(['private_ip_address' => $network['ip_address']]);
                        }
                    }
                    if ($this->server->ip_address) {
                        break;
                    }
                }
                $attempts++;
            }

            if (!$this->server->ip_address) {
                throw new \RuntimeException('Failed to obtain server IP address.');
            }

            // Generate and store provision command
            $script = $provisionService->generateBootstrapScript($this->server);
            $this->server->update([
                'provision_command' => $script,
                'status' => 'active',
            ]);

            event(new ServerProvisioned($this->server));
        } catch (\Exception $e) {
            Log::error("Server provisioning failed: {$e->getMessage()}", [
                'server_id' => $this->server->id,
            ]);

            $this->server->update(['status' => 'unreachable']);
            event(new ServerUnreachable($this->server));
        }
    }

    private function getDigitalOceanSshKeys(DigitalOceanProvider $provider): array
    {
        if (empty($this->sshKeyIds)) {
            return [];
        }

        $keys = $this->server->user->sshKeys()->whereIn('id', $this->sshKeyIds)->get();
        $doKeys = [];

        foreach ($keys as $key) {
            try {
                $doKey = $provider->addSshKey($key->name, $key->public_key);
                $doKeys[] = $doKey['id'];
            } catch (\Exception $e) {
                // Key might already exist on DO
                Log::warning("Failed to add SSH key to DO: {$e->getMessage()}");
            }
        }

        return $doKeys;
    }
}
