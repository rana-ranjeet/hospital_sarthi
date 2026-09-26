<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\GuideProfile;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        abort_unless($request->user()->role === 'patient', 403);

        $validated = $request->validate([
            'hospital_id' => ['required', 'integer', 'exists:hospitals,id'],
            'guide_profile_id' => ['required', 'integer', 'exists:guide_profiles,id'],
            'service' => ['required', 'string', Rule::exists('services', 'name')->where('is_active', true)],
            'visit_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $booking = DB::transaction(function () use ($request, $validated) {
            abort_unless(\App\Models\Hospital::query()->whereKey($validated['hospital_id'])->where('is_active', true)->exists(), 422, 'This hospital is not accepting bookings.');
            $guide = GuideProfile::query()->lockForUpdate()->findOrFail($validated['guide_profile_id']);
            abort_unless($guide->is_verified && $guide->is_available, 422, 'This guide is not available for booking.');
            abort_unless($guide->hospitals()->whereKey($validated['hospital_id'])->exists(), 422, 'This guide does not cover that hospital.');

            $weekday = Carbon::parse($validated['visit_date'])->dayOfWeek;
            $available = $guide->availabilities()
                ->where('weekday', $weekday)
                ->where('start_time', '<=', $validated['start_time'])
                ->where('end_time', '>', $validated['start_time'])
                ->exists();
            abort_unless($available, 422, 'That time is outside the guide’s listed availability.');

            $conflict = Booking::query()
                ->where('guide_profile_id', $guide->id)
                ->whereDate('visit_date', $validated['visit_date'])
                ->where('start_time', $validated['start_time'])
                ->whereIn('status', ['pending', 'accepted'])
                ->exists();
            abort_if($conflict, 422, 'That time has just been requested. Please choose another time.');

            $booking = Booking::create($validated + ['patient_id' => $request->user()->id]);
            $serviceId = Service::query()->where('name', $validated['service'])->where('is_active', true)->valueOrFail('id');
            $booking->services()->sync([$serviceId]);

            return $booking;
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Booking request sent.', 'booking' => $booking], 201);
        }

        return redirect()->route('dashboard')->with('status', 'Your booking request was sent to the guide.');
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->patient_id === $request->user()->id, 403);
        abort_unless($booking->status === 'pending', 422, 'Only pending booking requests can be cancelled.');

        $booking->update(['status' => 'cancelled']);

        return back()->with('status', 'Booking request cancelled.');
    }
}