@extends('portal')
@section('title', 'Admin management')
@section('content')
<div class="dashboard-heading">
    <div><div class="eyebrow"><span></span> Administration</div><h1>Platform management</h1><p>Manage accounts, guide access, hospital listings, services and transactions.</p></div>
    <span class="role-chip"><i class="bi bi-shield-lock"></i> Admin</span>
</div>

<section class="portal-section" id="users">
    <div class="section-title"><div><span class="eyebrow"><span></span> Accounts</span><h2>Users</h2></div>
        <details><summary class="btn btn-forest btn-sm">Add User</summary>
            <form method="POST" action="{{ route('admin.users.store') }}" class="portal-form mt-3">@csrf
                <div class="row g-2">
                    <div class="col-md-3"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
                    <div class="col-md-3"><label class="form-label">Email</label><input class="form-control" name="email" type="email" required></div>
                    <div class="col-md-2"><label class="form-label">Phone</label><input class="form-control" name="phone"></div>
                    <div class="col-md-2"><label class="form-label">Password</label><input class="form-control" name="password" type="password" minlength="8" required></div>
                    <div class="col-md-2"><label class="form-label">Confirm password</label><input class="form-control" name="password_confirmation" type="password" minlength="8" required></div>
                </div>
                <button class="btn btn-forest btn-sm mt-3">Create patient account</button>
            </form>
        </details>
    </div>
    <div class="admin-list">
        @forelse($users as $managedUser)
            <article class="admin-row">
                <span><strong>{{ $managedUser->name }}</strong><small>{{ $managedUser->email }} · {{ ucfirst($managedUser->role) }} · {{ $managedUser->blocked_at ? 'Blocked' : 'Active' }}</small></span>
                <details><summary class="btn btn-outline-secondary btn-sm">View / Edit</summary>
                    <form method="POST" action="{{ route('admin.users.update', $managedUser) }}" class="portal-form mt-3">@csrf @method('PUT')
                        <label class="form-label">Name</label><input class="form-control mb-2" name="name" value="{{ $managedUser->name }}" required>
                        <label class="form-label">Email</label><input class="form-control mb-2" name="email" type="email" value="{{ $managedUser->email }}" required>
                        <label class="form-label">Phone</label><input class="form-control mb-2" name="phone" value="{{ $managedUser->phone }}">
                        <button class="btn btn-forest btn-sm">Save changes</button>
                    </form>
                </details>
                <form method="POST" action="{{ route('admin.users.block', $managedUser) }}">@csrf @method('PATCH')<button class="btn btn-outline-secondary btn-sm">{{ $managedUser->blocked_at ? 'Activate' : 'Block' }}</button></form>
                @if($managedUser->id !== auth()->id())<form method="POST" action="{{ route('admin.users.delete', $managedUser) }}" onsubmit="return confirm('Permanently delete this user and dependent records?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm">Delete</button></form>@endif
            </article>
        @empty<p class="help-text">No users found.</p>@endforelse
    </div>
    {{ $users->links() }}
</section>

<section class="portal-section" id="guides">
    <div class="section-title"><div><span class="eyebrow"><span></span> Review guide profiles</span><h2>Guides</h2></div>
        <details><summary class="btn btn-forest btn-sm">Add Guide</summary>
            <form method="POST" action="{{ route('admin.guides.store') }}" class="portal-form mt-3">@csrf
                <div class="row g-2">
                    <div class="col-md-3"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
                    <div class="col-md-3"><label class="form-label">Email</label><input class="form-control" name="email" type="email" required></div>
                    <div class="col-md-2"><label class="form-label">Phone</label><input class="form-control" name="phone"></div>
                    <div class="col-md-2"><label class="form-label">Password</label><input class="form-control" name="password" type="password" minlength="8" required></div>
                    <div class="col-md-2"><label class="form-label">Confirm password</label><input class="form-control" name="password_confirmation" type="password" minlength="8" required></div>
                </div>
                <button class="btn btn-forest btn-sm mt-3">Create guide account</button>
            </form>
        </details>
    </div>
    <div class="admin-list">
        @forelse($guides as $guide)
            @php($guideStatus = $guide->status ?? ($guide->is_verified ? 'verified' : 'pending'))
            @php($availabilityByDay = $guide->availabilities->keyBy('weekday'))
            <article class="admin-row">
                <span><strong>{{ $guide->user->name }}</strong><small>{{ $guide->user->email }} · {{ $guide->years_experience }} years · {{ $guide->specialization ?: 'Specialization not set' }}</small><small>{{ $guide->hospitals->pluck('name')->join(', ') ?: 'No hospitals selected' }} · {{ ucfirst($guideStatus) }}</small></span>
                <details class="flex-grow-1">
                    <summary class="btn btn-outline-secondary btn-sm">View / Manage</summary>
                    <div class="portal-form mt-3">
                        <div class="row g-2 mb-3">
                            <div class="col-md-4"><strong>Profile</strong><p>{{ $guide->bio ?: 'No bio provided.' }}</p></div>
                            <div class="col-md-4"><strong>Languages</strong><p>{{ is_array($guide->languages) ? implode(', ', $guide->languages) : ($guide->languages ?: 'Not specified') }}</p></div>
                            <div class="col-md-4"><strong>Experience and fee</strong><p>{{ $guide->years_experience }} years · ₹{{ number_format((float) $guide->hourly_rate, 2) }} / hour</p></div>
                        </div>
                        <form method="POST" action="{{ route('admin.guides.update', $guide) }}">@csrf @method('PUT')
                            <h3 class="h6">Account and profile</h3>
                            <div class="row g-3">
                                <div class="col-md-4"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ $guide->user->name }}" required></div>
                                <div class="col-md-4"><label class="form-label">Email</label><input class="form-control" name="email" type="email" value="{{ $guide->user->email }}" required></div>
                                <div class="col-md-4"><label class="form-label">Phone</label><input class="form-control" name="phone" value="{{ $guide->user->phone }}"></div>
                                <div class="col-md-6"><label class="form-label">Bio / description</label><textarea class="form-control" name="bio" rows="3" maxlength="1200">{{ $guide->bio }}</textarea></div>
                                <div class="col-md-6"><label class="form-label">Languages (comma separated)</label><input class="form-control" name="languages" value="{{ is_array($guide->languages) ? implode(', ', $guide->languages) : $guide->languages }}"></div>
                                <div class="col-md-4"><label class="form-label">Specialization</label><input class="form-control" name="specialization" value="{{ $guide->specialization }}" maxlength="160"></div>
                                <div class="col-md-4"><label class="form-label">Years of experience</label><input class="form-control" name="years_experience" type="number" min="0" max="60" value="{{ $guide->years_experience }}" required></div>
                                <div class="col-md-4"><label class="form-label">Hourly fee (₹)</label><input class="form-control" name="hourly_rate" type="number" min="0" max="100000" step="0.01" value="{{ $guide->hourly_rate }}" required></div>
                                <div class="col-md-4"><label class="form-label">Verification status</label><select class="form-select" name="status">@foreach(['pending', 'verified', 'rejected', 'suspended'] as $status)<option value="{{ $status }}" @selected($guideStatus === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
                                <div class="col-md-8 d-flex align-items-end"><label class="form-check mb-2"><input class="form-check-input" type="checkbox" name="is_available" value="1" @checked($guide->is_available)> Accepting bookings (verified guides only)</label></div>
                                <div class="col-12"><fieldset><legend class="form-label">Associated hospitals</legend><div class="row g-2">@foreach($hospitals as $hospital)<div class="col-md-4"><label class="form-check"><input class="form-check-input" type="checkbox" name="hospitals[]" value="{{ $hospital->id }}" @checked($guide->hospitals->contains('id', $hospital->id))> {{ $hospital->name }}{{ $hospital->is_active ? '' : ' (hidden)' }}</label></div>@endforeach</div></fieldset></div>
                                <div class="col-12"><fieldset><legend class="form-label">Weekly availability</legend><div class="row g-2">@foreach(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $weekday => $weekdayName)@php($slot = $availabilityByDay->get($weekday))<div class="col-md-6 col-lg-4"><label class="form-label">{{ $weekdayName }}</label><div class="d-flex gap-2"><input class="form-control" type="time" name="availability[{{ $weekday }}][start_time]" value="{{ $slot ? substr($slot->start_time, 0, 5) : '' }}" aria-label="{{ $weekdayName }} start time"><input class="form-control" type="time" name="availability[{{ $weekday }}][end_time]" value="{{ $slot ? substr($slot->end_time, 0, 5) : '' }}" aria-label="{{ $weekdayName }} end time"></div><input type="hidden" name="availability[{{ $weekday }}][weekday]" value="{{ $weekday }}"></div>@endforeach</div></fieldset></div>
                            </div>
                            <button class="btn btn-forest btn-sm mt-3">Save guide changes</button>
                        </form>
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                            @if($guide->document_path)
                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.guides.documents.view', $guide) }}" target="_blank" rel="noopener">View document</a>
                                <form method="POST" action="{{ route('admin.guides.documents.delete', $guide) }}" onsubmit="return confirm('Remove this guide document?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm">Delete document</button></form>
                            @else<span class="help-text">No document uploaded.</span>@endif
                            <form method="POST" enctype="multipart/form-data" action="{{ route('admin.guides.documents.upload', $guide) }}">@csrf<input class="form-control form-control-sm" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" aria-label="Upload guide document" required><button class="btn btn-outline-secondary btn-sm mt-1">Upload document</button></form>
                        </div>
                    </div>
                </details>
                @if($guideStatus !== 'verified')<form method="POST" action="{{ route('admin.guides.verify', $guide) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="verified"><button class="btn btn-forest btn-sm">{{ $guideStatus === 'pending' ? 'Verify' : 'Activate' }}</button></form>@else<form method="POST" action="{{ route('admin.guides.verify', $guide) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="suspended"><button class="btn btn-outline-secondary btn-sm">Suspend</button></form>@endif
            </article>
        @empty<p class="help-text">No guide applications found.</p>@endforelse
    </div>
</section>

<section class="portal-section" id="hospitals">
    <div class="section-title"><div><span class="eyebrow"><span></span> Directory</div><h2>Hospitals</h2></div>
    <form method="POST" action="{{ route('admin.hospitals.store') }}" class="portal-form admin-hospital-form">@csrf<div class="row g-3"><div class="col-md-6"><label class="form-label">Hospital name</label><input class="form-control" name="name" required></div><div class="col-md-3"><label class="form-label">City</label><input class="form-control" name="city" required></div><div class="col-md-3"><label class="form-label">Type</label><input class="form-control" name="type"></div><div class="col-md-6"><label class="form-label">Address</label><input class="form-control" name="address" required></div><div class="col-md-3"><label class="form-label">Phone</label><input class="form-control" name="phone"></div><div class="col-md-3"><label class="form-label">Image URL</label><input class="form-control" type="url" name="image_url"></div></div><button class="btn btn-forest mt-3">Add hospital</button></form>
    <div class="admin-list mt-3">@foreach($hospitals as $hospital)<article class="admin-row"><span><strong>{{ $hospital->name }}</strong><small>{{ $hospital->city }} · {{ $hospital->address }} · {{ $hospital->is_active ? 'Active' : 'Hidden' }}</small></span><details><summary class="btn btn-outline-secondary btn-sm">Edit</summary><form method="POST" action="{{ route('admin.hospitals.update', $hospital) }}" class="portal-form mt-3">@csrf @method('PUT')<input class="form-control mb-2" name="name" value="{{ $hospital->name }}" required><input class="form-control mb-2" name="city" value="{{ $hospital->city }}" required><input class="form-control mb-2" name="type" value="{{ $hospital->type }}"><input class="form-control mb-2" name="address" value="{{ $hospital->address }}" required><input class="form-control mb-2" name="phone" value="{{ $hospital->phone }}"><input class="form-control mb-2" type="url" name="image_url" value="{{ $hospital->image_url }}"><button class="btn btn-forest btn-sm">Save</button></form></details><form method="POST" action="{{ route('admin.hospitals.toggle', $hospital) }}">@csrf @method('PATCH')<button class="btn btn-outline-secondary btn-sm">{{ $hospital->is_active ? 'Hide' : 'Activate' }}</button></form><form method="POST" action="{{ route('admin.hospitals.delete', $hospital) }}" onsubmit="return confirm('Delete this hospital?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm">Delete</button></form></article>@endforeach</div>
</section>

<section class="portal-section" id="services">
    <div class="section-title"><div><span class="eyebrow"><span></span> Booking catalog</div><h2>Services</h2></div>
    <form method="POST" action="{{ route('admin.services.store') }}" class="portal-form"><div class="row g-2">@csrf<div class="col-md-4"><label class="form-label">Name</label><input class="form-control" name="name" required></div><div class="col-md-4"><label class="form-label">Description</label><input class="form-control" name="description"></div><div class="col-md-2"><label class="form-label">Base price</label><input class="form-control" name="base_price" type="number" min="0" step="0.01" value="0" required></div><div class="col-md-2 d-flex align-items-end"><button class="btn btn-forest">Add service</button></div></div></form>
    <div class="admin-list mt-3">@forelse($services as $service)<article class="admin-row"><span><strong>{{ $service->name }}</strong><small>{{ $service->description ?: 'No description' }} · ₹{{ number_format((float) $service->base_price, 2) }} · {{ $service->is_active ? 'Active' : 'Inactive' }}</small></span><details><summary class="btn btn-outline-secondary btn-sm">Edit</summary><form method="POST" action="{{ route('admin.services.update', $service) }}" class="portal-form mt-3">@csrf @method('PUT')<input class="form-control mb-2" name="name" value="{{ $service->name }}" required><input class="form-control mb-2" name="description" value="{{ $service->description }}"><input class="form-control mb-2" name="base_price" type="number" min="0" step="0.01" value="{{ $service->base_price }}" required><label class="form-check"><input type="checkbox" name="is_active" value="1" @checked($service->is_active)> Active</label><button class="btn btn-forest btn-sm">Save</button></form></details><form method="POST" action="{{ route('admin.services.delete', $service) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm">Delete</button></form></article>@empty<p class="help-text">No services configured.</p>@endforelse</div>
</section>

<section class="portal-section" id="bookings">
    <div class="section-title"><div><span class="eyebrow"><span></span> Visit requests</div><h2>Bookings</h2></div>
    <form method="GET" action="{{ route('dashboard') }}" class="d-flex gap-2 mb-3"><select class="form-select" name="booking_status" aria-label="Filter bookings by status"><option value="">All statuses</option>@foreach(['pending', 'accepted', 'rejected', 'cancelled', 'completed'] as $status)<option value="{{ $status }}" @selected($bookingStatus === $status)>{{ ucfirst($status) }}</option>@endforeach</select><button class="btn btn-outline-secondary">Filter</button></form>
    @forelse($bookings as $booking)<article class="booking-row"><div class="booking-date"><strong>{{ $booking->visit_date->format('d') }}</strong><span>{{ $booking->visit_date->format('M Y') }}</span></div><div class="booking-details"><strong>{{ $booking->hospital->name }} · {{ $booking->service }}</strong><span>{{ $booking->patient->name }} with {{ $booking->guideProfile->user->name }}</span><small>{{ ucfirst($booking->status) }}</small></div><form method="POST" action="{{ route('admin.bookings.status', $booking) }}" class="d-flex gap-2">@csrf @method('PATCH')<select class="form-select form-select-sm" name="status" aria-label="Booking status">@foreach(['pending', 'accepted', 'rejected', 'cancelled', 'completed'] as $status)<option value="{{ $status }}" @selected($booking->status === $status)>{{ ucfirst($status) }}</option>@endforeach</select><button class="btn btn-forest btn-sm">Save</button></form></article>@empty<div class="empty-panel">No bookings match this filter.</div>@endforelse
    {{ $bookings->links() }}
</section>

<section class="portal-section" id="payments">
    <div class="section-title"><div><span class="eyebrow"><span></span> Transactions</div><h2>Payments</h2></div>
    <div class="admin-list">@forelse($payments as $payment)<article class="admin-row"><span><strong>{{ $payment->transaction_reference ?: 'Payment #'.$payment->id }} · {{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</strong><small>{{ $payment->booking->patient->name }} · {{ $payment->booking->hospital->name }} · {{ $payment->provider ?: 'Provider pending' }}</small></span><span class="status status-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span><small>{{ $payment->paid_at?->format('d M Y, H:i') ?: 'Not paid' }}</small></article>@empty<p class="help-text">No payment transactions recorded.</p>@endforelse</div>
</section>

<section class="portal-section" id="reviews">
    <div class="section-title"><div><span class="eyebrow"><span></span> Customer feedback</div><h2>Reviews</h2></div>
    <div class="admin-list">@forelse($reviews as $review)<article class="admin-row"><span><strong>{{ $review->rating }}/5 · {{ $review->booking->patient->name }} for {{ $review->booking->guideProfile->user->name }}</strong><small>{{ $review->booking->hospital->name }} · {{ $review->comment }}</small></span><form method="POST" action="{{ route('admin.reviews.visibility', $review) }}">@csrf @method('PATCH')<button class="btn btn-outline-secondary btn-sm">{{ $review->is_visible ? 'Hide' : 'Publish' }}</button></form><form method="POST" action="{{ route('admin.reviews.delete', $review) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm">Delete</button></form></article>@empty<p class="help-text">No reviews recorded.</p>@endforelse</div>
    {{ $reviews->links() }}
</section>

<section class="portal-section" id="commission">
    <div class="section-title"><div><span class="eyebrow"><span></span> Platform revenue</div><h2>Commission</h2></div>
    <form method="POST" action="{{ route('admin.commission.update') }}" class="portal-form">@csrf @method('PUT')<div class="row g-3"><div class="col-md-4"><label class="form-label">Commission rate (%)</label><input class="form-control" name="percentage_rate" type="number" min="0" max="100" step="0.01" value="{{ $commission?->percentage_rate ?? 0 }}" required></div><div class="col-md-4"><label class="form-label">Fixed amount per booking (₹)</label><input class="form-control" name="fixed_amount" type="number" min="0" step="0.01" value="{{ $commission?->fixed_amount ?? 0 }}" required></div><div class="col-md-4 d-flex align-items-end"><button class="btn btn-forest">Save commission</button></div></div></form>
</section>
@endsection
