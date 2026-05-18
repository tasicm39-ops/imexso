<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarHistory extends Model
{
    protected $table = 'car_history';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'car_id',
        'status',
        'buyer_info',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'buyer_info' => 'array',
            'created_at' => 'datetime',
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
