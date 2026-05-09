<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnnouncementRequest;
use App\Models\Announcement;
use App\Models\AnnouncementTranslation;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    public function index(): Response
    {
        $announcements = Announcement::query()
            ->with('translations')
            ->orderByDesc('starts_at')
            ->get();

        return Inertia::render('admin/announcements/index', [
            'announcements' => $announcements,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/announcements/create');
    }

    public function store(AnnouncementRequest $request): RedirectResponse
    {
        $announcement = Announcement::query()->create([
            'starts_at' => $request->validated('starts_at'),
            'ends_at' => $request->validated('ends_at'),
            'is_active' => $request->boolean('is_active'),
            'show_to_validated_only' => $request->boolean('show_to_validated_only'),
        ]);

        foreach ($request->normalizedTranslations() as $locale => $fields) {
            AnnouncementTranslation::query()->create([
                'announcement_id' => $announcement->id,
                'locale' => $locale,
                'title' => $fields['title'],
                'body' => $fields['body'],
            ]);
        }

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement created.');
    }

    public function edit(Announcement $announcement): Response
    {
        $announcement->load('translations');

        $titles = [];
        $bodies = [];
        foreach (config('locales.supported') as $locale) {
            $t = $announcement->translations->firstWhere('locale', $locale);
            $titles[$locale] = $t?->title ?? '';
            $bodies[$locale] = $t?->body ?? '';
        }

        return Inertia::render('admin/announcements/edit', [
            'announcement' => $announcement,
            'titles' => $titles,
            'bodies' => $bodies,
        ]);
    }

    public function update(AnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $announcement->update([
            'starts_at' => $request->validated('starts_at'),
            'ends_at' => $request->validated('ends_at'),
            'is_active' => $request->boolean('is_active'),
            'show_to_validated_only' => $request->boolean('show_to_validated_only'),
        ]);

        foreach ($request->normalizedTranslations() as $locale => $fields) {
            AnnouncementTranslation::query()->updateOrCreate(
                [
                    'announcement_id' => $announcement->id,
                    'locale' => $locale,
                ],
                [
                    'title' => $fields['title'],
                    'body' => $fields['body'],
                ]
            );
        }

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement deleted.');
    }
}
