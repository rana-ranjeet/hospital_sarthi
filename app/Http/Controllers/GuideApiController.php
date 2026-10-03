<?php

namespace App\Http\Controllers;

use App\Models\GuideProfile;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GuideApiController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->validate([
            'hospital_id' => ['nullable', 'integer', 'exists:hospitals,id'],
            'date' => ['nullable', 'required_with:time', 'date', 'after_or_equal:today'],
            'time' => ['nullable', 'required_with:date', 'date_format:H:i'],
        ]);
        $weekday = isset($filters['date']) ? Carbon::parse($filters['date'])->dayOfWeek : null;

        $guides = GuideProfile::query()
            ->with(['user:id,name,avatar_url', 'hospitals:id,name,city'])
            ->where('is_verified', true)
            ->where('is_available', true)
            ->when($request->filled('hospital_id'), fn ($query) => $query
                ->whereHas('hospitals', fn ($hospital) => $hospital->whereKey($request->integer('hospital_id'))->where('is_active', true)))
            ->when(isset($filters['date'], $filters['time']), function ($query) use ($filters, $weekday) {
                $query->whereHas('availabilities', fn ($availability) => $availability
                    ->where('weekday', $weekday)
                    ->where('start_time', '<=', $filters['time'])
                    ->where('end_time', '>', $filters['time']))
                    ->whereDoesntHave('bookings', fn ($booking) => $booking
                        ->whereDate('visit_date', $filters['date'])
                        ->where('start_time', $filters['time'])
                        ->whereIn('status', ['pending', 'accepted', 'received']));
            })
            ->get()
            ->map(fn (GuideProfile $guide) => [
                'id' => $guide->id,
                'name' => $guide->user->name,
                'photo' => $guide->user->avatar_url,
                'bio' => $guide->bio,
                'languages' => $guide->languages ?? [],
                'years_experience' => $guide->years_experience,
                'hourly_rate' => $guide->hourly_rate,
                'hospitals' => $guide->hospitals->map(fn ($hospital) => ['id' => $hospital->id, 'name' => $hospital->name, 'city' => $hospital->city]),
            ]);

        return response()->json($guides);
    }
}