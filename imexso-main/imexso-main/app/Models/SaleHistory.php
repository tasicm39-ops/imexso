<?php

namespace App\Models;

use Database\Factories\SaleHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleHistory extends Model
{
    /** @use HasFactory<SaleHistoryFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id_produit',
        'vin',
        'condition_type',
        'make',
        'model',
        'trim_level',
        'radio_code',
        'color',
        'location',
        'price',
        'tax_type',
        'client_id',
        'status',
        'order_date',
        'documents',
        'invoices',
        'to_unblock',
        'assignment_count',
        'assignment_1',
        'assignment_2',
        'days_available',
        'lot_number',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'assignment_count' => 'integer',
            'days_available' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id', 'legacy_client_id');
    }
}
