@extends('portal')
@section('title', 'Guides at '.$hospital->name)
@section('content')
<div class="dashboard-heading"><div><div class="eyebrow"><span></span> {{ $hospital->city }} · {{ $hospital->type ?? 'Hospital' }}</div><h1>{{ $hospital->name }}</h1><p>{{ $hospital->address }}</p></div><a class="btn btn-outline-secondary" href="{{ route('home') }}#find"><i class="bi bi-arrow-left me-1"></i> All hospitals</a></div>
<div class="section-title guide-list-title"><div><span class="eyebrow"><span></span> Verified companions</span><h2>Guides who know this hospital</h2></div></div>
@if($guides->isEmpty())<div class="empty-panel"><i class="bi bi-person-vcard"></i><strong>No guides are available here just yet.</strong><p>We’re building the local guide network. Check again soon or suggest this hospital to our team.</p><a class="btn btn-forest" href="mailto:hello@hospitalsarthi.in">Suggest a guide <i class="bi bi-arrow-up-right ms-1"></i></a></div>@else<div class="guide-cards">@foreach($guides as $guide)<article class="guide-card"><div class="guide-card-top"><span class="guide-avatar">{{ strtoupper(substr($guide->user->name, 0, 1)) }}</span><div><h3>{{ $guide->user->name }}</h3><span><i class="bi bi-patch-check-fill"></i> Verified hospital guide</span></div><span class="guide-rate">₹{{ number_format((float)$guide->hourly_rate, 0) }}<small>/ hour</small></span></div><p>{{ $guide->bio ?: 'Here to help you navigate the hospital and feel more at ease during your visit.' }}</p><div class="guide-meta"><span><i class="bi bi-translate"></i> {{ implode(', ', $guide->languages ?? []) ?: 'Languages on request' }}</span><span><i class="bi bi-briefcase"></i> {{ $guide->years_experience }} years’ experience</span></div><div class="guide-schedule"><strong>Weekly availability</strong>@forelse($guide->availabilities->sortBy('weekday') as $slot)<span>{{ \Illuminate\Support\Carbon::createFromTimestamp(strtotime('Sunday +'.$slot->weekday.' days'))->format('D') }} · {{ substr($slot->start_time, 0, 5) }}–{{ substr($slot->end_time, 0, 5) }}</span>@empty<span>Contact the guide to confirm availability.</span>@endforelse</div>
@if(auth()->check() && auth()->user()->role === 'patient')<details class="booking-details"><summary class="btn btn-forest">Request this guide <i class="bi bi-arrow-right ms-2"></i></summary><form method="POST" action="{{ route('bookings.store') }}" class="booking-form">@csrf<input type="hidden" name="hospital_id" value="{{ $hospital->id }}"><input type="hidden" name="guide_profile_id" value="{{ $guide->id }}"><label class="form-label">What do you need help with?</label><select name="service" class="form-select" required>@foreach(['OPD registration', 'Department navigation', 'Token and queue guidance', 'Diagnostic centre navigation', 'Billing and pharmacy', 'Report collection', 'Discharge process', 'General hospital navigation'] as $service)<option value="{{ $service }}">{{ $service }}</option>@endforeach</select><div class="row g-2 mt-1"><div class="col-7"><label class="form-label">Visit date</label><input type="date" name="visit_date" class="form-control" min="{{ now()->toDateString() }}" required></div><div class="col-5"><label class="form-label">Start time</label><input type="time" name="start_time" class="form-control" required></div></div><label class="form-label mt-2">Note for the guide <span class="optional">(optional)</span></label><textarea name="message" class="form-control" rows="2" maxlength="1000" placeholder="Any practical details they should know?"></textarea><button class="btn btn-forest w-100 mt-3" type="submit">Send booking request <i class="bi bi-send ms-2"></i></button></form></details>@elseif(!auth()->check())<a class="btn btn-forest" href="{{ route('login', ['redirect' => request()->fullUrl()]) }}">Log in to request this guide <i class="bi bi-arrow-right ms-2"></i></a>@else<span class="guide-role-note">Patient accounts can request a guide.</span>@endif
</article>@endforeach</div>@endif
<p class="guide-disclaimer"><i class="bi bi-info-circle me-1"></i> Guides assist with hospital navigation and other practical tasks. They do not provide medical advice or treatment.</p>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
	const requestedService = @json(request()->query('service'));
	if (!requestedService) return;

	document.querySelectorAll('.booking-form select[name="service"]').forEach((select) => {
		let option = Array.from(select.options).find((item) => item.value === requestedService);
		if (!option && requestedService === 'OPD Assistance') {
			option = new Option(requestedService, requestedService);
			select.add(option, 0);
		}
		if (option) select.value = requestedService;
	});
});
</script>
@endpush
@endsection
