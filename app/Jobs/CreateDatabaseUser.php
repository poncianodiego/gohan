<?php

namespace App\Jobs;

use App\Models\DatabaseUser;
use App\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateDatabaseUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Server $server,
        private DatabaseUser $databaseUser,
    ) {}

    public function handle(): void
    {
        // In production: SSH into server and create the database user
    }
}
