<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SitePage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SitePageController extends Controller
{
    public function show(Request $request, string $slug): JsonResponse
    {
        $locale = $request->query('locale', config('locales.default'));
        if (! in_array($locale, config('locales.supported'), true)) {
            $locale = config('locales.default');
        }

        $page = SitePage::query()->where('slug', $slug)->first();
        if ($page === null || ! $page->isPublished()) {
            abort(404);
        }

        $payload = Cache::remember(
            "site_page.{$slug}.{$locale}",
            3600,
            function () use ($page, $locale) {
                $content = $page->contents()->where('locale', $locale)->first();

                return [
                    'slug' => $page->slug,
                    'locale' => $locale,
                    'published_at' => $page->published_at?->toIso8601String(),
                    'payload' => $content?->payload ?? [],
                ];
            }
        );

        return response()->json($payload);
    }
}
