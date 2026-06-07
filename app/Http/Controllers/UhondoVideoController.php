<?php

namespace App\Http\Controllers;

use App\Models\UhondoVideo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UhondoVideoController extends Controller
{
    public function index(): View
    {
        $videos = UhondoVideo::query()
            ->orderBy('display_order')
            ->latest()
            ->get();

        return view('dashboard.uhondo.index', ['videos' => $videos]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'episode_label' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'display_order' => 'nullable|integer|min:0',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'video' => 'required|file|mimes:mp4,webm,ogv,ogg,mov|max:512000',
        ]);

        $validated['display_order'] = $validated['display_order'] ?? 0;
        $validated['is_active'] = $request->has('is_active');
        $validated['video_path'] = $request->file('video')->store('uhondo-videos', 'public');

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail_path'] = $request->file('thumbnail')->store('uhondo-thumbnails', 'public');
        }

        UhondoVideo::create($validated);

        return redirect()
            ->route('uhondo.index')
            ->with('success', 'Uhondo video uploaded successfully.');
    }

    public function edit(UhondoVideo $uhondo): View
    {
        return view('dashboard.uhondo.edit', ['video' => $uhondo]);
    }

    public function update(Request $request, UhondoVideo $uhondo): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'episode_label' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'display_order' => 'nullable|integer|min:0',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'video' => 'nullable|file|mimes:mp4,webm,ogv,ogg,mov|max:512000',
        ]);

        $validated['display_order'] = $validated['display_order'] ?? 0;
        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('video')) {
            if ($uhondo->video_path && Storage::disk('public')->exists($uhondo->video_path)) {
                Storage::disk('public')->delete($uhondo->video_path);
            }

            $validated['video_path'] = $request->file('video')->store('uhondo-videos', 'public');
        }

        if ($request->hasFile('thumbnail')) {
            if ($uhondo->thumbnail_path && Storage::disk('public')->exists($uhondo->thumbnail_path)) {
                Storage::disk('public')->delete($uhondo->thumbnail_path);
            }

            $validated['thumbnail_path'] = $request->file('thumbnail')->store('uhondo-thumbnails', 'public');
        }

        $uhondo->update($validated);

        return redirect()
            ->route('uhondo.index')
            ->with('success', 'Uhondo video updated successfully.');
    }

    public function destroy(UhondoVideo $uhondo): RedirectResponse
    {
        if ($uhondo->video_path && Storage::disk('public')->exists($uhondo->video_path)) {
            Storage::disk('public')->delete($uhondo->video_path);
        }

        if ($uhondo->thumbnail_path && Storage::disk('public')->exists($uhondo->thumbnail_path)) {
            Storage::disk('public')->delete($uhondo->thumbnail_path);
        }

        $uhondo->delete();

        return redirect()
            ->route('uhondo.index')
            ->with('success', 'Uhondo video deleted successfully.');
    }

    public function publicIndex(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 12), 1), 24);

        $paginator = UhondoVideo::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->latest()
            ->paginate($perPage);

        $videos = $paginator->getCollection()->map(fn (UhondoVideo $video): array => [
            'id' => $video->id,
            'title' => $video->title,
            'episode_label' => $video->episode_label,
            'description' => $video->description,
            'video_url' => route('api.uhondo-videos.stream', $video),
            'thumbnail_url' => $video->thumbnail_url,
            'created_at' => $video->created_at?->toIso8601String(),
        ]);

        return response()
            ->json([
                'data' => $videos,
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'has_more' => $paginator->hasMorePages(),
                    'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
                ],
            ])
            ->header('Access-Control-Allow-Origin', '*');
    }

    public function stream(UhondoVideo $uhondo): BinaryFileResponse
    {
        abort_unless($uhondo->is_active, 404);
        abort_unless(Storage::disk('public')->exists($uhondo->video_path), 404);

        return response()
            ->file(Storage::disk('public')->path($uhondo->video_path), [
                'Access-Control-Allow-Origin' => '*',
                'Content-Type' => Storage::disk('public')->mimeType($uhondo->video_path) ?? 'video/mp4',
            ]);
    }
}
