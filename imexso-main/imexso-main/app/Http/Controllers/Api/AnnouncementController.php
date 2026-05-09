<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function active(Request $request): JsonResponse
    {
        $locale = $request->query('locale', config('locales.default'));
        if (! in_array($locale, config('locales.supported'), true)) {
            $locale = config('locales.default');
        }

        $announcements = Announcement::query()
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->orderBy('starts_at')
            ->with('translations')
            ->get()
            ->map(function (Announcement $a) use ($locale) {
                $t = $a->translationForLocale($locale);
                if ($t === null) {
                    return null;
                }

                return [
                    'id' => $a->id,
                    'title' => $t->title,
                    'body' => $t->body,
                    'starts_at' => $a->starts_at->toIso8601String(),
                    'ends_at' => $a->ends_at->toIso8601String(),
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'data' => $announcements,
        ]);
    }
}
