<?php

namespace App\Http\Controllers;

use App\Jobs\DeleteServer;
use App\Jobs\InstallClaudeCode;
use App\Jobs\ProvisionServer;
use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ServerController extends Controller
{
    public function create(Request $request)
    {
        return Inertia::render('Servers/Create', [
            'credentials' => $request->user()->cloudCredentials()->latest()->get(),
        ]);
    }

    public function store(Request $request)
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

        return redirect("/servers/{$server->id}");
    }

    public function show(Request $request, Server $server)
    {
        abort_unless($server->user_id === $request->user()->id, 403);

        return Inertia::render('Servers/Show', [
            'server' => $server,
            'sites' => $server->sites()->latest()->get(),
        ]);
    }

    public function destroy(Request $request, Server $server)
    {
        abort_unless($server->user_id === $request->user()->id, 403);

        $server->update(['status' => 'deleting']);
        DeleteServer::dispatch($server);

        return redirect('/');
    }

    public function reboot(Request $request, Server $server)
    {
        abort_unless($server->user_id === $request->user()->id, 403);

        \App\Jobs\RebootServer::dispatch($server);

        return back();
    }

    public function installClaudeCode(Request $request, Server $server)
    {
        abort_unless($server->user_id === $request->user()->id, 403);

        InstallClaudeCode::dispatch($server);

        return back();
    }
}
