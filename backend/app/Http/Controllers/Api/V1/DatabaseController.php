<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Database;
use App\Models\DatabaseUser;
use App\Models\Server;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DatabaseController extends Controller
{
    public function index(Request $request, Server $server): JsonResponse
    {
        $this->authorizeOwnership($request, $server);

        $databases = $server->databases()->latest()->get();

        return response()->json(['data' => $databases]);
    }

    public function store(Request $request, Server $server): JsonResponse
    {
        $this->authorizeOwnership($request, $server);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_]+$/'],
        ]);

        $database = $server->databases()->create([
            'name' => $validated['name'],
            'type' => $server->database_type,
        ]);

        \App\Jobs\CreateDatabase::dispatch($server, $database);

        return response()->json(['data' => $database], 201);
    }

    public function destroy(Request $request, Server $server, Database $database): JsonResponse
    {
        $this->authorizeOwnership($request, $server);
        abort_unless($database->server_id === $server->id, 404);

        $database->delete();

        return response()->json(null, 204);
    }

    public function users(Request $request, Server $server): JsonResponse
    {
        $this->authorizeOwnership($request, $server);

        $users = $server->databaseUsers()->with('databases')->latest()->get();

        return response()->json(['data' => $users]);
    }

    public function storeUser(Request $request, Server $server): JsonResponse
    {
        $this->authorizeOwnership($request, $server);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_]+$/'],
            'password' => ['required', 'string', 'min:8'],
            'database_ids' => ['sometimes', 'array'],
            'database_ids.*' => ['exists:databases,id'],
        ]);

        $dbUser = $server->databaseUsers()->create([
            'name' => $validated['name'],
            'password' => $validated['password'],
        ]);

        if (!empty($validated['database_ids'])) {
            $dbUser->databases()->attach($validated['database_ids']);
        }

        \App\Jobs\CreateDatabaseUser::dispatch($server, $dbUser);

        return response()->json(['data' => $dbUser], 201);
    }

    private function authorizeOwnership(Request $request, Server $server): void
    {
        abort_unless($server->user_id === $request->user()->id, 403);
    }
}
