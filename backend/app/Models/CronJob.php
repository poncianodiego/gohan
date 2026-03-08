<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CronJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'command',
        'expression',
        'user',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
