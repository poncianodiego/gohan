<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Database extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'name',
        'type',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
