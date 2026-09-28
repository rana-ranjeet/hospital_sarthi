<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuideController extends Controller
{
    public function updateAvailability(Request $request): RedirectResponse
    {
        $profile = $request->user()->guideProfile()->first();
        abort_unless($profile, 404);

        $isAvailable = $request->boolean('is_available');
        abort_if($isAvailable && ! $profile->is_verified, 403, 'Your guide profile must be verified before accepting bookings.');

        $profile->update(['is_available' => $isAvailable]);

        return back()->with('status', $isAvailable ? 'You are now accepting new bookings.' : 'You are now offline.');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'city' => ['required', 'string', 'max:120'],
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
                'city' => $validated['city'],
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

        $validated = $request->validate(['status' => ['required', 'in:accepted,rejected,received']]);
        $allowedTransition = $booking->status === 'pending' && in_array($validated['status'], ['accepted', 'rejected'], true)
            || $booking->status === 'accepted' && $validated['status'] === 'received';
        abort_unless($allowedTransition, 422, 'This booking request cannot be updated to that status.');
        $booking->update(['status' => $validated['status']]);

        $message = match ($validated['status']) {
            'accepted' => 'Booking request accepted.',
            'rejected' => 'Booking request declined.',
            'received' => 'You have been marked as arrived.',
        };

        return back()->with('status', $message);
    }
}