<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use App\Models\Service;

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

        $bookingHospital = Hospital::query()
            ->where('is_active', true)
            ->whereHas('guides', fn ($query) => $query->where('is_verified', true)->where('is_available', true))
            ->orderBy('name')
            ->first();
        $modalServices = Service::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'base_price'])
            ->map(fn (Service $service) => [
                'id' => $service->id,
                'label' => $service->name,
                'description' => $service->description,
                'price' => $service->base_price,
            ]);
        $modalGuides = $bookingHospital?->guides()
            ->with('user:id,name')
            ->where('is_verified', true)
            ->where('is_available', true)
            ->orderByDesc('years_experience')
            ->get()
            ->map(fn ($guide) => [
                'id' => $guide->id,
                'name' => $guide->user->name,
                'rating' => null,
                'years' => $guide->years_experience,
                'languages' => implode(', ', $guide->languages ?? []),
                'initials' => collect(explode(' ', $guide->user->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode(''),
                'photo' => null,
            ]) ?? collect();

        return view('components.hero', compact('hospitals', 'bookingHospital', 'modalServices', 'modalGuides'));
    }
}