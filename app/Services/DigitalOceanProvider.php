<?php

namespace App\Services;

use App\Contracts\CloudProvider;
use Illuminate\Support\Facades\Http;

class DigitalOceanProvider implements CloudProvider
{
    private string $token;
    private string $baseUrl = 'https://api.digitalocean.com/v2';

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function createServer(array $params): array
    {
        $response = Http::withToken($this->token)
            ->post("{$this->baseUrl}/droplets", [
                'name' => $params['name'],
                'region' => $params['region'],
                'size' => $params['size'],
                'image' => $params['image'] ?? 'ubuntu-22-04-x64',
                'ssh_keys' => $params['ssh_keys'] ?? [],
                'backups' => $params['backups'] ?? false,
                'ipv6' => $params['ipv6'] ?? false,
                'monitoring' => $params['monitoring'] ?? true,
                'tags' => $params['tags'] ?? ['gohan'],
            ]);

        $response->throw();

        return $response->json('droplet');
    }

    public function deleteServer(string $serverId): void
    {
        Http::withToken($this->token)
            ->delete("{$this->baseUrl}/droplets/{$serverId}")
            ->throw();
    }

    public function rebootServer(string $serverId): void
    {
        Http::withToken($this->token)
            ->post("{$this->baseUrl}/droplets/{$serverId}/actions", [
                'type' => 'reboot',
            ])
            ->throw();
    }

    public function getServer(string $serverId): array
    {
        $response = Http::withToken($this->token)
            ->get("{$this->baseUrl}/droplets/{$serverId}");

        $response->throw();

        return $response->json('droplet');
    }

    public function getRegions(): array
    {
        $response = Http::withToken($this->token)
            ->get("{$this->baseUrl}/regions");

        $response->throw();

        return $response->json('regions');
    }

    public function getSizes(): array
    {
        $response = Http::withToken($this->token)
            ->get("{$this->baseUrl}/sizes");

        $response->throw();

        return $response->json('sizes');
    }

    public function addSshKey(string $name, string $publicKey): array
    {
        $response = Http::withToken($this->token)
            ->post("{$this->baseUrl}/account/keys", [
                'name' => $name,
                'public_key' => $publicKey,
            ]);

        $response->throw();

        return $response->json('ssh_key');
    }
}
