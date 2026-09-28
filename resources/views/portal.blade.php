<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>@yield('title', 'Your account') · Hospital Sarthi</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,500;9..144,600&display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
	<link href="{{ asset('css/portal.css') }}" rel="stylesheet">
	<link href="{{ asset('css/portal-footer.css') }}" rel="stylesheet">
	<link href="{{ asset('css/account-profile.css') }}" rel="stylesheet">
	@if(auth()->check() && auth()->user()->role === 'guide')<link href="{{ asset('css/guide-dashboard.css') }}" rel="stylesheet">@endif
</head>
<body><header class="portal-header"><div class="container portal-nav"><a class="brand" href="{{ route('home') }}"><span class="brand-mark"><i class="bi bi-plus-lg"></i></span><span>hospital<span class="brand-light">sarthi</span><small>HERE WITH YOU</small></span></a><div class="portal-nav-right">@auth<a class="account-profile-link" href="{{ route('dashboard') }}" aria-label="Profile: {{ auth()->user()->name }}" title="{{ auth()->user()->name }}"><span class="account-profile-avatar">@if(auth()->user()->avatar_url)<img src="{{ auth()->user()->avatar_url }}" alt="">@else<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path stroke-linecap="round" d="M4.5 20a7.5 7.5 0 0115 0"/></svg>@endif</span><span class="account-profile-name">{{ auth()->user()->name }}</span></a>@endauth<a href="{{ route('home') }}"><i class="bi bi-arrow-left me-1"></i> Back to home</a>@auth<form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="logout-link">Log out <i class="bi bi-box-arrow-right ms-1"></i></button></form>@endauth</div></div></header>
<main class="portal-main"><div class="container">
@if(session('status'))<div class="alert portal-alert" role="status"><i class="bi bi-check-circle me-2"></i>{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert portal-errors" role="alert"><strong>Please check the details below.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')</div></main>
<footer class="portal-footer">
	<div class="container">
		<div class="portal-footer-main">
			<a class="portal-footer-brand" href="{{ route('home') }}">Hospital Sarthi<span>Here with you</span></a>
			<nav class="portal-footer-links" aria-label="Dashboard footer links">
				<a href="{{ route('home') }}">Home</a>
				@auth
					<a href="{{ route('dashboard') }}">My dashboard</a>
					@if(auth()->user()->role === 'admin')
						<a href="{{ route('admin.manage', 'users') }}">Users</a>
						<a href="{{ route('admin.manage', 'guides') }}">Guides</a>
						<a href="{{ route('admin.manage', 'hospitals') }}">Hospitals</a>
						<a href="{{ route('admin.manage', 'services') }}">Services</a>
						<a href="{{ route('admin.manage', 'bookings') }}">Bookings</a>
						<a href="{{ route('admin.manage', 'payments') }}">Payments</a>
						<a href="{{ route('admin.manage', 'reviews') }}">Reviews</a>
						<a href="{{ route('admin.manage', 'commission') }}">Commission</a>
					@elseif(auth()->user()->role === 'patient')
						<a href="{{ route('home') }}#find">Find a hospital</a>
					@endif
				@endauth
				<a href="mailto:hello@aapkasarthi.in">Contact</a>
			</nav>
		</div>
		<div class="portal-footer-bottom"><span>© {{ date('Y') }} Hospital Sarthi</span><span>Practical support only. Not medical care or advice.</span></div>
	</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>@stack('scripts')</body></html>
