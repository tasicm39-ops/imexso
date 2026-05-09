<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::query();

        if ($request->filled('status')) {
            match ($request->input('status')) {
                'pending' => $query->where('is_validated', false),
                'validated' => $query->where('is_validated', true),
                'inactive' => $query->where('is_active', false),
                default => null,
            };
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('legacy_client_id', 'like', "%{$search}%");
            });
        }

        return Inertia::render('admin/users/index', [
            'users' => $query->orderByDesc('created_at')->paginate(20)->withQueryString(),
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function approve(User $user): RedirectResponse
    {
        $user->update([
            'is_validated' => true,
            'is_active' => true,
        ]);

        return back()->with('success', "User {$user->name} has been approved.");
    }

    public function reject(User $user): RedirectResponse
    {
        $user->update([
            'is_validated' => false,
            'is_active' => false,
        ]);

        return back()->with('success', "User {$user->name} has been rejected.");
    }

    public function toggleAdmin(User $user): RedirectResponse
    {
        $user->update([
            'is_admin' => ! $user->is_admin,
        ]);

        $status = $user->is_admin ? 'granted' : 'revoked';

        return back()->with('success', "Admin access {$status} for {$user->name}.");
    }

    public function updateClientId(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'legacy_client_id' => ['required', 'string', 'max:15', 'regex:/^C\d+$/'],
        ], [
            'legacy_client_id.regex' => 'Client ID must start with "C" followed by digits (e.g. C1234).',
        ]);

        $user->update([
            'legacy_client_id' => $request->input('legacy_client_id'),
        ]);

        return back()->with('success', "Client ID updated to {$user->legacy_client_id} for {$user->name}.");
    }
}
