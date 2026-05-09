<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SitePage extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<SitePageContent, $this>
     */
    public function contents(): HasMany
    {
        return $this->hasMany(SitePageContent::class);
    }

    public function isPublished(): bool
    {
        if ($this->published_at === null) {
            return false;
        }

        return $this->published_at->isPast();
    }
}
