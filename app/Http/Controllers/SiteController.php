<?php

namespace App\Http\Controllers;

use App\Jobs\CreateSite;
use App\Jobs\InstallSsl;
use App\Jobs\ToggleSiteVisibility;
use App\Models\Server;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SiteController extends Controller
{
    public function create(Request $request, Server $server)
    {
        abort_unless($server->user_id === $request->user()->id, 403);

        return Inertia::render('Sites/Create', [
            'server' => $server,
        ]);
    }

    public function store(Request $request, Server $server)
    {
        abort_unless($server->user_id === $request->user()->id, 403);

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

        return redirect("/sites/{$site->id}");
    }

    public function show(Request $request, Site $site)
    {
        abort_unless($site->server->user_id === $request->user()->id, 403);

        $site->load(['server', 'deployments' => function ($q) {
            $q->latest()->limit(5);
        }]);

        return Inertia::render('Sites/Show', [
            'site' => $site,
        ]);
    }

    public function deploy(Request $request, Site $site)
    {
        abort_unless($site->server->user_id === $request->user()->id, 403);

        $deployment = $site->deployments()->create([
            'branch' => $site->branch,
            'status' => 'pending',
        ]);

        \App\Jobs\RunDeployment::dispatch($deployment);

        return back();
    }

    public function toggleVisibility(Request $request, Site $site)
    {
        abort_unless($site->server->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'visibility' => ['required', 'in:private,public'],
        ]);

        ToggleSiteVisibility::dispatch($site, $validated['visibility']);

        return back();
    }

    public function installSsl(Request $request, Site $site)
    {
        abort_unless($site->server->user_id === $request->user()->id, 403);

        InstallSsl::dispatch($site);

        return back();
    }

    public function env(Request $request, Site $site)
    {
        abort_unless($site->server->user_id === $request->user()->id, 403);

        return Inertia::render('Sites/Env', [
            'site' => $site,
            'envContent' => '# Environment file content loaded from server',
        ]);
    }

    public function updateEnv(Request $request, Site $site)
    {
        abort_unless($site->server->user_id === $request->user()->id, 403);

        $request->validate(['env' => ['required', 'string']]);

        \App\Jobs\UpdateSiteEnv::dispatch($site, $request->input('env'));

        return back();
    }

    public function logs(Request $request, Site $site)
    {
        abort_unless($site->server->user_id === $request->user()->id, 403);

        return Inertia::render('Sites/Logs', [
            'site' => $site,
            'logLines' => [],
            'logType' => $request->input('type', 'laravel'),
        ]);
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
}
