<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TranslationController extends Controller
{
    public function index(Request $request, string $locale): JsonResponse
    {
        $translations = Cache::remember(
            "translations.{$locale}",
            3600,
            fn () => Translation::query()
                ->where('locale', $locale)
                ->get()
                ->groupBy('group')
                ->map(fn ($items) => $items->pluck('value', 'key'))
                ->toArray(),
        );

        return response()->json($translations);
    }
}
