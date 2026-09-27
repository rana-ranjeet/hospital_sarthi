<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\GuideProfile;
use App\Models\Hospital;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        abort_unless($request->user()->role === 'patient', 403);

        $isModalRequest = $request->hasAny(['service_id', 'guide_id', 'date', 'time', 'patient_name', 'mobile', 'note']);
        $mobile = $this->normalizeIndianMobile($request->input('mobile', $request->user()->phone));

        $request->merge([
            'guide_profile_id' => $request->input('guide_id', $request->input('guide_profile_id')),
            'visit_date' => $request->input('date', $request->input('visit_date')),
            'start_time' => $request->input('time', $request->input('start_time')),
            'message' => $request->input('note', $request->input('message')),
            'patient_name' => $request->input('patient_name', $request->user()->name),
            'mobile' => $mobile,
        ]);

        $validator = Validator::make($request->all(), [
            'hospital_id' => ['required', 'integer', 'exists:hospitals,id'],
            'guide_profile_id' => ['required', 'integer', 'exists:guide_profiles,id'],
            'service_id' => ['nullable', 'required_without:service', 'integer', Rule::exists('services', 'id')->where('is_active', true)],
            'service' => ['nullable', 'required_without:service_id', 'string', Rule::exists('services', 'name')->where('is_active', true)],
            'patient_name' => [$isModalRequest ? 'required' : 'nullable', 'string', 'max:120'],
            'mobile' => [$isModalRequest ? 'required' : 'nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'visit_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please correct the booking details and try again.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        try {
            $booking = DB::transaction(function () use ($request, $validated) {
                $hospital = Hospital::query()->whereKey($validated['hospital_id'])->where('is_active', true)->first();
                if (! $hospital) {
                    throw ValidationException::withMessages(['hospital_id' => 'This hospital is not accepting bookings.']);
                }

                $guide = GuideProfile::query()->lockForUpdate()->findOrFail($validated['guide_profile_id']);
                if (! $guide->is_verified || ! $guide->is_available) {
                    throw ValidationException::withMessages(['guide_profile_id' => 'This guide is not available for booking.']);
                }
                if (! $guide->hospitals()->whereKey($validated['hospital_id'])->exists()) {
                    throw ValidationException::withMessages(['guide_profile_id' => 'This guide does not cover that hospital.']);
                }

                $service = isset($validated['service_id'])
                    ? Service::query()->where('is_active', true)->findOrFail($validated['service_id'])
                    : Service::query()->where('is_active', true)->where('name', $validated['service'])->firstOrFail();

                $weekday = Carbon::parse($validated['visit_date'])->dayOfWeek;
                $available = $guide->availabilities()
                    ->where('weekday', $weekday)
                    ->where('start_time', '<=', $validated['start_time'])
                    ->where('end_time', '>', $validated['start_time'])
                    ->exists();
                if (! $available) {
                    throw ValidationException::withMessages(['start_time' => 'That time is outside the guide’s listed availability.']);
                }

                $conflict = Booking::query()
                    ->where('guide_profile_id', $guide->id)
                    ->whereDate('visit_date', $validated['visit_date'])
                    ->where('start_time', $validated['start_time'])
                    ->whereIn('status', ['pending', 'accepted'])
                    ->exists();
                if ($conflict) {
                    throw ValidationException::withMessages(['start_time' => 'That time has just been requested. Please choose another time.']);
                }

                $booking = Booking::create([
                    'patient_id' => $request->user()->id,
                    'patient_name' => $validated['patient_name'] ?? $request->user()->name,
                    'mobile' => $validated['mobile'] ?? null,
                    'guide_profile_id' => $guide->id,
                    'hospital_id' => $hospital->id,
                    'service' => $service->name,
                    'visit_date' => $validated['visit_date'],
                    'start_time' => $validated['start_time'],
                    'message' => $validated['message'] ?? null,
                    'amount' => $service->base_price,
                    'status' => 'pending',
                    'payment_status' => 'pending',
                ]);
                $booking->services()->sync([$service->id]);

                return $booking;
            });
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage() ?: 'The booking could not be created.',
                    'errors' => $exception->errors(),
                ], 422);
            }

            throw $exception;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully. Payment is pending; no payment was collected.',
                'booking_id' => $booking->id,
                'status' => $booking->status,
                'payment_status' => $booking->payment_status,
            ], 201);
        }

        return redirect()->route('dashboard')->with('status', 'Your booking request was sent to the guide.');
    }

    private function normalizeIndianMobile(?string $mobile): ?string
    {
        if ($mobile === null || trim($mobile) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $mobile);
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return $digits;
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->patient_id === $request->user()->id, 403);
        abort_unless($booking->status === 'pending', 422, 'Only pending booking requests can be cancelled.');

        $booking->update(['status' => 'cancelled']);

        return back()->with('status', 'Booking request cancelled.');
    }
}