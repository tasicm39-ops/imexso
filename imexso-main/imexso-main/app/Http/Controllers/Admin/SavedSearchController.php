<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SavedSearchController extends Controller
{
    public function index(Request $request): Response
    {
        $query = SavedSearch::query()
            ->with('user')
            ->orderByDesc('created_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $users = User::query()
            ->whereHas('savedSearches')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('admin/saved-searches/index', [
            'savedSearches' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['user_id', 'search']),
            'users' => $users,
        ]);
    }
}
