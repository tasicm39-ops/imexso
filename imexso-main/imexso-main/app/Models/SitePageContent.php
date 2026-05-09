<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitePageContent extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'site_page_id',
        'locale',
        'payload',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    /**
     * @return BelongsTo<SitePage, $this>
     */
    public function sitePage(): BelongsTo
    {
        return $this->belongsTo(SitePage::class);
    }
}
