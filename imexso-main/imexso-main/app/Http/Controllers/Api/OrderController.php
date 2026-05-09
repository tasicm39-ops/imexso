<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OrderStoreRequest;
use App\Models\Car;
use App\Models\CarCartItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(OrderStoreRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $clientId = $user->legacy_client_id;

        if ($clientId === null || $clientId === '') {
            return response()->json([
                'message' => 'You need a client ID to place an order. For your first purchase, please contact a seller.',
                'requires_client_id' => true,
            ], 422);
        }

        $carIds = $request->input('car_ids');
        $notes = $request->input('notes');

        $cars = Car::query()->whereIn('id', $carIds)->get();

        $unavailable = $cars->filter(fn (Car $car): bool => $car->sync_status !== 'active');

        if ($unavailable->isNotEmpty()) {
            $refs = $unavailable->pluck('id_produit')->implode(', ');

            return response()->json([
                'message' => "The following vehicles are no longer available: {$refs}",
                'unavailable_references' => $unavailable->pluck('id_produit')->values(),
            ], 422);
        }

        $orders = DB::transaction(function () use ($cars, $user, $clientId, $notes): array {
            $createdOrders = [];

            foreach ($cars as $car) {
                $createdOrders[] = Order::query()->create([
                    'user_id' => $user->id,
                    'car_id' => $car->id,
                    'client_id' => $clientId,
                    'car_reference' => $car->id_produit,
                    'status' => 'confirmed',
                    'notes' => $notes,
                    'confirmed_at' => now(),
                ]);

                $car->update(['sync_status' => 'sold']);

                CarCartItem::query()
                    ->where('car_id', $car->id)
                    ->delete();
            }

            return $createdOrders;
        });

        return response()->json([
            'message' => 'Order confirmed successfully.',
            'order_count' => count($orders),
            'references' => collect($orders)->pluck('car_reference')->values(),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->with(['car' => fn ($q) => $q->with('photos')])
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json($orders);
    }
}
