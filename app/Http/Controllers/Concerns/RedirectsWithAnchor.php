<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait RedirectsWithAnchor
{
    /**
     * Redirect back to the referring page, reopening the <details> element
     * named by the form's "open" field (e.g. "lesson-12") so the admin
     * doesn't lose their place in a long edit page after saving.
     */
    private function backToAnchor(Request $request): RedirectResponse
    {
        $anchor = $request->string('open')->value();
        $anchor = preg_match('/^[A-Za-z0-9_-]+$/', $anchor) ? $anchor : null;

        $url = url()->previous().($anchor ? '#'.$anchor : '');

        return redirect($url);
    }
}
