<?php

namespace App\Http\Controllers;

use App\Jobs\RunDeployment;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function deploy(Request $request, string $token): JsonResponse
    {
        $site = Site::where('webhook_token', $token)->firstOrFail();

        $deployment = $site->deployments()->create([
            'branch' => $site->branch,
            'status' => 'pending',
            'commit_hash' => $request->input('after') ?? $request->input('checkout_sha'),
            'commit_message' => $request->input('head_commit.message') ?? $request->input('commits.0.message'),
        ]);

        RunDeployment::dispatch($deployment);

        return response()->json(['message' => 'Deployment queued.'], 202);
    }
}
