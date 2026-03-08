<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueWorker extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'connection',
        'queue',
        'timeout',
        'sleep',
        'tries',
        'processes',
        'status',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
