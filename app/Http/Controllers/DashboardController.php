<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Hospital;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            return view('admin.dashboard');
        } elseif ($user->role === 'guide') {
            $profile = $user->guideProfile()->with(['hospitals', 'availabilities'])->first();
            $bookings = $profile
                ? $profile->bookings()->with(['patient', 'hospital'])->get()
                : collect();
            $today = Carbon::today();
            $todayBookings = $bookings->filter(fn ($booking) => $booking->visit_date->isSameDay($today)
                && ! in_array($booking->status, ['cancelled', 'rejected'], true))->values();
            $upcomingBookings = $bookings->filter(fn ($booking) => $booking->visit_date->greaterThanOrEqualTo($today)
                && in_array($booking->status, ['pending', 'accepted', 'received'], true))
                ->sortBy(fn ($booking) => $booking->visit_date->format('Y-m-d').' '.$booking->start_time)
                ->take(6)->values();
            $valueBetween = fn (Carbon $start, Carbon $end) => $bookings
                ->filter(fn ($booking) => $booking->visit_date->betweenIncluded($start, $end)
                    && ! in_array($booking->status, ['cancelled', 'rejected'], true))
                ->sum(fn ($booking) => (float) $booking->amount);
            $ratingQuery = Review::query()->where('is_visible', true)->whereHas('booking', fn ($query) => $query->where('guide_profile_id', $profile?->id));

            return view('guide.dashboard', [
                'profile' => $profile,
                'hospitals' => Hospital::where('is_active', true)->orderBy('name')->get(),
                'newBookings' => $bookings->where('status', 'pending')
                    ->sortBy(fn ($booking) => $booking->visit_date->format('Y-m-d').' '.$booking->start_time)->take(6)->values(),
                'todayBookings' => $todayBookings,
                'upcomingBookings' => $upcomingBookings,
                'todayBookedValue' => $valueBetween($today, $today),
                'weekBookedValue' => $valueBetween($today->copy()->startOfWeek(), $today->copy()->endOfWeek()),
                'monthBookedValue' => $valueBetween($today->copy()->startOfMonth(), $today->copy()->endOfMonth()),
                'historyCounts' => [
                    'completed' => $bookings->where('status', 'completed')->count(),
                    'cancelled' => $bookings->where('status', 'cancelled')->count(),
                    'rejected' => $bookings->where('status', 'rejected')->count(),
                ],
                'averageRating' => (float) ($ratingQuery->avg('rating') ?? 0),
                'ratingCount' => $ratingQuery->count(),
            ]);
        } else {
            $data = [
                'bookings' => $user->patientBookings()->with(['hospital', 'guideProfile.user'])->latest('visit_date')->get(),
            ];
        }

        return view('dashboard', ['role' => $user->role] + $data);
    }
}