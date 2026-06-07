<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles, Notifiable;

    protected $fillable = [
        'name', 'employee_id', 'email', 'phone', 'password',
        'jurisdiction_type', 'jurisdiction_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function statusLogs(): HasMany
    {
        return $this->hasMany(FeederStatusLog::class, 'updated_by');
    }

    public function jurisdictionLabel(): string
    {
        static $cache = [];
        $key = $this->jurisdiction_type . ':' . $this->jurisdiction_id;
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        return $cache[$key] = match ($this->jurisdiction_type) {
            'circle'       => optional(Circle::find($this->jurisdiction_id))->name ?? '—',
            'division'     => optional(Division::find($this->jurisdiction_id))->name ?? '—',
            'sub_division' => optional(SubDivision::find($this->jurisdiction_id))->name ?? '—',
            default        => 'Global',
        };
    }
}
