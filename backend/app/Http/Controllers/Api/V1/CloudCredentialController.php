<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CloudCredential;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CloudCredentialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $credentials = $request->user()->cloudCredentials()->latest()->get();

        return response()->json(['data' => $credentials]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:digitalocean'],
            'name' => ['required', 'string', 'max:255'],
            'token' => ['required', 'string'],
        ]);

        $credential = $request->user()->cloudCredentials()->create($validated);

        return response()->json(['data' => $credential], 201);
    }

    public function show(Request $request, CloudCredential $credential): JsonResponse
    {
        $this->authorizeOwnership($request, $credential);

        return response()->json(['data' => $credential]);
    }

    public function destroy(Request $request, CloudCredential $credential): JsonResponse
    {
        $this->authorizeOwnership($request, $credential);
        $credential->delete();

        return response()->json(null, 204);
    }

    private function authorizeOwnership(Request $request, CloudCredential $credential): void
    {
        abort_unless($credential->user_id === $request->user()->id, 403);
    }
}
