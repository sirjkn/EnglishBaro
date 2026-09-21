<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardRedirectController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        if (Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('student.dashboard');
    }
}
