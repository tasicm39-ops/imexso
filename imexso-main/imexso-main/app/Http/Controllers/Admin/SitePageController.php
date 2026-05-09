<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SitePage;
use App\Models\SitePageContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SitePageController extends Controller
{
    public function index(): Response
    {
        $pages = SitePage::query()
            ->orderBy('slug')
            ->get(['id', 'slug', 'published_at']);

        return Inertia::render('admin/site-pages/index', [
            'pages' => $pages,
        ]);
    }

    public function edit(SitePage $site_page): Response
    {
        $site_page->load('contents');

        $payloadJson = array_fill_keys(config('locales.supported'), '{}');
        foreach (config('locales.supported') as $locale) {
            $row = $site_page->contents->firstWhere('locale', $locale);
            if ($row !== null) {
                $payloadJson[$locale] = json_encode($row->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            }
        }

        return Inertia::render('admin/site-pages/edit', [
            'page' => $site_page,
            'payloadJson' => $payloadJson,
        ]);
    }

    public function update(Request $request, SitePage $site_page): RedirectResponse
    {
        $payloadRules = [];
        foreach (config('locales.supported') as $locale) {
            $payloadRules['payload_'.$locale] = 'required|string';
        }

        $validated = $request->validate(array_merge([
            'published_at' => 'nullable|date',
        ], $payloadRules));

        foreach (config('locales.supported') as $locale) {
            $key = 'payload_'.$locale;
            try {
                $decoded = json_decode($validated[$key], true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                throw ValidationException::withMessages([
                    $key => 'Invalid JSON.',
                ]);
            }
            SitePageContent::query()->updateOrCreate(
                [
                    'site_page_id' => $site_page->id,
                    'locale' => $locale,
                ],
                [
                    'payload' => $decoded,
                ]
            );
        }

        $publishedAt = $validated['published_at'] ?? null;
        if ($publishedAt === '') {
            $publishedAt = null;
        }

        $site_page->update([
            'published_at' => $publishedAt,
        ]);

        foreach (config('locales.supported') as $locale) {
            Cache::forget("site_page.{$site_page->slug}.{$locale}");
        }

        return redirect()->route('admin.site-pages.index')->with('success', 'Page saved.');
    }
}
