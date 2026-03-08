<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\CreateSite;
use App\Jobs\InstallSsl;
use App\Jobs\ToggleSiteVisibility;
use App\Models\Server;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SiteController extends Controller
{
    public function index(Request $request, Server $server): JsonResponse
    {
        $this->authorizeServerOwnership($request, $server);

        $sites = $server->sites()->with('deployments', function ($q) {
            $q->latest()->limit(1);
        })->latest()->get();

        return response()->json(['data' => $sites]);
    }

    public function store(Request $request, Server $server): JsonResponse
    {
        $this->authorizeServerOwnership($request, $server);

        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255'],
            'project_type' => ['sometimes', 'string', 'in:laravel'],
            'php_version' => ['sometimes', 'string'],
            'web_directory' => ['sometimes', 'string'],
            'repository' => ['sometimes', 'string'],
            'branch' => ['sometimes', 'string'],
            'git_provider_id' => ['sometimes', 'nullable', 'exists:git_providers,id'],
            'fresh_laravel' => ['sometimes', 'boolean'],
        ]);

        $site = $server->sites()->create([
            'domain' => $validated['domain'],
            'project_type' => $validated['project_type'] ?? 'laravel',
            'php_version' => $validated['php_version'] ?? $server->php_version,
            'web_directory' => $validated['web_directory'] ?? '/public',
            'repository' => $validated['repository'] ?? null,
            'branch' => $validated['branch'] ?? 'main',
            'git_provider_id' => $validated['git_provider_id'] ?? null,
            'webhook_token' => Str::random(40),
            'directory' => '/home/gohan/' . Str::slug($validated['domain']),
            'deploy_script' => $this->defaultDeployScript(),
            'status' => 'installing',
        ]);

        CreateSite::dispatch($site, $validated['fresh_laravel'] ?? false);

        return response()->json(['data' => $site], 201);
    }

    public function show(Request $request, Site $site): JsonResponse
    {
        $this->authorizeSiteOwnership($request, $site);
        $site->load(['server', 'deployments' => function ($q) {
            $q->latest()->limit(5);
        }]);

        return response()->json(['data' => $site]);
    }

    public function update(Request $request, Site $site): JsonResponse
    {
        $this->authorizeSiteOwnership($request, $site);

        $validated = $request->validate([
            'deploy_script' => ['sometimes', 'string'],
            'branch' => ['sometimes', 'string'],
            'repository' => ['sometimes', 'string'],
            'web_directory' => ['sometimes', 'string'],
        ]);

        $site->update($validated);

        return response()->json(['data' => $site]);
    }

    public function destroy(Request $request, Site $site): JsonResponse
    {
        $this->authorizeSiteOwnership($request, $site);
        $site->delete();

        return response()->json(null, 204);
    }

    public function toggleVisibility(Request $request, Site $site): JsonResponse
    {
        $this->authorizeSiteOwnership($request, $site);

        $validated = $request->validate([
            'visibility' => ['required', 'in:private,public'],
        ]);

        ToggleSiteVisibility::dispatch($site, $validated['visibility']);

        return response()->json(['data' => ['message' => 'Visibility change initiated.']]);
    }

    public function installSsl(Request $request, Site $site): JsonResponse
    {
        $this->authorizeSiteOwnership($request, $site);

        InstallSsl::dispatch($site);

        return response()->json(['data' => ['message' => 'SSL installation initiated.']]);
    }

    public function env(Request $request, Site $site): JsonResponse
    {
        $this->authorizeSiteOwnership($request, $site);

        return response()->json(['data' => ['env' => '# Environment file content loaded from server']]);
    }

    public function updateEnv(Request $request, Site $site): JsonResponse
    {
        $this->authorizeSiteOwnership($request, $site);

        $request->validate([
            'env' => ['required', 'string'],
        ]);

        // Job to write .env to server and restart services
        \App\Jobs\UpdateSiteEnv::dispatch($site, $request->input('env'));

        return response()->json(['data' => ['message' => 'Environment updated.']]);
    }

    private function defaultDeployScript(): string
    {
        return <<<'SCRIPT'
cd $SITE_PATH
git pull origin $BRANCH
composer install --no-interaction --prefer-dist --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan optimize
SCRIPT;
    }

    private function authorizeServerOwnership(Request $request, Server $server): void
    {
        abort_unless($server->user_id === $request->user()->id, 403);
    }

    private function authorizeSiteOwnership(Request $request, Site $site): void
    {
        abort_unless($site->server->user_id === $request->user()->id, 403);
    }
}
