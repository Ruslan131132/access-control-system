<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    use HasFactory;

    public const SUCCESS = 1;

    public const FAIL = 2;

    public const WARNING = 3;

    protected $fillable = [
        'type',
        'text',
        'user_id',
        'ip',
        'user_agent',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
