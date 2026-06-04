<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    protected $fillable = ['circle_id', 'name'];

    public function circle(): BelongsTo
    {
        return $this->belongsTo(Circle::class);
    }

    public function subDivisions(): HasMany
    {
        return $this->hasMany(SubDivision::class);
    }
}
