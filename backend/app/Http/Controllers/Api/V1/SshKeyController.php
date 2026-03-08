<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SshKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SshKeyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $keys = $request->user()->sshKeys()->latest()->get();

        return response()->json(['data' => $keys]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'public_key' => ['required', 'string'],
        ]);

        $fingerprint = $this->generateFingerprint($validated['public_key']);

        $key = $request->user()->sshKeys()->create([
            ...$validated,
            'fingerprint' => $fingerprint,
        ]);

        return response()->json(['data' => $key], 201);
    }

    public function show(Request $request, SshKey $sshKey): JsonResponse
    {
        $this->authorizeOwnership($request, $sshKey);

        return response()->json(['data' => $sshKey]);
    }

    public function destroy(Request $request, SshKey $sshKey): JsonResponse
    {
        $this->authorizeOwnership($request, $sshKey);
        $sshKey->delete();

        return response()->json(null, 204);
    }

    private function authorizeOwnership(Request $request, SshKey $sshKey): void
    {
        abort_unless($sshKey->user_id === $request->user()->id, 403);
    }

    private function generateFingerprint(string $publicKey): ?string
    {
        $parts = explode(' ', trim($publicKey));
        if (count($parts) < 2) {
            return null;
        }

        $keyData = base64_decode($parts[1]);
        if ($keyData === false) {
            return null;
        }

        return implode(':', str_split(md5($keyData), 2));
    }
}
