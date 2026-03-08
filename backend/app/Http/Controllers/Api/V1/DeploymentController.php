<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\RunDeployment;
use App\Models\Deployment;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeploymentController extends Controller
{
    public function index(Request $request, Site $site): JsonResponse
    {
        $this->authorizeSiteOwnership($request, $site);

        $deployments = $site->deployments()->latest()->paginate(20);

        return response()->json($deployments);
    }

    public function store(Request $request, Site $site): JsonResponse
    {
        $this->authorizeSiteOwnership($request, $site);

        $deployment = $site->deployments()->create([
            'branch' => $site->branch,
            'status' => 'pending',
        ]);

        RunDeployment::dispatch($deployment);

        return response()->json(['data' => $deployment], 201);
    }

    public function show(Request $request, Site $site, Deployment $deployment): JsonResponse
    {
        $this->authorizeSiteOwnership($request, $site);
        abort_unless($deployment->site_id === $site->id, 404);

        return response()->json(['data' => $deployment]);
    }

    public function webhook(Request $request, string $token): JsonResponse
    {
        $site = Site::where('webhook_token', $token)->firstOrFail();

        $deployment = $site->deployments()->create([
            'branch' => $site->branch,
            'status' => 'pending',
            'commit_hash' => $request->input('after') ?? $request->input('checkout_sha'),
            'commit_message' => $request->input('head_commit.message') ?? $request->input('commits.0.message'),
        ]);

        RunDeployment::dispatch($deployment);

        return response()->json(['data' => ['message' => 'Deployment queued.']], 202);
    }

    private function authorizeSiteOwnership(Request $request, Site $site): void
    {
        abort_unless($site->server->user_id === $request->user()->id, 403);
    }
}
