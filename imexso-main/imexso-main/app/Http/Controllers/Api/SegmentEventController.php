<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SegmentEventRequest;
use App\Http\Resources\SegmentEventResource;
use App\Models\SegmentEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SegmentEventController extends Controller
{
    public function store(SegmentEventRequest $request): JsonResponse
    {
        $event = SegmentEvent::query()->create([
            'user_id' => $request->user()->id,
            'event_type' => $request->validated('event_type'),
            'payload' => $request->validated('payload'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return (new SegmentEventResource($event))
            ->response()
            ->setStatusCode(201);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = SegmentEvent::query()->orderByDesc('created_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->input('event_type'));
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to'));
        }

        return SegmentEventResource::collection($query->paginate(
            $request->integer('per_page', 15),
        ));
    }
}
