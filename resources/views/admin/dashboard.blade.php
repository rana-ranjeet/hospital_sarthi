@extends('portal')
@section('title', 'Admin dashboard')
@section('content')
<div class="dashboard-heading">
    <div><div class="eyebrow"><span></span> Administration</div><h1>Admin workspace</h1><p>Choose a section to manage.</p></div>
    <span class="role-chip"><i class="bi bi-shield-lock"></i> Admin</span>
</div>

@php
    $adminSections = [
        ['users', 'User accounts', 'bi-people'],
        ['guides', 'Guide profiles', 'bi-person-badge'],
        ['hospitals', 'Hospitals', 'bi-hospital'],
        ['services', 'Services', 'bi-clipboard2-check'],
        ['bookings', 'Bookings', 'bi-calendar2-check'],
        ['payments', 'Payments', 'bi-credit-card'],
        ['reviews', 'Reviews', 'bi-chat-square-text'],
        ['commission', 'Commission', 'bi-graph-up-arrow'],
    ];
@endphp
<section class="portal-section mt-0" aria-label="Admin sections">
    <nav class="list-group list-group-flush">
        @foreach($adminSections as [$section, $label, $icon])
            <a class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-3" href="{{ route('admin.manage', $section) }}">
                <span class="d-flex align-items-center gap-3"><i class="bi {{ $icon }} text-success"></i><span>{{ $label }}</span></span>
                <i class="bi bi-arrow-up-right text-secondary" aria-hidden="true"></i>
            </a>
        @endforeach
    </nav>
</section>
@endsection
