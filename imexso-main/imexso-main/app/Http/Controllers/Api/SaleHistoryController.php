<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SaleHistoryResource;
use App\Models\SaleHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SaleHistoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = SaleHistory::query();

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->input('client_id'));
        }

        if ($request->filled('id_produit')) {
            $query->where('id_produit', $request->input('id_produit'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('make')) {
            $query->where('make', $request->input('make'));
        }

        $query->orderBy('order_date', 'desc')->orderBy('id', 'desc');

        return SaleHistoryResource::collection($query->paginate(
            $request->integer('per_page', 15),
        ));
    }

    public function show(SaleHistory $saleHistory): SaleHistoryResource
    {
        return new SaleHistoryResource($saleHistory);
    }
}
