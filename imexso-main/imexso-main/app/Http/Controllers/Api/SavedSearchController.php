<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SavedSearchRequest;
use App\Http\Resources\SavedSearchResource;
use App\Models\SavedSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SavedSearchController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $searches = SavedSearch::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return SavedSearchResource::collection($searches);
    }

    public function store(SavedSearchRequest $request): JsonResponse
    {
        $search = SavedSearch::query()->create([
            'user_id' => $request->user()->id,
            'name' => $request->validated('name'),
            'filters' => $request->validated('filters'),
        ]);

        return (new SavedSearchResource($search))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Request $request, SavedSearch $savedSearch): JsonResponse
    {
        if ($savedSearch->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $savedSearch->delete();

        return response()->json(['message' => 'Saved search deleted.'], 200);
    }
}
