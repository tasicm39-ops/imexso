<?php

namespace App\Models;

use Database\Factories\OfferFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    /** @use HasFactory<OfferFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'car_id',
        'margin_type',
        'margin_amount',
        'vat_rate',
        'validity_days',
        'price_excl_vat',
        'price_incl_vat',
        'client_name',
        'client_email',
        'message',
        'setup_fees',
        'registration_fees',
        'admin_fees',
        'bonus_malus',
        'ww_fees',
        'delivery_type',
        'pdf_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'margin_amount' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'validity_days' => 'integer',
            'price_excl_vat' => 'decimal:2',
            'price_incl_vat' => 'decimal:2',
            'setup_fees' => 'decimal:2',
            'registration_fees' => 'decimal:2',
            'admin_fees' => 'decimal:2',
            'bonus_malus' => 'decimal:2',
            'ww_fees' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Car, $this>
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
