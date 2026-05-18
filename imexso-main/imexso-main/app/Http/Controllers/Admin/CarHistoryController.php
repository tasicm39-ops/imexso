<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CarHistoryStatus;
use App\Http\Controllers\Controller;
use App\Models\CarHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CarHistoryController extends Controller
{
    public function index(Request $request): Response
    {
        $query = CarHistory::query()
            ->with([
                'car.photos' => fn ($photoQuery) => $photoQuery->orderBy('position')->limit(1),
            ])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('car_id')) {
            $query->where('car_id', $request->integer('car_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('car_id', 'like', "%{$search}%")
                    ->orWhereHas('car', function ($carQuery) use ($search) {
                        $carQuery->where('id_produit', 'like', "%{$search}%")
                            ->orWhere('make', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%")
                            ->orWhere('vin', 'like', "%{$search}%");
                    });
            });
        }

        return Inertia::render('admin/cars/history/index', [
            'histories' => $query->paginate(25)->withQueryString(),
            'filters' => $request->only(['status', 'search', 'car_id']),
            'statuses' => array_map(
                fn (CarHistoryStatus $status) => $status->value,
                CarHistoryStatus::cases(),
            ),
        ]);
    }
}
