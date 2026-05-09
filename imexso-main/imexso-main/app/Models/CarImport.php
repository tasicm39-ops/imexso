<?php

namespace App\Models;

use Database\Factories\CarImportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarImport extends Model
{
    /** @use HasFactory<CarImportFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'filename',
        'total_items_in_xml',
        'new_count',
        'updated_count',
        'sold_count',
        'unchanged_count',
        'status',
        'error_message',
        'started_at',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_items_in_xml' => 'integer',
            'new_count' => 'integer',
            'updated_count' => 'integer',
            'sold_count' => 'integer',
            'unchanged_count' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
