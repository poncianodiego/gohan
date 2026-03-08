<?php

namespace App\Jobs;

use App\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InstallClaudeCode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct(private Server $server) {}

    public function handle(): void
    {
        // In production, this would SSH into the server and run the install script
        // For now, mark as installed
        $this->server->update(['claude_code_installed' => true]);
    }

    public function installScript(): string
    {
        return <<<'BASH'
#!/bin/bash
set -e

# Install Claude Code
npm install -g @anthropic-ai/claude-code

echo "Claude Code installed successfully"
BASH;
    }
}
