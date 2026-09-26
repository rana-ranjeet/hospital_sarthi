<?php

namespace App\Http\Controllers;

use App\Models\GuideProfile;
use App\Models\Hospital;
use App\Models\Booking;
use App\Models\Commission;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    public function storeHospital(Request $request): RedirectResponse
    {
        $details = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'city' => ['required', 'string', 'max:120'],
            'type' => ['nullable', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'image_url' => ['nullable', 'url', 'max:255'],
        ]);

        Hospital::create($details);

        return back()->with('status', 'Hospital listing added.');
    }

    public function toggleHospital(Hospital $hospital): RedirectResponse
    {
        $hospital->update(['is_active' => ! $hospital->is_active]);

        return back()->with('status', 'Hospital listing updated.');
    }

    public function verifyGuide(Request $request, GuideProfile $guideProfile): RedirectResponse
    {
        $status = $request->validate(['status' => ['required', 'in:verified,rejected,suspended']])['status'];
        $guideProfile->update([
            'status' => $status,
            'is_verified' => $status === 'verified',
            'is_available' => $status === 'verified',
        ]);

        return back()->with('status', 'Guide status updated to '.$status.'.');
    }

    public function uploadGuideDocument(Request $request, GuideProfile $guideProfile): RedirectResponse
    {
        $request->validate(['document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240']]);
        if ($guideProfile->document_path) {
            Storage::disk('local')->delete($guideProfile->document_path);
        }
        $guideProfile->update(['document_path' => $request->file('document')->store('guide-documents')]);

        return back()->with('status', 'Guide document uploaded.');
    }

    public function viewGuideDocument(GuideProfile $guideProfile): Response
    {
        abort_unless($guideProfile->document_path && Storage::disk('local')->exists($guideProfile->document_path), 404);

        return Storage::disk('local')->response($guideProfile->document_path);
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $details = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);
        $user->update($details);

        return back()->with('status', 'User details updated.');
    }

    public function toggleUserBlock(User $user): RedirectResponse
    {
        abort_if($user->is(auth()->user()), 422, 'You cannot block your own account.');
        $user->update(['blocked_at' => $user->blocked_at ? null : now()]);

        return back()->with('status', $user->blocked_at ? 'User account blocked.' : 'User account unblocked.');
    }

    public function deleteUser(User $user): RedirectResponse
    {
        abort_if($user->is(auth()->user()), 422, 'You cannot delete your own account.');
        $user->delete();

        return back()->with('status', 'User account deleted.');
    }

    public function updateHospital(Request $request, Hospital $hospital): RedirectResponse
    {
        $hospital->update($request->validate([
            'name' => ['required', 'string', 'max:180'],
            'city' => ['required', 'string', 'max:120'],
            'type' => ['nullable', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'image_url' => ['nullable', 'url', 'max:255'],
        ]));

        return back()->with('status', 'Hospital listing updated.');
    }

    public function deleteHospital(Hospital $hospital): RedirectResponse
    {
        abort_if($hospital->bookings()->exists(), 422, 'Hospitals with bookings cannot be deleted. Hide the listing instead.');
        $hospital->delete();

        return back()->with('status', 'Hospital listing deleted.');
    }

    public function storeService(Request $request): RedirectResponse
    {
        Service::create($this->validatedService($request));

        return back()->with('status', 'Service created.');
    }

    public function updateService(Request $request, Service $service): RedirectResponse
    {
        $service->update($this->validatedService($request, $service));

        return back()->with('status', 'Service updated.');
    }

    public function deleteService(Service $service): RedirectResponse
    {
        abort_if($service->bookings()->exists(), 422, 'Services used by bookings cannot be deleted. Deactivate this service instead.');
        $service->delete();

        return back()->with('status', 'Service deleted.');
    }

    public function updateBookingStatus(Request $request, Booking $booking): RedirectResponse
    {
        $booking->update($request->validate(['status' => ['required', 'in:pending,accepted,rejected,cancelled,completed']]));

        return back()->with('status', 'Booking status updated.');
    }

    public function toggleReviewVisibility(Review $review): RedirectResponse
    {
        $review->update(['is_visible' => ! $review->is_visible]);

        return back()->with('status', $review->is_visible ? 'Review is visible.' : 'Review hidden.');
    }

    public function deleteReview(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('status', 'Review deleted.');
    }

    public function updateCommission(Request $request): RedirectResponse
    {
        $details = $request->validate([
            'percentage_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'fixed_amount' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ]);

        DB::transaction(function () use ($details) {
            Commission::query()->update(['is_active' => false]);
            Commission::create($details + ['is_active' => true, 'effective_at' => now()]);
        });

        return back()->with('status', 'Platform commission updated.');
    }

    private function validatedService(Request $request, ?Service $service = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:160', 'unique:services,name'.($service ? ','.$service->id : '')],
            'description' => ['nullable', 'string', 'max:2000'],
            'base_price' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'is_active' => ['sometimes', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}