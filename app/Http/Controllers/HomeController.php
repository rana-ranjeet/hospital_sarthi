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
            ->orderBy('name')
            ->get(['id', 'name', 'city']);
        $cityOptions = collect(['Bhopal', 'Lucknow'])
            ->merge($hospitals->pluck('city'))
            ->filter()
            ->unique(fn ($city) => mb_strtolower($city))
            ->sort()
            ->values();
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
        $hospitalOptions = $hospitals->map(fn (Hospital $hospital) => [
            'id' => $hospital->id,
            'name' => $hospital->name,
            'city' => $hospital->city,
        ]);

        return view('components.hero', compact('hospitals', 'cityOptions', 'hospitalOptions', 'modalServices'));
    }
}