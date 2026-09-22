<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Track;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Track::class);

        $tracks = Track::query()
            ->withCount('enrollments')
            ->with(['enrollments' => function ($query) {
                $query->with(['user', 'subscription', 'progress'])->latest('enrolled_at');
            }])
            ->orderBy('order')
            ->get();

        return view('admin.enrollments.index', [
            'tracks' => $tracks,
        ]);
    }
}
