<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use App\Models\Service;
use Illuminate\View\View;

class HospitalController extends Controller
{
    public function guides(Hospital $hospital): View
    {
        abort_unless($hospital->is_active, 404);

        $guides = $hospital->guides()
            ->with(['user:id,name', 'availabilities'])
            ->where('is_verified', true)
            ->where('is_available', true)
            ->orderByDesc('years_experience')
            ->get();

        $services = Service::query()->where('is_active', true)->orderBy('name')->pluck('name');

        return view('hospitals.guides', compact('hospital', 'guides', 'services'));
    }
}