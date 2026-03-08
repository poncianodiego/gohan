<?php

namespace App\Contracts;

interface CloudProvider
{
    public function createServer(array $params): array;

    public function deleteServer(string $serverId): void;

    public function rebootServer(string $serverId): void;

    public function getServer(string $serverId): array;

    public function getRegions(): array;

    public function getSizes(): array;

    public function addSshKey(string $name, string $publicKey): array;
}
