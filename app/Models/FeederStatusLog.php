<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeederStatusLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'feeder_id', 'old_status', 'new_status', 'remarks', 'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function feeder(): BelongsTo
    {
        return $this->belongsTo(Feeder::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
