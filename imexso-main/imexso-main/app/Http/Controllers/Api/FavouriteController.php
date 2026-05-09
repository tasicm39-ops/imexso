<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\FavouriteRequest;
use App\Http\Resources\FavouriteResource;
use App\Models\Favourite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FavouriteController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $favourites = Favourite::query()
            ->where('user_id', $request->user()->id)
            ->with(['car.photos'])
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return FavouriteResource::collection($favourites);
    }

    /**
     * Toggle favourite: creates if not exists, deletes if exists.
     */
    public function store(FavouriteRequest $request): JsonResponse
    {
        $userId = $request->user()->id;
        $carId = $request->validated('car_id');

        $existing = Favourite::query()
            ->where('user_id', $userId)
            ->where('car_id', $carId)
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json(['favourited' => false], 200);
        }

        $favourite = Favourite::query()->create([
            'user_id' => $userId,
            'car_id' => $carId,
        ]);

        return (new FavouriteResource($favourite))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Request $request, int $carId): JsonResponse
    {
        $deleted = Favourite::query()
            ->where('user_id', $request->user()->id)
            ->where('car_id', $carId)
            ->delete();

        if ($deleted === 0) {
            return response()->json(['message' => 'Favourite not found.'], 404);
        }

        return response()->json(['favourited' => false], 200);
    }

    /**
     * Return a list of car IDs the current user has favourited.
     */
    public function carIds(Request $request): JsonResponse
    {
        $ids = Favourite::query()
            ->where('user_id', $request->user()->id)
            ->pluck('car_id');

        return response()->json(['car_ids' => $ids]);
    }
}
