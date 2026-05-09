<?php

namespace App\Services;

use App\Models\CarCartItem;
use Illuminate\Support\Facades\DB;

final class CarCartCountService
{
    /**
     * Distinct users who currently have this car in their cart, only while the car is still available (active inventory).
     */
    public function totalUsersWithCarInCart(int $carId): int
    {
        return (int) DB::table('car_cart_items')
            ->join('cars', 'cars.id', '=', 'car_cart_items.car_id')
            ->where('car_cart_items.car_id', $carId)
            ->where('cars.sync_status', 'active')
            ->selectRaw('count(distinct car_cart_items.user_id) as c')
            ->value('c');
    }

    /**
     * How many *other* users (excluding the viewer) have this car in their cart — for public messaging.
     */
    public function otherUsersWithCarInCart(?int $viewerUserId, int $carId): int
    {
        $total = $this->totalUsersWithCarInCart($carId);

        if ($viewerUserId === null) {
            return $total;
        }

        $viewerHasCart = CarCartItem::query()
            ->where('user_id', $viewerUserId)
            ->where('car_id', $carId)
            ->whereRelation('car', 'sync_status', 'active')
            ->exists();

        return $viewerHasCart ? max(0, $total - 1) : $total;
    }
}
