<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Substation extends Model
{
    protected $fillable = ['sub_division_id', 'name'];

    public function subDivision(): BelongsTo
    {
        return $this->belongsTo(SubDivision::class);
    }

    public function feeders(): HasMany
    {
        return $this->hasMany(Feeder::class);
    }
}
