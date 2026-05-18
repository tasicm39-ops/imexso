<?php

namespace App\Models;

use Database\Factories\CarFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Car extends Model
{
    /** @use HasFactory<CarFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'int';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'id_produit',
        'vin',
        'make',
        'model',
        'trim_level',
        'fuel_type',
        'engine_displacement',
        'horsepower',
        'engine_code',
        'weight',
        'manufacturing_year',
        'gearbox',
        'gearbox_code',
        'color',
        'color_code',
        'mileage',
        'co2',
        'co2_wltp',
        'wltp_electric_range',
        'euro_standard',
        'professional_price',
        'vat_type',
        'status',
        'registration_date',
        'retention_date',
        'warranty_start_date',
        'doors',
        'category',
        'catalogue_base_price_excl_vat',
        'catalogue_total_price_excl_vat',
        'catalogue_price_incl_vat',
        'used_car_fees',
        'used_car_fees_detail',
        'condition_type',
        'france_discount',
        'catalogue_model_name',
        'is_clearance',
        'argus_price',
        'previous_price',
        'user_type',
        'is_new',
        'catalogue_remark',
        'creation_date',
        'private_price_incl_vat',
        'publication_codes',
        'tags',
        'main_equipment_codes',
        'carlab',
        'carpass_url',
        'ecological_penalty',
        'vehicle_condition',
        'auction_data',
        'stock_level',
        'okcars',
        'ecarlux',
        'publish_platforms',
        'excluded_countries',
        'supplementary_equipment',
        'standard_equipment',
        'sync_status',
        'stock_status',
        'last_synced_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'engine_displacement' => 'integer',
            'horsepower' => 'integer',
            'weight' => 'integer',
            'manufacturing_year' => 'integer',
            'mileage' => 'integer',
            'co2' => 'integer',
            'co2_wltp' => 'integer',
            'wltp_electric_range' => 'integer',
            'professional_price' => 'decimal:2',
            'status' => 'array',
            'registration_date' => 'date',
            'retention_date' => 'date',
            'warranty_start_date' => 'date',
            'doors' => 'integer',
            'catalogue_base_price_excl_vat' => 'decimal:2',
            'catalogue_total_price_excl_vat' => 'decimal:2',
            'catalogue_price_incl_vat' => 'decimal:2',
            'used_car_fees' => 'decimal:2',
            'france_discount' => 'decimal:2',
            'is_clearance' => 'boolean',
            'argus_price' => 'decimal:2',
            'previous_price' => 'decimal:2',
            'is_new' => 'boolean',
            'creation_date' => 'date',
            'private_price_incl_vat' => 'decimal:2',
            'main_equipment_codes' => 'array',
            'carlab' => 'boolean',
            'ecological_penalty' => 'decimal:2',
            'auction_data' => 'array',
            'stock_level' => 'integer',
            'okcars' => 'boolean',
            'ecarlux' => 'boolean',
            'publish_platforms' => 'array',
            'excluded_countries' => 'array',
            'supplementary_equipment' => 'array',
            'standard_equipment' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<CarPhoto, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(CarPhoto::class)->orderBy('position');
    }

    /**
     * @return HasMany<CarOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(CarOption::class);
    }

    /**
     * @return HasOne<CarMarketing, $this>
     */
    public function marketing(): HasOne
    {
        return $this->hasOne(CarMarketing::class);
    }

    /**
     * @return HasMany<Offer, $this>
     */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * @return HasMany<Favourite, $this>
     */
    public function favourites(): HasMany
    {
        return $this->hasMany(Favourite::class);
    }

    /**
     * @return HasMany<CarCartItem, $this>
     */
    public function carCartItems(): HasMany
    {
        return $this->hasMany(CarCartItem::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany<CarHistory, $this>
     */
    public function history(): HasMany
    {
        return $this->hasMany(CarHistory::class)->orderByDesc('created_at');
    }

    /**
     * @param  Builder<Car>  $query
     * @return Builder<Car>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('sync_status', 'active');
    }

    /**
     * @param  Builder<Car>  $query
     * @return Builder<Car>
     */
    public function scopeSold(Builder $query): Builder
    {
        return $query->where('sync_status', 'sold');
    }

    public function markAvailable(): void
    {
        $this->forceFill([
            'stock_status' => 'AVAILABLE',
            'sync_status' => 'active',
        ])->save();
    }

    public function markSold(): void
    {
        $this->forceFill([
            'stock_status' => 'SOLD',
            'sync_status' => 'sold',
        ])->save();
    }
}
