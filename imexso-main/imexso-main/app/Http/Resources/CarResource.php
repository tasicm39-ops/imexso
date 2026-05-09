<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
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
            'make' => $this->make,
            'model' => $this->model,
            'trim_level' => $this->trim_level,
            'fuel_type' => $this->fuel_type,
            'engine_displacement' => $this->engine_displacement,
            'horsepower' => $this->horsepower,
            'engine_code' => $this->engine_code,
            'weight' => $this->weight,
            'manufacturing_year' => $this->manufacturing_year,
            'gearbox' => $this->gearbox,
            'gearbox_code' => $this->gearbox_code,
            'color' => $this->color,
            'color_code' => $this->color_code,
            'mileage' => $this->mileage,
            'co2' => $this->co2,
            'co2_wltp' => $this->co2_wltp,
            'wltp_electric_range' => $this->wltp_electric_range,
            'euro_standard' => $this->euro_standard,
            'professional_price' => $this->professional_price,
            'vat_type' => $this->vat_type,
            'status' => $this->status,
            'registration_date' => $this->registration_date?->format('Y-m-d'),
            'retention_date' => $this->retention_date?->format('Y-m-d'),
            'warranty_start_date' => $this->warranty_start_date?->format('Y-m-d'),
            'doors' => $this->doors,
            'category' => $this->category,
            'catalogue_base_price_excl_vat' => $this->catalogue_base_price_excl_vat,
            'catalogue_total_price_excl_vat' => $this->catalogue_total_price_excl_vat,
            'catalogue_price_incl_vat' => $this->catalogue_price_incl_vat,
            'used_car_fees' => $this->used_car_fees,
            'used_car_fees_detail' => $this->used_car_fees_detail,
            'condition_type' => $this->condition_type,
            'france_discount' => $this->france_discount,
            'catalogue_model_name' => $this->catalogue_model_name,
            'is_clearance' => $this->is_clearance,
            'argus_price' => $this->argus_price,
            'previous_price' => $this->previous_price,
            'user_type' => $this->user_type,
            'is_new' => $this->is_new,
            'catalogue_remark' => $this->catalogue_remark,
            'creation_date' => $this->creation_date?->format('Y-m-d'),
            'private_price_incl_vat' => $this->private_price_incl_vat,
            'publication_codes' => $this->publication_codes,
            'tags' => $this->tags,
            'main_equipment_codes' => $this->main_equipment_codes,
            'carlab' => $this->carlab,
            'carpass_url' => $this->carpass_url,
            'ecological_penalty' => $this->ecological_penalty,
            'vehicle_condition' => $this->vehicle_condition,
            'auction_data' => $this->auction_data,
            'stock_level' => $this->stock_level,
            'okcars' => $this->okcars,
            'ecarlux' => $this->ecarlux,
            'publish_platforms' => $this->publish_platforms,
            'excluded_countries' => $this->excluded_countries,
            'supplementary_equipment' => $this->supplementary_equipment,
            'standard_equipment' => $this->standard_equipment,
            'sync_status' => $this->sync_status,
            'last_synced_at' => $this->last_synced_at?->toIso8601String(),
            'photos' => CarPhotoResource::collection($this->whenLoaded('photos')),
            'options' => CarOptionResource::collection($this->whenLoaded('options')),
            'marketing' => $this->when(
                $this->relationLoaded('marketing') && $this->marketing !== null,
                fn () => new CarMarketingResource($this->marketing),
            ),
            'cart_users_count' => $this->when(
                isset($this->resource->cart_users_count),
                fn (): int => (int) $this->resource->cart_users_count,
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
