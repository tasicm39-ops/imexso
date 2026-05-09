<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CarIndexRequest;
use App\Http\Resources\CarResource;
use App\Models\ApiClient;
use App\Models\Car;
use App\Models\CarCartItem;
use App\Models\Favourite;
use App\Models\SegmentEvent;
use App\Models\User;
use App\Services\CarCartCountService;
use App\Services\CarPublicListingService;
use App\Support\CarColorCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CarController extends Controller
{
    public function __construct(
        private CarPublicListingService $carPublicListingService,
        private CarCartCountService $carCartCountService,
    ) {}

    public function index(CarIndexRequest $request): AnonymousResourceCollection
    {
        $query = Car::query();

        $this->applyFilters($request, $query);
        $this->applyCountryExclusion($request, $query);

        $totalAll = (clone $query)->count();

        $query = $this->carPublicListingService->queryCanonicalCars($query);

        $query->with(['photos', 'options', 'marketing']);
        $query->withCount([
            'carCartItems as cart_users_count' => function (Builder $q): void {
                $q->whereHas('car', fn (Builder $cq) => $cq->where('sync_status', 'active'));
            },
        ]);
        $this->applySorting($request, $query);

        $paginated = $query->paginate($request->integer('per_page', 15));

        return CarResource::collection($paginated)->additional([
            'total_all' => $totalAll,
        ]);
    }

    public function show(Request $request, Car $car): JsonResponse
    {
        $car->load(['photos', 'options', 'marketing']);

        $viewerCount = max(1, SegmentEvent::query()
            ->where('event_type', 'view_car')
            ->where('payload->car_id', $car->id)
            ->where('created_at', '>=', now()->subMinutes(30))
            ->distinct('user_id')
            ->count('user_id'));

        $isFavourited = false;
        $isInCart = false;
        if ($request->user()) {
            $isFavourited = Favourite::query()
                ->where('user_id', $request->user()->id)
                ->where('car_id', $car->id)
                ->exists();
            $isInCart = CarCartItem::query()
                ->where('user_id', $request->user()->id)
                ->where('car_id', $car->id)
                ->exists();
        }

        $resource = (new CarResource($car))->toArray($request);
        $resource['viewer_count'] = $viewerCount;
        $resource['is_favourited'] = $isFavourited;
        $resource['is_in_cart'] = $isInCart;
        $resource['cart_other_users_count'] = $this->carCartCountService->otherUsersWithCarInCart(
            $request->user()?->id,
            $car->id,
        );

        return response()->json(['data' => $resource]);
    }

    public function filters(Request $request): JsonResponse
    {
        $baseQuery = Car::query()->where('sync_status', 'active');

        $this->applyCountryExclusion($request, $baseQuery);

        $modelsByMake = (clone $baseQuery)
            ->whereNotNull('make')
            ->whereNotNull('model')
            ->select('make', 'model')
            ->distinct()
            ->get()
            ->groupBy('make')
            ->map(fn ($items) => $items->pluck('model')->sort()->values())
            ->sortKeys();

        $rawGearboxes = (clone $baseQuery)->whereNotNull('gearbox')->distinct()->pluck('gearbox')->sort()->values();
        $transmissionGroups = $this->groupTransmissions($rawGearboxes);

        $rawColors = (clone $baseQuery)->whereNotNull('color')->distinct()->pluck('color')->sort()->values();
        $colorGroups = $this->buildColorFilterGroups($baseQuery);

        return response()->json([
            'makes' => (clone $baseQuery)->whereNotNull('make')->distinct()->pluck('make')->sort()->values(),
            'models' => (clone $baseQuery)->whereNotNull('model')->distinct()->pluck('model')->sort()->values(),
            'models_by_make' => $modelsByMake,
            'fuel_types' => (clone $baseQuery)->whereNotNull('fuel_type')->distinct()->pluck('fuel_type')->sort()->values(),
            'gearboxes' => $rawGearboxes,
            'transmission_groups' => $transmissionGroups,
            'colors' => $rawColors,
            'color_groups' => $colorGroups,
            'categories' => (clone $baseQuery)->whereNotNull('category')->distinct()->pluck('category')->sort()->values(),
            'condition_types' => (clone $baseQuery)->whereNotNull('condition_type')->distinct()->pluck('condition_type')->sort()->values(),
            'ranges' => [
                'horsepower' => [
                    'min' => (clone $baseQuery)->min('horsepower') ?? 0,
                    'max' => (clone $baseQuery)->max('horsepower') ?? 0,
                ],
                'price' => [
                    'min' => (clone $baseQuery)->min('professional_price') ?? 0,
                    'max' => (clone $baseQuery)->max('professional_price') ?? 0,
                ],
                'mileage' => [
                    'min' => (clone $baseQuery)->min('mileage') ?? 0,
                    'max' => (clone $baseQuery)->max('mileage') ?? 0,
                ],
                'year' => [
                    'min' => (clone $baseQuery)->min('manufacturing_year') ?? 0,
                    'max' => (clone $baseQuery)->max('manufacturing_year') ?? 0,
                ],
            ],
        ]);
    }

    /**
     * @param  Collection<int, string>  $gearboxes
     * @return array<string, list<string>>
     */
    private function groupTransmissions($gearboxes): array
    {
        $manualPatterns = ['5v', '6v', '5 v', '6 v'];

        $manual = [];
        $automatic = [];

        foreach ($gearboxes as $gearbox) {
            $normalized = mb_strtolower(trim($gearbox));
            if (in_array($normalized, array_map('mb_strtolower', $manualPatterns), true)) {
                $manual[] = $gearbox;
            } else {
                $automatic[] = $gearbox;
            }
        }

        $groups = [];
        if (count($manual) > 0) {
            $groups['Manual'] = $manual;
        }
        if (count($automatic) > 0) {
            $groups['Automatic'] = $automatic;
        }

        return $groups;
    }

    /**
     * Builds color filter groups from {@code color_code} (XML {@code code_couleur}) with legacy rows that only have a free-text {@code color}.
     *
     * @return list<array{key: string, codes: list<string>, legacy_colors: list<string>, hex: string}>
     */
    private function buildColorFilterGroups(Builder $baseQuery): array
    {
        $pairs = (clone $baseQuery)
            ->where(function (Builder $q) {
                $q->where(function (Builder $q2) {
                    $q2->whereNotNull('color_code')->where('color_code', '!=', '');
                })->orWhereNotNull('color');
            })
            ->select(['color_code', 'color'])
            ->distinct()
            ->get();

        /** @var array<string, array{codes: array<string, true>, legacy_colors: array<string, true>}> $grouped */
        $grouped = [];

        foreach ($pairs as $row) {
            $key = CarColorCode::resolveGroupKey($row->color_code, $row->color);
            if (! isset($grouped[$key])) {
                $grouped[$key] = ['codes' => [], 'legacy_colors' => []];
            }

            $code = $row->color_code !== null ? trim((string) $row->color_code) : '';

            if ($code !== '') {
                $grouped[$key]['codes'][$code] = true;
            }

            if (filled($row->color) && $code === '') {
                $grouped[$key]['legacy_colors'][$row->color] = true;
            }
        }

        $ordered = [];

        foreach (CarColorCode::orderedGroupKeys() as $key) {
            if (! isset($grouped[$key])) {
                continue;
            }

            $entry = $this->colorGroupEntryFromBuckets($key, $grouped[$key]);
            if ($entry !== null) {
                $ordered[] = $entry;
            }
            unset($grouped[$key]);
        }

        foreach ($grouped as $key => $buckets) {
            $entry = $this->colorGroupEntryFromBuckets($key, $buckets);
            if ($entry !== null) {
                $ordered[] = $entry;
            }
        }

        return $ordered;
    }

    /**
     * @param  array{codes: array<string, true>, legacy_colors: array<string, true>}  $buckets
     * @return array{key: string, codes: list<string>, legacy_colors: list<string>, hex: string}|null
     */
    private function colorGroupEntryFromBuckets(string $key, array $buckets): ?array
    {
        $codes = array_map('strval', array_keys($buckets['codes']));
        $legacy = array_keys($buckets['legacy_colors']);
        sort($codes, SORT_NATURAL);
        sort($legacy, SORT_NATURAL);

        if ($codes === [] && $legacy === []) {
            return null;
        }

        return [
            'key' => $key,
            'codes' => $codes,
            'legacy_colors' => $legacy,
            'hex' => CarColorCode::hexForGroup($key),
        ];
    }

    /**
     * @param  Builder<Car>  $query
     */
    private function applyFilters(CarIndexRequest $request, Builder $query): void
    {
        if ($request->filled('make')) {
            $query->where('make', $request->input('make'));
        }

        if ($request->filled('model')) {
            $query->where('model', $request->input('model'));
        }

        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', $request->input('fuel_type'));
        }

        if ($request->filled('condition_type')) {
            $query->where('condition_type', $request->input('condition_type'));
        }

        if ($request->filled('sync_status')) {
            $query->where('sync_status', $request->input('sync_status'));
        } else {
            $now = now();
            $driver = DB::connection()->getDriverName();
            $query->where(function (Builder $q) use ($now, $driver): void {
                $q->where('sync_status', 'active')
                    ->orWhere(function (Builder $soldQuery) use ($now, $driver): void {
                        $soldQuery->where('sync_status', 'sold')
                            ->whereHas('marketing', function (Builder $marketingQuery) use ($now, $driver): void {
                                $marketingQuery->where('sold_enabled', true)
                                    ->whereNotNull('sold_marked_at');

                                if ($driver === 'sqlite') {
                                    $marketingQuery->whereRaw(
                                        "datetime(sold_marked_at, '+' || sold_visible_days || ' days') >= ?",
                                        [$now->toDateTimeString()]
                                    );
                                } else {
                                    $marketingQuery->whereRaw(
                                        'DATE_ADD(sold_marked_at, INTERVAL sold_visible_days DAY) >= ?',
                                        [$now]
                                    );
                                }
                            });
                    });
            });
        }

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->input('user_type'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('gearbox')) {
            $gearboxValues = explode(',', $request->input('gearbox'));
            if (count($gearboxValues) === 1) {
                $query->where('gearbox', $gearboxValues[0]);
            } else {
                $query->whereIn('gearbox', $gearboxValues);
            }
        }

        if ($request->filled('color')) {
            $parts = array_values(array_filter(array_map('trim', explode(',', $request->input('color')))));
            $codes = array_values(array_filter($parts, fn (string $p): bool => ctype_digit($p)));
            $colorNames = array_values(array_filter($parts, fn (string $p): bool => ! ctype_digit($p)));

            if ($codes !== [] && $colorNames !== []) {
                $query->where(function (Builder $q) use ($codes, $colorNames) {
                    $q->whereIn('color_code', $codes)
                        ->orWhere(function (Builder $q2) use ($colorNames) {
                            $q2->whereNull('color_code')->whereIn('color', $colorNames);
                        });
                });
            } elseif ($codes !== []) {
                $query->whereIn('color_code', $codes);
            } elseif (count($colorNames) === 1) {
                $query->where('color', $colorNames[0]);
            } else {
                $query->whereIn('color', $colorNames);
            }
        }

        if ($request->filled('horsepower_min')) {
            $query->where('horsepower', '>=', $request->integer('horsepower_min'));
        }

        if ($request->filled('horsepower_max')) {
            $query->where('horsepower', '<=', $request->integer('horsepower_max'));
        }

        if ($request->filled('price_min')) {
            $query->where('professional_price', '>=', $request->float('price_min'));
        }

        if ($request->filled('price_max')) {
            $query->where('professional_price', '<=', $request->float('price_max'));
        }

        if ($request->filled('mileage_min')) {
            $query->where('mileage', '>=', $request->integer('mileage_min'));
        }

        if ($request->filled('mileage_max')) {
            $query->where('mileage', '<=', $request->integer('mileage_max'));
        }

        if ($request->filled('year_min')) {
            $query->where('manufacturing_year', '>=', $request->integer('year_min'));
        }

        if ($request->filled('year_max')) {
            $query->where('manufacturing_year', '<=', $request->integer('year_max'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function (Builder $q) use ($search) {
                $q->where('make', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('trim_level', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('is_new')) {
            $query->where('is_new', true);
        }

        if ($request->boolean('has_promotion')) {
            $query->whereHas('marketing', function (Builder $q): void {
                $q->where('is_active', true)->where('promotion_enabled', true);
            });
        }
    }

    /**
     * @param  Builder<Car>  $query
     */
    private function applySorting(CarIndexRequest $request, Builder $query): void
    {
        $sort = $request->input('sort');

        match ($sort) {
            'price_asc' => $query->orderBy('professional_price', 'asc'),
            'price_desc' => $query->orderBy('professional_price', 'desc'),
            'year_desc' => $query->orderBy('manufacturing_year', 'desc'),
            'mileage_asc' => $query->orderBy('mileage', 'asc'),
            default => $query->orderBy('created_at', 'desc')->orderBy('id', 'desc'),
        };
    }

    /**
     * @param  Builder<Car>  $query
     */
    private function applyCountryExclusion(Request $request, Builder $query): void
    {
        $country = $request->input('country');

        if ($country === null) {
            $authenticatable = $request->user();

            if ($authenticatable instanceof User) {
                $country = $authenticatable->country;
            } elseif ($authenticatable instanceof ApiClient) {
                $country = $authenticatable->country;
            }
        }

        if ($country === null || $country === '') {
            return;
        }

        $query->where(function (Builder $q) use ($country) {
            $q->whereNull('excluded_countries')
                ->orWhere('excluded_countries', 'like', '[]')
                ->orWhereJsonDoesntContain('excluded_countries', $country);
        });
    }
}
