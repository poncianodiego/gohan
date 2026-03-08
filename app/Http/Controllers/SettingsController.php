<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Settings', [
            'sshKeys' => $request->user()->sshKeys()->latest()->get(),
            'credentials' => $request->user()->cloudCredentials()->latest()->get(),
            'gitProviders' => $request->user()->gitProviders()->latest()->get(),
        ]);
    }

    public function storeSshKey(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'public_key' => ['required', 'string'],
        ]);

        $fingerprint = $this->generateFingerprint($validated['public_key']);

        $request->user()->sshKeys()->create([
            ...$validated,
            'fingerprint' => $fingerprint,
        ]);

        return back();
    }

    public function destroySshKey(Request $request, int $id)
    {
        $key = $request->user()->sshKeys()->findOrFail($id);
        $key->delete();

        return back();
    }

    public function storeCredential(Request $request)
    {
        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:digitalocean'],
            'name' => ['required', 'string', 'max:255'],
            'token' => ['required', 'string'],
        ]);

        $request->user()->cloudCredentials()->create($validated);

        return back();
    }

    public function destroyCredential(Request $request, int $id)
    {
        $credential = $request->user()->cloudCredentials()->findOrFail($id);
        $credential->delete();

        return back();
    }

    public function storeGitProvider(Request $request)
    {
        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:github,gitlab'],
            'name' => ['required', 'string', 'max:255'],
            'token' => ['required', 'string'],
        ]);

        $request->user()->gitProviders()->create($validated);

        return back();
    }

    public function destroyGitProvider(Request $request, int $id)
    {
        $provider = $request->user()->gitProviders()->findOrFail($id);
        $provider->delete();

        return back();
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
