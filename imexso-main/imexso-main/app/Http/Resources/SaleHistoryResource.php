<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleHistoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_produit' => $this->id_produit,
            'vin' => null,
            'condition_type' => $this->condition_type,
            'make' => $this->make,
            'model' => $this->model,
            'trim_level' => $this->trim_level,
            'radio_code' => $this->radio_code,
            'color' => $this->color,
            'location' => $this->location,
            'price' => $this->price,
            'tax_type' => $this->tax_type,
            'client_id' => $this->client_id,
            'status' => $this->status,
            'order_date' => $this->order_date?->format('Y-m-d'),
            'documents' => $this->documents,
            'invoices' => $this->invoices,
            'to_unblock' => $this->to_unblock,
            'assignment_count' => $this->assignment_count,
            'assignment_1' => $this->assignment_1,
            'assignment_2' => $this->assignment_2,
            'days_available' => $this->days_available,
            'lot_number' => $this->lot_number,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
