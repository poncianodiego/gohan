<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GitProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GitProviderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $providers = $request->user()->gitProviders()->latest()->get();

        return response()->json(['data' => $providers]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:github,gitlab'],
            'name' => ['required', 'string', 'max:255'],
            'token' => ['required', 'string'],
        ]);

        $provider = $request->user()->gitProviders()->create($validated);

        return response()->json(['data' => $provider], 201);
    }

    public function destroy(Request $request, GitProvider $gitProvider): JsonResponse
    {
        abort_unless($gitProvider->user_id === $request->user()->id, 403);
        $gitProvider->delete();

        return response()->json(null, 204);
    }

    public function repositories(Request $request, GitProvider $gitProvider): JsonResponse
    {
        abort_unless($gitProvider->user_id === $request->user()->id, 403);

        $repos = $this->fetchRepositories($gitProvider);

        return response()->json(['data' => $repos]);
    }

    private function fetchRepositories(GitProvider $provider): array
    {
        $token = $provider->token;

        if ($provider->provider === 'github') {
            $response = Http::withToken($token)
                ->get('https://api.github.com/user/repos', [
                    'sort' => 'updated',
                    'per_page' => 100,
                ]);

            return collect($response->json())->map(fn ($repo) => [
                'id' => $repo['id'],
                'name' => $repo['full_name'],
                'url' => $repo['clone_url'],
                'private' => $repo['private'],
                'default_branch' => $repo['default_branch'],
            ])->all();
        }

        if ($provider->provider === 'gitlab') {
            $response = Http::withToken($token)
                ->get('https://gitlab.com/api/v4/projects', [
                    'membership' => true,
                    'order_by' => 'last_activity_at',
                    'per_page' => 100,
                ]);

            return collect($response->json())->map(fn ($repo) => [
                'id' => $repo['id'],
                'name' => $repo['path_with_namespace'],
                'url' => $repo['http_url_to_repo'],
                'private' => $repo['visibility'] === 'private',
                'default_branch' => $repo['default_branch'],
            ])->all();
        }

        return [];
    }
}
