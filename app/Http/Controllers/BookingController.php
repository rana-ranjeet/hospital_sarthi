<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\GuideProfile;
use App\Models\Hospital;
use App\Models\Service;
use App\Rules\ValidMobileNumber;
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

        $isModalRequest = $request->hasAny([
            'service_id', 'guide_id', 'date', 'time', 'patient_name', 'mobile', 'mobile_number', 'note',
            'alternate_mobile_number', 'age', 'gender', 'blood_group', 'relationship_with_patient',
        ]);
        $user = $request->user();
        $mobileCountryCode = (string) $request->input('mobile_country_code', $user->mobile_country_code ?: '+91');
        $alternateCountryCode = (string) $request->input('alternate_country_code', $user->alternate_country_code ?: '+91');
        $mobileInputField = $request->exists('mobile_number') ? 'mobile_number' : 'mobile';
        $mobileNumber = $request->input('mobile_number', $request->input('mobile'));
        if ($mobileNumber === null) {
            $mobileNumber = $user->mobile_number ?: $this->normalizeIndianMobile($user->phone);
        } elseif ($mobileInputField === 'mobile') {
            $mobileNumber = $this->normalizeIndianMobile($mobileNumber);
        }

        $request->merge([
            'guide_profile_id' => $request->input('guide_id', $request->input('guide_profile_id')),
            'visit_date' => $request->input('date', $request->input('visit_date')),
            'start_time' => $request->input('time', $request->input('start_time')),
            'message' => $request->input('note', $request->input('message')),
            'patient_name' => $request->input('patient_name', $user->name),
            $mobileInputField => $mobileNumber,
            'mobile_country_code' => $mobileCountryCode,
            'alternate_country_code' => $alternateCountryCode,
            'alternate_mobile_number' => $request->input('alternate_mobile_number', $user->alternate_mobile_number),
            'age' => $request->input('age', $user->age),
            'gender' => $request->input('gender', $user->gender),
            'blood_group' => $request->input('blood_group', $user->blood_group),
            'relationship_with_patient' => $request->input('relationship_with_patient', $user->relationship_with_patient ?: 'Self'),
            'other_relationship' => $request->input('other_relationship', $user->other_relationship),
        ]);

        $countryCodes = array_keys(config('patient.country_calling_codes'));
        $validator = Validator::make($request->all(), [
            'hospital_id' => ['required', 'integer', 'exists:hospitals,id'],
            'guide_profile_id' => ['required', 'integer', 'exists:guide_profiles,id'],
            'service_id' => ['nullable', 'required_without:service', 'integer', Rule::exists('services', 'id')->where('is_active', true)],
            'service' => ['nullable', 'required_without:service_id', 'string', Rule::exists('services', 'name')->where('is_active', true)],
            'patient_name' => [$isModalRequest ? 'required' : 'nullable', 'string', 'max:120'],
            'mobile_country_code' => ['required', 'string', Rule::in($countryCodes)],
            $mobileInputField => [$isModalRequest ? 'required' : 'nullable', 'string', 'regex:/^\d+$/', new ValidMobileNumber($mobileCountryCode)],
            'alternate_country_code' => ['required', 'string', Rule::in($countryCodes)],
            'alternate_mobile_number' => ['nullable', 'string', 'regex:/^\d+$/', new ValidMobileNumber($alternateCountryCode)],
            'age' => ['nullable', 'integer', 'between:0,120'],
            'gender' => ['nullable', Rule::in(config('patient.genders'))],
            'blood_group' => ['nullable', Rule::in(config('patient.blood_groups'))],
            'relationship_with_patient' => ['nullable', Rule::in(config('patient.relationships'))],
            'other_relationship' => ['nullable', 'required_if:relationship_with_patient,Other', 'string', 'max:120'],
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
    $validated['mobile_number'] = $validated[$mobileInputField] ?? null;

        try {
            $booking = DB::transaction(function () use ($request, $validated) {
                Booking::expirePendingResponses();

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
                    ->whereIn('status', ['pending', 'accepted', 'received'])
                    ->exists();
                if ($conflict) {
                    throw ValidationException::withMessages(['start_time' => 'That time has just been requested. Please choose another time.']);
                }

                $booking = Booking::create([
                    'patient_id' => $request->user()->id,
                    'patient_name' => $validated['patient_name'] ?? $request->user()->name,
                    'mobile' => $validated['mobile_country_code'] === '+91' ? $validated['mobile_number'] : null,
                    'mobile_country_code' => $validated['mobile_country_code'],
                    'mobile_number' => $validated['mobile_number'],
                    'alternate_country_code' => $validated['alternate_country_code'],
                    'alternate_mobile_number' => $validated['alternate_mobile_number'] ?? null,
                    'age' => $validated['age'] ?? null,
                    'gender' => $validated['gender'] ?? null,
                    'blood_group' => $validated['blood_group'] ?? null,
                    'relationship_with_patient' => $validated['relationship_with_patient'] ?? null,
                    'other_relationship' => $validated['other_relationship'] ?? null,
                    'guide_profile_id' => $guide->id,
                    'hospital_id' => $hospital->id,
                    'service' => $service->name,
                    'visit_date' => $validated['visit_date'],
                    'start_time' => $validated['start_time'],
                    'message' => $validated['message'] ?? null,
                    'amount' => $service->base_price,
                    'status' => 'pending',
                    'response_deadline' => now()->addMinutes(2),
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
                'response_deadline' => $booking->response_deadline->toIso8601String(),
                'seconds_remaining' => 120,
                'payment_status' => $booking->payment_status,
            ], 201);
        }

        return redirect()->route('dashboard')->with('status', 'Your booking request was sent to the guide.');
    }

    public function status(Request $request, Booking $booking): JsonResponse
    {
        abort_unless($booking->patient_id === $request->user()->id, 403);

        Booking::query()
            ->whereKey($booking->id)
            ->where('status', 'pending')
            ->whereNotNull('response_deadline')
            ->where('response_deadline', '<=', now())
            ->update(['status' => 'expired']);

        $booking->refresh()->loadMissing(['guideProfile.user']);
        $remaining = $booking->status === 'pending' && $booking->response_deadline
            ? max(0, now()->diffInSeconds($booking->response_deadline, false))
            : 0;

        $response = [
            'booking_id' => $booking->id,
            'status' => $booking->status,
            'response_deadline' => $booking->response_deadline?->toIso8601String(),
            'seconds_remaining' => $remaining,
        ];

        if ($booking->status === 'accepted') {
            $guideUser = $booking->guideProfile?->user;
            $rawPhone = trim((string) ($guideUser?->phone ?: $guideUser?->mobile_number));
            $digits = preg_replace('/\D+/', '', $rawPhone);
            if (strlen($digits) === 10) {
                $digits = ltrim((string) ($guideUser?->mobile_country_code ?: '+91'), '+').$digits;
            } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
                $digits = '91'.substr($digits, 1);
            }

            $response['guide'] = [
                'name' => $guideUser?->name,
                'phone' => $rawPhone ?: null,
                'whatsapp_url' => $digits ? 'https://wa.me/'.$digits.'?text='.rawurlencode('Hi, my booking #'.$booking->id.' was accepted. I am contacting you about the visit.') : null,
            ];
        }

        return response()->json($response);
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