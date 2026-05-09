<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OfferController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Offer::query()
            ->with(['user:id,name,email,company_name', 'car:id,make,model,id_produit,professional_price'])
            ->orderByDesc('created_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('delivery_type')) {
            $query->where('delivery_type', $request->string('delivery_type')->toString());
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                    ->orWhere('client_email', 'like', "%{$search}%")
                    ->orWhereHas('car', function ($carQ) use ($search) {
                        $carQ->where('id_produit', 'like', "%{$search}%")
                            ->orWhere('make', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    });
            });
        }

        $users = User::query()
            ->whereHas('offers')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('admin/offers/index', [
            'offers' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['user_id', 'delivery_type', 'search']),
            'users' => $users,
        ]);
    }
}
