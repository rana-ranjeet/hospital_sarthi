<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\GuideProfile;
use App\Models\Hospital;
use App\Models\Commission;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $bookings = Booking::query()
                ->with(['patient', 'guideProfile.user', 'hospital', 'payments'])
                ->when($request->filled('booking_status'), fn ($query) => $query->where('status', $request->string('booking_status')))
                ->latest()
                ->paginate(20, ['*'], 'bookings_page')
                ->withQueryString();

            return view('admin.dashboard', [
                'hospitals' => Hospital::orderBy('name')->get(),
                'guides' => GuideProfile::with(['user', 'hospitals'])->latest()->get(),
                'users' => User::query()->with('guideProfile')->latest()->paginate(20, ['*'], 'users_page')->withQueryString(),
                'bookings' => $bookings,
                'services' => Service::query()->orderBy('name')->get(),
                'payments' => Payment::query()->with(['booking.patient', 'booking.hospital'])->latest()->limit(25)->get(),
                'reviews' => Review::query()->with(['booking.patient', 'booking.guideProfile.user', 'booking.hospital'])->latest()->paginate(20, ['*'], 'reviews_page')->withQueryString(),
                'commission' => Commission::query()->where('is_active', true)->latest()->first(),
                'bookingStatus' => $request->query('booking_status', ''),
            ]);
        } elseif ($user->role === 'guide') {
            $data = [
                'profile' => $user->guideProfile()->with(['hospitals', 'availabilities'])->first(),
                'bookings' => Booking::with(['patient', 'hospital'])->where('guide_profile_id', $user->guideProfile?->id)->latest('visit_date')->get(),
                'hospitals' => Hospital::where('is_active', true)->orderBy('name')->get(),
            ];
        } else {
            $data = [
                'bookings' => $user->patientBookings()->with(['hospital', 'guideProfile.user'])->latest('visit_date')->get(),
            ];
        }

        return view('dashboard', ['role' => $user->role] + $data);
    }
}