<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'last_activation',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    public function todayLogs(): HasMany
    {
        return $this->hasMany(Log::class)->whereDate('created_at', Carbon::now());
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class);
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrUser::class);
    }

    public function getLastActivationAttribute(): ?string
    {
        if (array_key_exists('last_activation_at', $this->attributes)) {
            $value = $this->attributes['last_activation_at'];

            return $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
        }

        return $this->qrCodes()
            ->where('active', false)
            ->orderByDesc('updated_at')
            ->value('updated_at');
    }
}
