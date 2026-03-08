<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Server extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cloud_credential_id',
        'name',
        'provider',
        'provider_server_id',
        'region',
        'size',
        'ip_address',
        'private_ip_address',
        'os',
        'php_version',
        'database_type',
        'status',
        'ssh_port',
        'sudo_password',
        'provision_command',
        'claude_code_installed',
        'meta',
    ];

    protected $hidden = [
        'sudo_password',
    ];

    protected function casts(): array
    {
        return [
            'sudo_password' => 'encrypted',
            'claude_code_installed' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cloudCredential(): BelongsTo
    {
        return $this->belongsTo(CloudCredential::class);
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function databases(): HasMany
    {
        return $this->hasMany(Database::class);
    }

    public function databaseUsers(): HasMany
    {
        return $this->hasMany(DatabaseUser::class);
    }
}
