@extends('portal')
@section('title', $mode === 'login' ? 'Log in' : 'Create an account')
@section('content')
<div class="auth-layout">
    <div class="auth-aside">
        <div class="eyebrow"><span></span> Here with you</div>
        <h1>{{ $mode === 'login' ? 'Good to have you back.' : 'A little help can change the whole day.' }}</h1>
        <p>{{ $mode === 'login' ? 'Your next visit, and the people supporting it, are right here.' : 'Create an account to request a guide, or join as a hospital companion.' }}</p>
        <div class="auth-aside-note"><i class="bi bi-shield-check"></i> Your account is private and protected.</div>
    </div>
    <div class="auth-form-panel">
        <div class="eyebrow"><span></span> {{ $mode === 'login' ? 'Welcome back' : 'Get started' }}</div>
        <h2>{{ $mode === 'login' ? 'Log in to your account' : '' }}</h2>
        <p class="auth-intro">{{ $mode === 'login' ? 'Enter your details to continue.' : 'It only takes a moment to get started.' }}</p>
        <form method="POST" action="{{ $mode === 'login' ? route('login.store') : route('register.store') }}">
            @csrf
            @if($mode === 'register')
                @php($selectedRoleValue = old('role', $selectedRole ?? 'patient'))
                <label class="form-label" for="name">Your name</label>
                <input class="form-control" id="name" name="name" value="{{ old('name') }}" autocomplete="name" required>
                @error('name')<small class="field-error">{{ $message }}</small>@enderror
                <fieldset class="role-picker">
                    <legend class="form-label">I’m joining as</legend>
                    <div class="role-options">
                        <label><input type="radio" name="role" value="patient" @checked($selectedRoleValue === 'patient')><span><i class="bi bi-person"></i> Patient or family</span></label>
                        <label><input type="radio" name="role" value="guide" @checked($selectedRoleValue === 'guide')><span><i class="bi bi-person-badge"></i> Hospital guide</span></label>
                    </div>
                </fieldset>
                <fieldset id="patient-registration-fields" @if($selectedRoleValue !== 'patient') disabled @endif>
                    <legend class="form-label mt-3">Patient details</legend>
                    @include('components.patient-profile-fields', ['patient' => null])
                </fieldset>
                <fieldset id="guide-registration-fields" class="mt-3" @if($selectedRoleValue !== 'guide') disabled @endif>
                    <legend class="form-label">Guide details</legend>
                    <label class="form-label" for="phone">Phone <span class="optional">(optional)</span></label>
                    <input class="form-control" id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel">
                    <label class="form-label mt-3" for="city">City</label>
                    <input class="form-control" id="city" name="city" value="{{ old('city') }}" autocomplete="address-level2" maxlength="120" required>
                    @error('city')<small class="field-error">{{ $message }}</small>@enderror
                </fieldset>
            @endif
            <label class="form-label mt-3" for="email">Email address</label>
            <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
            @error('email')<small class="field-error">{{ $message }}</small>@enderror
            <label class="form-label mt-3" for="password">Password</label>
            <input class="form-control" id="password" name="password" type="password" autocomplete="{{ $mode === 'login' ? 'current-password' : 'new-password' }}" required>
            @error('password')<small class="field-error">{{ $message }}</small>@enderror
            @if($mode === 'register')
                <label class="form-label mt-3" for="password_confirmation">Confirm password</label>
                <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            @else
                <label class="remember-row mt-3"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
            @endif
            <button class="btn btn-forest w-100 mt-4" type="submit">{{ $mode === 'login' ? 'Log in' : 'Create account' }} <i class="bi bi-arrow-right ms-2"></i></button>
        </form>
        <div class="google-auth-divider"><span>or</span></div>
        <a id="google-auth-link" class="google-auth-button" href="{{ route('auth.google.redirect', $mode === 'register' ? ['role' => ($selectedRole ?? 'patient')] : []) }}"><span class="google-auth-mark" aria-hidden="true">G</span>{{ $mode === 'login' ? 'Continue with Google' : 'Sign up with Google' }}</a>
        <div class="auth-switch">{{ $mode === 'login' ? 'New to Hospital Sarthi?' : 'Already have an account?' }} <a href="{{ $mode === 'login' ? route('register') : route('login') }}">{{ $mode === 'login' ? 'Create an account' : 'Log in' }}</a></div>
        <div class="auth-disclaimer"><i class="bi bi-info-circle me-1"></i> Guides provide practical, non-medical hospital support.</div>
    </div>
</div>
@if($mode === 'register')
    <script>
        const patientRegistrationFields = document.getElementById('patient-registration-fields');
        const guideRegistrationFields = document.getElementById('guide-registration-fields');
        document.querySelectorAll('input[name="role"]').forEach((roleInput) => {
            roleInput.addEventListener('change', () => {
                const isPatient = roleInput.value === 'patient' && roleInput.checked;
                patientRegistrationFields.disabled = !isPatient;
                guideRegistrationFields.disabled = isPatient;
                document.getElementById('google-auth-link').href = `{{ route('auth.google.redirect') }}?role=${roleInput.value}`;
            });
        });
    </script>
@endif
@endsection