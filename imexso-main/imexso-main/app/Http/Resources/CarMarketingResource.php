<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarMarketingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'car_id' => $this->car_id,
            'limited_stock_enabled' => $this->limited_stock_enabled,
            'limited_stock_count' => $this->limited_stock_count,
            'new_price_enabled' => $this->new_price_enabled,
            'new_price_amount' => $this->new_price_amount,
            'promotion_enabled' => $this->promotion_enabled,
            'promotion_label' => $this->promotion_label,
            'badge_text' => $this->badge_text,
            'sold_enabled' => $this->sold_enabled,
            'sold_visible_days' => $this->sold_visible_days,
            'sold_marked_at' => $this->sold_marked_at?->toIso8601String(),
            'sold_expires_at' => $this->sold_marked_at?->copy()->addDays((int) $this->sold_visible_days)->toIso8601String(),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
