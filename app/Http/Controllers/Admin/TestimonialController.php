<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Services\AuditLogger;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', \App\Models\User::class);

        $testimonials = Testimonial::query()->with(['user', 'avatar'])->orderBy('order')->paginate(20);

        $overview = [
            'total' => Testimonial::query()->count(),
            'average_rating' => (float) Testimonial::query()->whereNotNull('rating')->avg('rating'),
            'published' => Testimonial::query()->where('is_published', true)->count(),
            'unpublished' => Testimonial::query()->where('is_published', false)->count(),
        ];

        $openId = $request->integer('open') ?: $testimonials->first()?->id;

        return view('admin.testimonials.index', ['testimonials' => $testimonials, 'overview' => $overview, 'openId' => $openId]);
    }

    public function store(Request $request, MediaService $mediaService): RedirectResponse
    {
        $validated = $this->validated($request);
        $validated['avatar_media_id'] = $this->handleAvatar($request, $mediaService);

        $testimonial = Testimonial::create($validated);

        AuditLogger::log('admin.testimonial.created', $testimonial, [], $validated);

        return redirect()->route('admin.testimonials.index', ['open' => $testimonial->id])->with('status', 'Testimonial created.');
    }

    public function update(Request $request, Testimonial $testimonial, MediaService $mediaService): RedirectResponse
    {
        $validated = $this->validated($request);
        $avatarId = $this->handleAvatar($request, $mediaService);

        if ($avatarId) {
            $validated['avatar_media_id'] = $avatarId;
        }

        $testimonial->update($validated);

        AuditLogger::log('admin.testimonial.updated', $testimonial, [], $validated);

        return redirect()->route('admin.testimonials.index', ['open' => $testimonial->id])->with('status', 'Testimonial updated.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        AuditLogger::log('admin.testimonial.deleted', $testimonial, $testimonial->toArray());

        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('status', 'Testimonial deleted.');
    }

    public function togglePublish(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update(['is_published' => ! $testimonial->is_published]);

        AuditLogger::log('admin.testimonial.toggled', $testimonial, [], ['is_published' => $testimonial->is_published]);

        // Which testimonial is selected is client-side Alpine state, not
        // reflected in the URL, so back()/Referer has no way to know which
        // one to reopen — it was defaulting to the first testimonial in the
        // list. Pass it explicitly instead.
        return redirect()->route('admin.testimonials.index', ['open' => $testimonial->id])
            ->with('status', $testimonial->is_published ? 'Testimonial published.' : 'Testimonial unpublished.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role_label' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['boolean'],
        ]) + ['is_published' => $request->boolean('is_published'), 'order' => $request->integer('order')];
    }

    private function handleAvatar(Request $request, MediaService $mediaService): ?int
    {
        if ($request->hasFile('avatar_file')) {
            return $mediaService->storeUploadedFile($request->file('avatar_file'), 'image')->id;
        }

        if ($request->filled('avatar_url')) {
            return $mediaService->storeExternalImage($request->input('avatar_url'))->id;
        }

        return null;
    }
}
