<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\SegmentEvent;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('admin/dashboard', [
            'stats' => [
                'total_users' => User::query()->count(),
                'pending_users' => User::query()->where('is_validated', false)->count(),
                'active_cars' => Car::query()->where('sync_status', 'active')->count(),
                'sold_cars' => Car::query()->where('sync_status', 'sold')->count(),
                'recent_events' => SegmentEvent::query()->where('created_at', '>=', now()->subDay())->count(),
            ],
            'recentCars' => Inertia::defer(
                fn () => Car::query()
                    ->with('photos')
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->get(),
                'tables',
            ),
            'recentEvents' => Inertia::defer(
                fn () => SegmentEvent::query()
                    ->with('user')
                    ->orderByDesc('created_at')
                    ->limit(10)
                    ->get(),
                'tables',
            ),
        ]);
    }
}
