<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feeder extends Model
{
    protected $fillable = [
        'substation_id', 'name', 'tnd_code', 'category',
        'total_consumer', 'total_tc', 'current_status',
        'last_updated_by', 'last_updated_at',
    ];

    protected $casts = [
        'last_updated_at' => 'datetime',
    ];

    public function substation(): BelongsTo
    {
        return $this->belongsTo(Substation::class);
    }

    public function lastUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(FeederStatusLog::class);
    }
}
