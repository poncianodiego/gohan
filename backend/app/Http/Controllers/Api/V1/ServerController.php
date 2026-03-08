<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ProvisionServer;
use App\Jobs\DeleteServer;
use App\Jobs\InstallClaudeCode;
use App\Models\Server;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $servers = $request->user()->servers()
            ->withCount('sites')
            ->latest()
            ->get();

        return response()->json(['data' => $servers]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cloud_credential_id' => ['required', 'exists:cloud_credentials,id'],
            'name' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string'],
            'size' => ['required', 'string'],
            'php_version' => ['sometimes', 'string', 'in:8.1,8.2,8.3,8.4'],
            'database_type' => ['sometimes', 'string', 'in:mysql,postgresql'],
            'ssh_key_ids' => ['sometimes', 'array'],
            'ssh_key_ids.*' => ['exists:ssh_keys,id'],
        ]);

        $credential = $request->user()->cloudCredentials()->findOrFail($validated['cloud_credential_id']);

        $server = $request->user()->servers()->create([
            'cloud_credential_id' => $credential->id,
            'name' => $validated['name'],
            'region' => $validated['region'],
            'size' => $validated['size'],
            'php_version' => $validated['php_version'] ?? '8.3',
            'database_type' => $validated['database_type'] ?? 'postgresql',
            'sudo_password' => Str::random(32),
            'status' => 'provisioning',
        ]);

        ProvisionServer::dispatch($server, $validated['ssh_key_ids'] ?? []);

        return response()->json(['data' => $server], 201);
    }

    public function show(Request $request, Server $server): JsonResponse
    {
        $this->authorizeOwnership($request, $server);
        $server->loadCount('sites');

        return response()->json(['data' => $server]);
    }

    public function update(Request $request, Server $server): JsonResponse
    {
        $this->authorizeOwnership($request, $server);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
        ]);

        $server->update($validated);

        return response()->json(['data' => $server]);
    }

    public function destroy(Request $request, Server $server): JsonResponse
    {
        $this->authorizeOwnership($request, $server);

        $server->update(['status' => 'deleting']);
        DeleteServer::dispatch($server);

        return response()->json(null, 204);
    }

    public function reboot(Request $request, Server $server): JsonResponse
    {
        $this->authorizeOwnership($request, $server);

        // Dispatch reboot job
        \App\Jobs\RebootServer::dispatch($server);

        return response()->json(['data' => ['message' => 'Server reboot initiated.']]);
    }

    public function installClaudeCode(Request $request, Server $server): JsonResponse
    {
        $this->authorizeOwnership($request, $server);

        InstallClaudeCode::dispatch($server);

        return response()->json(['data' => ['message' => 'Claude Code installation initiated.']]);
    }

    private function authorizeOwnership(Request $request, Server $server): void
    {
        abort_unless($server->user_id === $request->user()->id, 403);
    }
}
