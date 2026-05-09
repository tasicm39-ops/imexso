<?php

namespace App\Models;

use Database\Factories\CarMarketingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarMarketing extends Model
{
    /** @use HasFactory<CarMarketingFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'car_id',
        'limited_stock_enabled',
        'limited_stock_count',
        'new_price_enabled',
        'new_price_amount',
        'promotion_enabled',
        'promotion_label',
        'badge_text',
        'sold_enabled',
        'sold_visible_days',
        'sold_marked_at',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'limited_stock_enabled' => 'boolean',
            'limited_stock_count' => 'integer',
            'new_price_enabled' => 'boolean',
            'new_price_amount' => 'decimal:2',
            'promotion_enabled' => 'boolean',
            'sold_enabled' => 'boolean',
            'sold_visible_days' => 'integer',
            'sold_marked_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Car, $this>
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
