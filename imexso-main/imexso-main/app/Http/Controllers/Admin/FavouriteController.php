<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Favourite;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FavouriteController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Favourite::query()
            ->with(['user', 'car.photos'])
            ->orderByDesc('created_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        $users = User::query()
            ->whereHas('favourites')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('admin/favourites/index', [
            'favourites' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['user_id', 'search']),
            'users' => $users,
        ]);
    }
}
