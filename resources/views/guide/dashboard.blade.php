@extends('portal')
@section('title', 'Guide dashboard')
@section('content')
@php
    $guideName = auth()->user()->name;
    $guideInitials = collect(explode(' ', trim($guideName)))->filter()->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
@endphp
<div class="guide-dashboard">
    <header class="guide-welcome">
        <div><span class="eyebrow"><span></span> GUIDE WORKSPACE</span><h1>Welcome, {{ $guideName }}</h1><p>{{ $profile?->hospitals->pluck('name')->join(', ') ?: 'Your guide dashboard' }}</p></div>
        <span class="guide-verification {{ $profile?->is_verified ? 'is-verified' : 'is-pending' }}"><i class="bi {{ $profile?->is_verified ? 'bi-patch-check-fill' : 'bi-hourglass-split' }}"></i>{{ $profile?->is_verified ? 'Verified guide' : 'Verification pending' }}</span>
    </header>

    <section class="guide-stat-grid" aria-label="Booking overview">
        <article class="guide-stat guide-stat-availability"><span class="guide-stat-icon"><i class="bi bi-broadcast"></i></span><span class="guide-stat-label">Availability</span><strong>{{ $profile?->is_available && $profile?->is_verified ? 'Online' : 'Offline' }}</strong><small>{{ $profile?->is_available && $profile?->is_verified ? 'Visible for new bookings' : 'Not accepting new bookings' }}</small></article>
        <article class="guide-stat guide-stat-new"><span class="guide-stat-icon"><i class="bi bi-calendar-plus"></i></span><span class="guide-stat-label">New bookings</span><strong>{{ $newBookings->count() }}</strong><small>Requests waiting for your response</small></article>
        <article class="guide-stat guide-stat-today"><span class="guide-stat-icon"><i class="bi bi-calendar2-check"></i></span><span class="guide-stat-label">Today's bookings</span><strong>{{ $todayBookings->count() }}</strong><small>Visits scheduled for today</small></article>
        <article class="guide-stat guide-stat-value"><span class="guide-stat-icon"><i class="bi bi-currency-rupee"></i></span><span class="guide-stat-label">Today's booked value</span><strong>₹{{ number_format($todayBookedValue) }}</strong><small>Service value, not a payout</small></article>
    </section>

    <div class="guide-dashboard-grid">
        <div class="guide-dashboard-main">
            <section class="guide-panel guide-availability-panel" id="availability">
                <div class="guide-panel-heading"><div><span class="guide-panel-icon"><i class="bi bi-calendar-week"></i></span><div><h2>Availability</h2><p>Choose whether you can receive new requests.</p></div></div><span class="guide-live-status {{ $profile?->is_available && $profile?->is_verified ? 'is-online' : '' }}"><i></i>{{ $profile?->is_available && $profile?->is_verified ? 'Online' : 'Offline' }}</span></div>
                @if($profile)
                    <form method="POST" action="{{ route('guide.availability.update') }}" class="guide-availability-form">@csrf @method('PATCH')
                        <label class="guide-switch-label" for="guideAvailable"><span><strong>{{ $profile->is_available && $profile->is_verified ? 'You are accepting bookings' : 'You are not accepting bookings' }}</strong><small>{{ $profile->is_verified ? 'Changes take effect immediately.' : 'Your profile must be verified before going online.' }}</small></span><input id="guideAvailable" class="guide-switch" type="checkbox" name="is_available" value="1" @checked($profile->is_available && $profile->is_verified) @disabled(!$profile->is_verified) onchange="this.form.submit()"><span class="guide-switch-track" aria-hidden="true"></span></label>
                    </form>
                @endif
            </section>

            <section class="guide-panel" id="new-bookings">
                <div class="guide-panel-heading"><div><span class="guide-panel-icon"><i class="bi bi-inbox"></i></span><div><h2>New bookings</h2><p>Review each request and respond.</p></div></div><span class="guide-panel-count">{{ $newBookings->count() }}</span></div>
                @if($newBookings->isNotEmpty())
                    <div class="guide-booking-list">
                        @foreach($newBookings as $booking)
                            <article class="guide-booking-item">
                                <div class="guide-booking-date"><strong>{{ $booking->visit_date->format('d') }}</strong><span>{{ $booking->visit_date->format('M') }}</span></div>
                                <div class="guide-booking-info"><strong>{{ $booking->patient_name ?: $booking->patient->name }}</strong><span>{{ $booking->hospital->name }} · {{ $booking->visit_date->format('D, d M') }} at {{ substr($booking->start_time, 0, 5) }}</span><small>{{ $booking->service }}@if($booking->message) · {{ $booking->message }}@endif</small></div>
                                <div class="guide-booking-actions"><form method="POST" action="{{ route('guide.bookings.respond', $booking) }}">@csrf @method('PATCH')<button class="guide-button guide-button-accept" name="status" value="accepted"><i class="bi bi-check-lg"></i> Accept</button></form><form method="POST" action="{{ route('guide.bookings.respond', $booking) }}">@csrf @method('PATCH')<button class="guide-button guide-button-decline" name="status" value="rejected">Decline</button></form></div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="guide-empty"><i class="bi bi-calendar2-check"></i><strong>You're all caught up</strong><span>New booking requests will appear here.</span></div>
                @endif
            </section>

            <section class="guide-panel" id="upcoming-bookings">
                <div class="guide-panel-heading"><div><span class="guide-panel-icon"><i class="bi bi-calendar2-event"></i></span><div><h2>Upcoming bookings</h2><p>Accepted visits and requests ahead.</p></div></div><span class="guide-panel-count">{{ $upcomingBookings->count() }}</span></div>
                @if($upcomingBookings->isNotEmpty())
                    <div class="guide-table-wrap"><table class="guide-table"><thead><tr><th>Patient</th><th>Hospital & service</th><th>Date & time</th><th>Status</th><th>Action</th></tr></thead><tbody>
                        @foreach($upcomingBookings as $booking)
                            <tr><td><strong>{{ $booking->patient_name ?: $booking->patient->name }}</strong><small>{{ $booking->mobile_country_code && $booking->mobile_number ? $booking->mobile_country_code.' '.$booking->mobile_number : ($booking->mobile ?: $booking->patient->phone ?: 'Patient') }}</small></td><td><strong>{{ $booking->hospital->name }}</strong><small>{{ $booking->service }}</small></td><td>{{ $booking->visit_date->format('d M Y') }}<small>{{ substr($booking->start_time, 0, 5) }}</small></td><td><span class="guide-status guide-status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span></td><td>@if($booking->status === 'accepted')<form method="POST" action="{{ route('guide.bookings.respond', $booking) }}">@csrf @method('PATCH')<button class="guide-button guide-button-accept" name="status" value="received">Mark arrived</button></form>@elseif($booking->status === 'pending')<span class="guide-table-hint">Respond above</span>@else<span class="guide-table-hint">In progress</span>@endif</td></tr>
                        @endforeach
                    </tbody></table></div>
                @else
                    <div class="guide-empty guide-empty-compact"><span>No upcoming visits yet.</span></div>
                @endif
            </section>

            <section class="guide-panel" id="profile-settings">
                <details class="guide-profile-edit"><summary><span><i class="bi bi-person-gear"></i> Edit profile and weekly availability</span><i class="bi bi-chevron-down"></i></summary>
                    @if($profile)<form method="POST" action="{{ route('guide.profile.update') }}" class="guide-profile-form">@csrf @method('PUT')
                        <div class="guide-form-grid"><label class="guide-field"><span>City</span><input name="city" value="{{ old('city', $profile->city) }}" maxlength="120" required></label><label class="guide-field guide-field-wide"><span>About you</span><textarea name="bio" rows="2" maxlength="1200">{{ old('bio', $profile->bio) }}</textarea></label><label class="guide-field guide-field-wide"><span>Languages</span><input name="languages" value="{{ old('languages', implode(', ', $profile->languages ?? [])) }}" placeholder="Hindi, English"></label><label class="guide-field"><span>Experience (years)</span><input name="years_experience" type="number" min="0" max="60" value="{{ old('years_experience', $profile->years_experience) }}" required></label><label class="guide-field"><span>Hourly rate (₹)</span><input name="hourly_rate" type="number" min="0" step="50" value="{{ old('hourly_rate', $profile->hourly_rate) }}" required></label></div>
                        <fieldset class="guide-hospital-picker"><legend>Hospitals you cover</legend>@forelse($hospitals as $hospital)<label><input type="checkbox" name="hospitals[]" value="{{ $hospital->id }}" @checked(in_array($hospital->id, old('hospitals', $profile->hospitals->pluck('id')->all())))><span>{{ $hospital->name }} <small>{{ $hospital->city }}</small></span></label>@empty<p>No active hospitals listed.</p>@endforelse</fieldset>
                        @php($weeklyAvailability = $profile->availabilities->keyBy('weekday'))<fieldset class="guide-weekly-picker"><legend>Weekly availability</legend><div class="guide-weekly-grid">@foreach(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $weekday => $weekdayName)@php($slot = $weeklyAvailability->get($weekday))<label><span>{{ $weekdayName }}</span><div><input type="time" name="availability[{{ $weekday }}][start_time]" value="{{ old('availability.'.$weekday.'.start_time', $slot ? substr($slot->start_time, 0, 5) : '') }}" aria-label="{{ $weekdayName }} start"><input type="time" name="availability[{{ $weekday }}][end_time]" value="{{ old('availability.'.$weekday.'.end_time', $slot ? substr($slot->end_time, 0, 5) : '') }}" aria-label="{{ $weekdayName }} end"></div></label><input type="hidden" name="availability[{{ $weekday }}][weekday]" value="{{ $weekday }}">@endforeach</div></fieldset>
                        <button class="guide-button guide-button-accept" type="submit"><i class="bi bi-check-lg"></i> Save profile</button>
                    </form>@endif
                </details>
            </section>
        </div>

        <aside class="guide-dashboard-aside">
            <section class="guide-panel guide-profile-card">
                <div class="guide-profile-identity"><span class="guide-profile-avatar">@if(auth()->user()->avatar_url)<img src="{{ auth()->user()->avatar_url }}" alt="">@else{{ $guideInitials }}@endif</span><div><h2>{{ $guideName }}</h2><span class="guide-verification {{ $profile?->is_verified ? 'is-verified' : 'is-pending' }}"><i class="bi {{ $profile?->is_verified ? 'bi-patch-check-fill' : 'bi-hourglass-split' }}"></i>{{ $profile?->is_verified ? 'Verified guide' : 'Awaiting verification' }}</span></div></div>
                <dl class="guide-profile-facts"><div><dt><i class="bi bi-upc-scan"></i> Guide ID</dt><dd>ASG-{{ str_pad((string) ($profile?->id ?? auth()->id()), 6, '0', STR_PAD_LEFT) }}</dd></div><div><dt><i class="bi bi-geo-alt"></i> Location</dt><dd>{{ $profile?->city ?: $profile?->hospitals->first()?->city ?: 'Not set' }}</dd></div><div><dt><i class="bi bi-briefcase"></i> Experience</dt><dd>{{ $profile?->years_experience ?? 0 }} years</dd></div><div><dt><i class="bi bi-star-fill"></i> Rating</dt><dd>{{ $ratingCount ? number_format($averageRating, 1).' ('.$ratingCount.' reviews)' : 'No reviews yet' }}</dd></div></dl>
                <a class="guide-profile-link" href="#profile-settings">Manage profile <i class="bi bi-arrow-up-right"></i></a>
            </section>

            <section class="guide-panel guide-quick-panel"><div class="guide-panel-heading"><div><span class="guide-panel-icon"><i class="bi bi-lightning-charge"></i></span><div><h2>Quick actions</h2></div></div></div><div class="guide-quick-grid"><a href="#new-bookings"><i class="bi bi-inboxes"></i><span>View bookings</span></a><a href="#availability"><i class="bi bi-toggles"></i><span>Update availability</span></a><a href="#earnings"><i class="bi bi-currency-rupee"></i><span>View service value</span></a><a href="mailto:hello@aapkasarthi.in?subject=Guide%20support"><i class="bi bi-headset"></i><span>Help & support</span></a></div></section>

            <section class="guide-panel" id="earnings"><div class="guide-panel-heading"><div><span class="guide-panel-icon"><i class="bi bi-wallet2"></i></span><div><h2>Booked service value</h2><p>Not collected payouts</p></div></div></div><div class="guide-value-list"><div><span><i class="bi bi-calendar-day"></i> Today</span><strong>₹{{ number_format($todayBookedValue) }}</strong></div><div><span><i class="bi bi-calendar-week"></i> This week</span><strong>₹{{ number_format($weekBookedValue) }}</strong></div><div><span><i class="bi bi-calendar-month"></i> This month</span><strong>₹{{ number_format($monthBookedValue) }}</strong></div><div class="guide-value-note"><i class="bi bi-info-circle"></i> Payment collection and guide payouts are not enabled yet.</div></div></section>

            <section class="guide-panel" id="booking-history"><div class="guide-panel-heading"><div><span class="guide-panel-icon"><i class="bi bi-clock-history"></i></span><div><h2>Booking history</h2></div></div></div><div class="guide-history-list"><div><span><i class="bi bi-check-circle-fill is-complete"></i> Completed</span><strong>{{ $historyCounts['completed'] }}</strong></div><div><span><i class="bi bi-x-circle-fill is-cancelled"></i> Cancelled</span><strong>{{ $historyCounts['cancelled'] }}</strong></div><div><span><i class="bi bi-dash-circle-fill is-rejected"></i> Declined</span><strong>{{ $historyCounts['rejected'] }}</strong></div></div></section>
        </aside>
    </div>
</div>
@endsection
