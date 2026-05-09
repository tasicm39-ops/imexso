<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CarCartStoreRequest;
use App\Http\Resources\CarCartItemResource;
use App\Models\Car;
use App\Models\CarCartItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CarCartController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $items = CarCartItem::query()
            ->where('user_id', $request->user()->id)
            ->with(['car' => fn ($query) => $query->with(['photos'])])
            ->orderByDesc('updated_at')
            ->paginate($request->integer('per_page', 20));

        return CarCartItemResource::collection($items);
    }

    public function carIds(Request $request): JsonResponse
    {
        $ids = CarCartItem::query()
            ->where('user_id', $request->user()->id)
            ->whereRelation('car', 'sync_status', 'active')
            ->pluck('car_id');

        return response()->json(['car_ids' => $ids]);
    }

    public function store(CarCartStoreRequest $request): JsonResponse
    {
        $car = Car::query()->findOrFail($request->integer('car_id'));

        if ($car->sync_status !== 'active') {
            return response()->json([
                'message' => 'This vehicle is no longer available for reservation.',
            ], 422);
        }

        $item = CarCartItem::query()->firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'car_id' => $car->id,
            ],
        );

        return response()->json([
            'in_cart' => true,
            'created' => $item->wasRecentlyCreated,
        ], $item->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(Request $request, Car $car): JsonResponse
    {
        $deleted = CarCartItem::query()
            ->where('user_id', $request->user()->id)
            ->where('car_id', $car->id)
            ->delete();

        if ($deleted === 0) {
            return response()->json([
                'in_cart' => false,
                'message' => 'This vehicle was not in your cart.',
            ], 404);
        }

        return response()->json(['in_cart' => false]);
    }
}
