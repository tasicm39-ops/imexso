<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'legacy_user_id',
        'legacy_client_id',
        'phone',
        'language',
        'user_type',
        'company_name',
        'legal_form',
        'address',
        'postal_code',
        'city',
        'country',
        'fax',
        'vat_number',
        'is_active',
        'is_validated',
        'is_seller',
        'is_admin',
        'is_professional',
        'activity',
        'website',
        'last_contact_date',
        'last_order_date',
        'new_car_seller',
        'used_car_seller',
        'logo',
        'slogan',
        'car_volume',
        'revenue',
        'activity_follow_up',
        'vehicles_searched',
        'comments',
        'old_email',
        'gdpr_accepted_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    public function segmentEvents(): HasMany
    {
        return $this->hasMany(SegmentEvent::class);
    }

    public function carImports(): HasMany
    {
        return $this->hasMany(CarImport::class);
    }

    public function favourites(): HasMany
    {
        return $this->hasMany(Favourite::class);
    }

    public function carCartItems(): HasMany
    {
        return $this->hasMany(CarCartItem::class);
    }

    public function savedSearches(): HasMany
    {
        return $this->hasMany(SavedSearch::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_active' => 'boolean',
            'is_validated' => 'boolean',
            'is_seller' => 'boolean',
            'is_admin' => 'boolean',
            'is_professional' => 'boolean',
            'last_contact_date' => 'date',
            'last_order_date' => 'date',
            'car_volume' => 'integer',
            'revenue' => 'decimal:2',
            'gdpr_accepted_at' => 'datetime',
        ];
    }
}
