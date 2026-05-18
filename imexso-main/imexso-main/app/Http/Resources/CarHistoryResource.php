<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarHistoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'car_id' => $this->car_id,
            'status' => $this->status,
            'buyer_info' => $this->buyer_info,
            'created_at' => $this->created_at?->toIso8601String(),
            'car' => $this->whenLoaded('car', fn (): array => [
                'id' => $this->car->id,
                'id_produit' => $this->car->id_produit,
                'make' => $this->car->make,
                'model' => $this->car->model,
                'stock_status' => $this->car->stock_status,
                'sync_status' => $this->car->sync_status,
            ]),
        ];
    }
}
