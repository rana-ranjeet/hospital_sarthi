<?php

namespace App\Http\Controllers;

use App\Models\Hospital;

class HomeController extends Controller
{
    public function __invoke()
    {
        $hospitals = Hospital::query()
            ->where('is_active', true)
            ->withCount(['guides as verified_guides_count' => fn ($query) => $query->where('is_verified', true)])
            ->orderByDesc('verified_guides_count')
            ->limit(6)
            ->get();

        return view('components.hero', compact('hospitals'));
    }
}