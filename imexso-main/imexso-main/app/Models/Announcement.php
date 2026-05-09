<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Announcement extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'starts_at',
        'ends_at',
        'is_active',
        'show_to_validated_only',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'show_to_validated_only' => 'boolean',
        ];
    }

    /**
     * @return HasMany<AnnouncementTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(AnnouncementTranslation::class);
    }

    public function isCurrentlyActive(?\DateTimeInterface $now = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = $now ? Carbon::instance($now) : now();

        return $now->greaterThanOrEqualTo($this->starts_at)
            && $now->lessThanOrEqualTo($this->ends_at);
    }

    /**
     * Pick the best translation for a locale, falling back to French then English (and other supported locales).
     */
    public function translationForLocale(string $locale): ?AnnouncementTranslation
    {
        $this->loadMissing('translations');

        $byLocale = $this->translations->keyBy('locale');

        $chain = array_unique(array_merge(
            [$locale],
            config('locales.announcement_fallback', ['fr', 'en']),
            config('locales.supported'),
        ));

        foreach ($chain as $loc) {
            $t = $byLocale->get($loc);
            if ($t !== null && trim($t->title) !== '' && trim($t->body) !== '') {
                return $t;
            }
        }

        return null;
    }
}
