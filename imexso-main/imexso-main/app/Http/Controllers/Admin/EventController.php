<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SegmentEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(Request $request): Response
    {
        $query = SegmentEvent::query()
            ->with('user:id,name,email,company_name,phone,country,legacy_client_id')
            ->orderByDesc('created_at');

        $this->applyFilters($request, $query);

        $perPage = $request->integer('per_page', 20);

        $eventTypes = SegmentEvent::query()
            ->distinct()
            ->pluck('event_type')
            ->sort()
            ->values();

        $payloadMakes = SegmentEvent::query()
            ->whereIn('event_type', ['view_car', 'filter'])
            ->whereNotNull('payload')
            ->get(['payload'])
            ->pluck('payload.make')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $payloadModels = SegmentEvent::query()
            ->whereIn('event_type', ['view_car', 'filter'])
            ->whereNotNull('payload')
            ->get(['payload'])
            ->pluck('payload.model')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $countries = SegmentEvent::query()
            ->whereHas('user', fn (Builder $q) => $q->whereNotNull('country')->where('country', '!=', ''))
            ->with('user:id,country')
            ->get()
            ->pluck('user.country')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return Inertia::render('admin/events/index', [
            'events' => $query->paginate($perPage)->withQueryString(),
            'filters' => $request->only([
                'event_type', 'search', 'date_from', 'date_to',
                'payload_make', 'payload_model', 'country', 'per_page',
            ]),
            'eventTypes' => $eventTypes,
            'payloadMakes' => $payloadMakes,
            'payloadModels' => $payloadModels,
            'countries' => $countries,
        ]);
    }

    /**
     * @param  Builder<SegmentEvent>  $query
     */
    private function applyFilters(Request $request, Builder $query): void
    {
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->input('event_type'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('legacy_client_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to').' 23:59:59');
        }

        if ($request->filled('payload_make')) {
            $make = $request->input('payload_make');
            $query->where(function (Builder $q) use ($make) {
                $q->whereJsonContains('payload->make', $make);
            });
        }

        if ($request->filled('payload_model')) {
            $model = $request->input('payload_model');
            $query->where(function (Builder $q) use ($model) {
                $q->whereJsonContains('payload->model', $model);
            });
        }

        if ($request->filled('country')) {
            $country = $request->input('country');
            $query->whereHas('user', function (Builder $q) use ($country) {
                $q->where('country', $country);
            });
        }
    }
}
