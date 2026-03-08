<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\QueueWorker;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QueueWorkerController extends Controller
{
    public function index(Request $request, Site $site): JsonResponse
    {
        $this->authorizeSiteOwnership($request, $site);

        return response()->json(['data' => $site->queueWorkers]);
    }

    public function store(Request $request, Site $site): JsonResponse
    {
        $this->authorizeSiteOwnership($request, $site);

        $validated = $request->validate([
            'connection' => ['sometimes', 'string'],
            'queue' => ['sometimes', 'string'],
            'timeout' => ['sometimes', 'integer', 'min:1'],
            'sleep' => ['sometimes', 'integer', 'min:0'],
            'tries' => ['sometimes', 'integer', 'min:0'],
            'processes' => ['sometimes', 'integer', 'min:1', 'max:10'],
        ]);

        $worker = $site->queueWorkers()->create($validated);

        return response()->json(['data' => $worker], 201);
    }

    public function update(Request $request, Site $site, QueueWorker $worker): JsonResponse
    {
        $this->authorizeSiteOwnership($request, $site);
        abort_unless($worker->site_id === $site->id, 404);

        $validated = $request->validate([
            'status' => ['sometimes', 'in:running,stopped,restarting'],
            'timeout' => ['sometimes', 'integer'],
            'sleep' => ['sometimes', 'integer'],
            'tries' => ['sometimes', 'integer'],
            'processes' => ['sometimes', 'integer', 'min:1', 'max:10'],
        ]);

        $worker->update($validated);

        return response()->json(['data' => $worker]);
    }

    public function destroy(Request $request, Site $site, QueueWorker $worker): JsonResponse
    {
        $this->authorizeSiteOwnership($request, $site);
        abort_unless($worker->site_id === $site->id, 404);

        $worker->delete();

        return response()->json(null, 204);
    }

    private function authorizeSiteOwnership(Request $request, Site $site): void
    {
        abort_unless($site->server->user_id === $request->user()->id, 403);
    }
}
