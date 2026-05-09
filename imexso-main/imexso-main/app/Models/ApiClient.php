<?php

namespace App\Models;

use Database\Factories\ApiClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class ApiClient extends Model
{
    /** @use HasFactory<ApiClientFactory> */
    use HasApiTokens, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'contact_email',
        'description',
        'is_active',
        'country',
        'allowed_abilities',
        'rate_limit_per_minute',
        'last_used_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'allowed_abilities' => 'array',
            'rate_limit_per_minute' => 'integer',
            'last_used_at' => 'datetime',
        ];
    }
}
