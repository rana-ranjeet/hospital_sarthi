<?php

namespace App\Http\Controllers;

use App\Models\GuideProfile;
use Illuminate\Http\Request;

class GuideApiController extends Controller
{
    public function __invoke(Request $request)
    {
        $guides = GuideProfile::query()
            ->with(['user:id,name', 'hospitals:id,name,city'])
            ->where('is_verified', true)
            ->where('is_available', true)
            ->when($request->filled('hospital_id'), fn ($query) => $query->whereHas('hospitals', fn ($hospital) => $hospital->whereKey($request->integer('hospital_id'))->where('is_active', true)))
            ->get()
            ->map(fn (GuideProfile $guide) => [
                'id' => $guide->id,
                'name' => $guide->user->name,
                'bio' => $guide->bio,
                'languages' => $guide->languages ?? [],
                'years_experience' => $guide->years_experience,
                'hourly_rate' => $guide->hourly_rate,
                'hospitals' => $guide->hospitals->map(fn ($hospital) => ['id' => $hospital->id, 'name' => $hospital->name, 'city' => $hospital->city]),
            ]);

        return response()->json($guides);
    }
}