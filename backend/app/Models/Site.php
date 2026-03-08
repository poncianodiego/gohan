<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'git_provider_id',
        'domain',
        'project_type',
        'directory',
        'web_directory',
        'php_version',
        'repository',
        'branch',
        'deploy_script',
        'webhook_token',
        'visibility',
        'ssl_enabled',
        'ssl_expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'ssl_enabled' => 'boolean',
            'ssl_expires_at' => 'datetime',
        ];
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function gitProvider(): BelongsTo
    {
        return $this->belongsTo(GitProvider::class);
    }

    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class);
    }

    public function queueWorkers(): HasMany
    {
        return $this->hasMany(QueueWorker::class);
    }

    public function cronJobs(): HasMany
    {
        return $this->hasMany(CronJob::class);
    }
}
