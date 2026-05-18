<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CarImportRequest;
use App\Http\Requests\Admin\CarMarketingRequest;
use App\Http\Resources\CarHistoryResource;
use App\Models\Car;
use App\Models\CarHistory;
use App\Models\CarMarketing;
use App\Models\Offer;
use App\Models\SegmentEvent;
use App\Services\CarXmlImportService;
use App\Services\VenduXmlImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CarController extends Controller
{
    public function index(Request $request): Response
    {
        $syncStatus = $request->input('sync_status', 'active');

        $query = Car::query()->with(['photos', 'marketing']);

        if ($syncStatus !== 'all') {
            $query->where('sync_status', $syncStatus);
        }

        if ($request->filled('make')) {
            $query->where('make', $request->input('make'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('make', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('trim_level', 'like', "%{$search}%")
                    ->orWhere('vin', 'like', "%{$search}%")
                    ->orWhere('id_produit', 'like', "%{$search}%");
            });
        }

        $makesQuery = Car::query()
            ->whereNotNull('make')
            ->where('make', '!=', 'UNKNOWN');

        if ($syncStatus !== 'all') {
            $makesQuery->where('sync_status', $syncStatus);
        }

        $makes = $makesQuery
            ->distinct()
            ->pluck('make')
            ->sort()
            ->values();

        $cars = $query
            ->orderByRaw("CASE WHEN sync_status = 'active' THEN 0 ELSE 1 END")
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $carIds = $cars->getCollection()->pluck('id')->all();
        $statsData = $this->buildCarStats($carIds);

        return Inertia::render('admin/cars/index', [
            'cars' => $cars,
            'filters' => [
                'sync_status' => $syncStatus,
                'make' => $request->input('make'),
                'search' => $request->input('search'),
            ],
            'makes' => $makes,
            'counts' => [
                'active' => Car::query()->where('sync_status', 'active')->count(),
                'sold' => Car::query()->where('sync_status', 'sold')->count(),
                'total' => Car::query()->count(),
            ],
            'offerStats' => $statsData['offers'],
            'viewStats' => $statsData['views'],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/cars/import');
    }

    public function store(CarImportRequest $request, CarXmlImportService $importService): RedirectResponse
    {
        $file = $request->file('file');
        $xmlContent = file_get_contents($file->getRealPath());

        if ($xmlContent === false || $xmlContent === '') {
            return back()->with('error', 'Failed to read the uploaded file.');
        }

        try {
            $import = $importService->import([
                'content' => $xmlContent,
                'filename' => $file->getClientOriginalName(),
                'user_id' => $request->user()?->id,
            ]);

            return redirect()
                ->route('admin.cars.index')
                ->with('success', sprintf(
                    'Import completed: %d new, %d updated, %d sold, %d unchanged out of %d items.',
                    $import->new_count,
                    $import->updated_count,
                    $import->sold_count,
                    $import->unchanged_count,
                    $import->total_items_in_xml,
                ));
        } catch (ValidationException $e) {
            return back()->with('error', 'Validation failed for one or more items in the XML.')->withErrors($e->errors());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function storeVendu(VenduXmlImportService $importService): RedirectResponse
    {
        try {
            $stats = $importService->import();

            return redirect()
                ->route('admin.cars.index')
                ->with('success', sprintf(
                    'Vendu import completed: %d processed, %d cars updated, %d archive cars created, %d history created, %d history updated, %d skipped, %d missing.',
                    $stats['processed'],
                    $stats['updated'],
                    $stats['cars_created'],
                    $stats['history_created'],
                    $stats['history_updated'],
                    $stats['skipped'],
                    $stats['missing_cars'],
                ));
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function marketing(Car $car): Response
    {
        $car->load('marketing');

        $history = CarHistory::query()
            ->where('car_id', $car->id)
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('admin/cars/marketing', [
            'car' => $car,
            'history' => [
                'data' => $history
                    ->map(fn (CarHistory $entry) => (new CarHistoryResource($entry))->resolve())
                    ->values()
                    ->all(),
            ],
            'marketing' => $car->marketing ?? new CarMarketing([
                'limited_stock_enabled' => false,
                'limited_stock_count' => null,
                'new_price_enabled' => false,
                'new_price_amount' => null,
                'promotion_enabled' => false,
                'promotion_label' => null,
                'badge_text' => null,
                'sold_enabled' => false,
                'sold_visible_days' => 5,
                'sold_marked_at' => null,
                'is_active' => true,
            ]),
        ]);
    }

    public function updateMarketing(CarMarketingRequest $request, Car $car): RedirectResponse
    {
        $existingMarketing = $car->marketing;
        $validated = $request->validated();

        if (($validated['sold_enabled'] ?? false) === true) {
            $validated['sold_marked_at'] = $existingMarketing?->sold_enabled
                ? ($existingMarketing->sold_marked_at ?? now())
                : now();
        } else {
            $validated['sold_marked_at'] = null;
        }

        $car->marketing()->updateOrCreate(
            ['car_id' => $car->id],
            $validated,
        );

        return redirect()
            ->route('admin.cars.marketing', $car)
            ->with('success', 'Marketing settings updated successfully.');
    }

    /**
     * @param  list<int>  $carIds
     * @return array{offers: array<int, list<array{date: string, count: int}>>, views: array<int, list<array{date: string, count: int}>>}
     */
    private function buildCarStats(array $carIds): array
    {
        if ($carIds === []) {
            return ['offers' => [], 'views' => []];
        }

        $since = Carbon::now()->subDays(4)->startOfDay();
        $dates = collect(range(0, 4))->map(fn (int $i) => Carbon::now()->subDays(4 - $i)->toDateString());

        $offerRows = Offer::query()
            ->whereIn('car_id', $carIds)
            ->where('created_at', '>=', $since)
            ->selectRaw('car_id, DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('car_id', 'date')
            ->get();

        $viewRows = SegmentEvent::query()
            ->where('event_type', 'view_car')
            ->whereIn('payload->car_id', $carIds)
            ->where('created_at', '>=', $since)
            ->selectRaw("json_extract(payload, '$.car_id') as car_id_raw, DATE(created_at) as date, COUNT(*) as count")
            ->groupBy('car_id_raw', 'date')
            ->get()
            ->each(fn ($row) => $row->car_id = (int) trim((string) $row->car_id_raw, '"'));

        $offerMap = $offerRows->groupBy('car_id');
        $viewMap = $viewRows->groupBy('car_id');

        $fillDays = function ($grouped, int $carId) use ($dates): array {
            $rows = $grouped->get($carId, collect())->keyBy('date');

            return $dates->map(fn (string $d) => [
                'date' => $d,
                'count' => (int) ($rows[$d]->count ?? 0),
            ])->all();
        };

        $offers = [];
        $views = [];

        foreach ($carIds as $id) {
            $offers[$id] = $fillDays($offerMap, $id);
            $views[$id] = $fillDays($viewMap, $id);
        }

        return ['offers' => $offers, 'views' => $views];
    }
}
