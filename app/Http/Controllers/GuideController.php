<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuideController extends Controller
{
    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bio' => ['nullable', 'string', 'max:1200'],
            'languages' => ['nullable', 'string', 'max:200'],
            'years_experience' => ['required', 'integer', 'min:0', 'max:60'],
            'hourly_rate' => ['required', 'numeric', 'min:0', 'max:100000'],
            'hospitals' => ['nullable', 'array'],
            'hospitals.*' => ['integer', 'exists:hospitals,id'],
            'availability' => ['nullable', 'array'],
            'availability.*.weekday' => ['required', 'integer', 'between:0,6'],
            'availability.*.start_time' => ['nullable', 'date_format:H:i'],
            'availability.*.end_time' => ['nullable', 'date_format:H:i'],
        ]);

        $hospitalIds = array_values(array_unique($validated['hospitals'] ?? []));
        $activeHospitalCount = DB::table('hospitals')->whereIn('id', $hospitalIds)->where('is_active', true)->count();
        if ($activeHospitalCount !== count($hospitalIds)) {
            return back()->withErrors(['hospitals' => 'Choose only currently listed hospitals.'])->withInput();
        }

        $availability = array_filter($validated['availability'] ?? [], fn ($slot) => ! empty($slot['start_time']) && ! empty($slot['end_time']));
        foreach ($availability as $slot) {
            if (Carbon::createFromFormat('H:i', $slot['start_time'])->greaterThanOrEqualTo(Carbon::createFromFormat('H:i', $slot['end_time']))) {
                return back()->withErrors(['availability' => 'Each availability end time must be later than its start time.'])->withInput();
            }
        }

        $profile = $request->user()->guideProfile;
        DB::transaction(function () use ($profile, $validated, $hospitalIds, $availability, $request) {
            $profile->update([
                'bio' => $validated['bio'] ?? null,
                'languages' => array_values(array_filter(array_map('trim', explode(',', $validated['languages'] ?? '')))),
                'years_experience' => $validated['years_experience'],
                'hourly_rate' => $validated['hourly_rate'],
                'is_available' => $request->boolean('is_available'),
            ]);
            $profile->hospitals()->sync($hospitalIds);
            $profile->availabilities()->delete();
            foreach ($availability as $slot) {
                $profile->availabilities()->create($slot);
            }
        });

        return back()->with('status', 'Your guide profile and availability were updated. Verification is managed by our admin team.');
    }

    public function respond(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->guide_profile_id === $request->user()->guideProfile?->id, 403);

        $validated = $request->validate(['status' => ['required', 'in:accepted,rejected']]);
        abort_unless($booking->status === 'pending', 422, 'This booking request has already been handled.');
        $booking->update(['status' => $validated['status']]);

        return back()->with('status', 'Booking request '.($validated['status'] === 'accepted' ? 'accepted.' : 'declined.'));
    }
}