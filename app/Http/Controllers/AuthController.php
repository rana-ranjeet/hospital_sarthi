<?php

namespace App\Http\Controllers;

use App\Models\GuideProfile;
use App\Models\User;
use App\Rules\ValidMobileNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.form', ['mode' => 'login']);
    }

    public function showRegister(Request $request): View
    {
        return view('auth.form', ['mode' => 'register', 'selectedRole' => $request->query('role', 'patient')]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Those details did not match an account.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('home');
    }

    public function redirectToGoogle(Request $request): RedirectResponse
    {
        $clientId = trim((string) config('services.google.client_id'));
        $clientSecret = trim((string) config('services.google.client_secret'));
        if ($clientId === '' || $clientSecret === '' || strcasecmp($clientSecret, 'YOUR_CLIENT_SECRET') === 0) {
            return redirect()->route('login')->withErrors(['google' => 'Google sign-in needs the real OAuth Client ID and Client Secret from Google Cloud Console.']);
        }

        $role = $request->query('role', 'patient');
        $request->session()->put('google_signup_role', in_array($role, ['patient', 'guide'], true) ? $role : 'patient');

        return Socialite::driver('google')->with(['prompt' => 'select_account'])->redirect();
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('login')->withErrors(['google' => 'Google sign-in was cancelled or could not be completed.']);
        }

        $googleId = (string) $googleUser->getId();
        $email = strtolower((string) $googleUser->getEmail());
        $emailVerified = filter_var($googleUser->getRaw()['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($googleId === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || ! $emailVerified) {
            return redirect()->route('login')->withErrors(['google' => 'Google must provide a verified email address to sign in.']);
        }

        $user = User::query()->where('google_id', $googleId)->first();
        if (! $user) {
            $user = User::query()->where('email', $email)->first();
            if ($user && $user->google_id && $user->google_id !== $googleId) {
                return redirect()->route('login')->withErrors(['google' => 'This email is linked to a different Google account.']);
            }
        }

        if ($user?->blocked_at) {
            return redirect()->route('login')->withErrors(['google' => 'This account is currently blocked.']);
        }

        if (! $user) {
            $role = $request->session()->pull('google_signup_role', 'patient');
            $user = User::create([
                'name' => $googleUser->getName() ?: Str::before($email, '@'),
                'email' => $email,
                'password' => Str::random(64),
                'role' => in_array($role, ['patient', 'guide'], true) ? $role : 'patient',
                'google_id' => $googleId,
                'avatar_url' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
            ]);

            if ($user->role === 'guide') {
                GuideProfile::create(['user_id' => $user->id]);
            }
        } else {
            $user->forceFill([
                'google_id' => $googleId,
                'avatar_url' => $googleUser->getAvatar() ?: $user->avatar_url,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    public function register(Request $request): RedirectResponse
    {
        $countryCodes = array_keys(config('patient.country_calling_codes'));
        $mobileCountryCode = (string) $request->input('mobile_country_code', '+91');
        $alternateCountryCode = (string) $request->input('alternate_country_code', '+91');
        $details = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'in:patient,guide'],
            'city' => ['required_if:role,guide', 'nullable', 'string', 'max:120'],
            'mobile_country_code' => ['exclude_unless:role,patient', 'required', 'string', Rule::in($countryCodes)],
            'mobile_number' => ['exclude_unless:role,patient', 'required', 'string', 'regex:/^\d+$/', new ValidMobileNumber($mobileCountryCode)],
            'alternate_country_code' => ['exclude_unless:role,patient', 'required', 'string', Rule::in($countryCodes)],
            'alternate_mobile_number' => ['exclude_unless:role,patient', 'nullable', 'string', 'regex:/^\d+$/', new ValidMobileNumber($alternateCountryCode)],
            'age' => ['exclude_unless:role,patient', 'nullable', 'integer', 'between:0,120'],
            'gender' => ['exclude_unless:role,patient', 'nullable', Rule::in(config('patient.genders'))],
            'blood_group' => ['exclude_unless:role,patient', 'nullable', Rule::in(config('patient.blood_groups'))],
            'relationship_with_patient' => ['exclude_unless:role,patient', 'nullable', Rule::in(config('patient.relationships'))],
            'other_relationship' => ['exclude_unless:role,patient', 'nullable', 'required_if:relationship_with_patient,Other', 'string', 'max:120'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $city = $details['city'] ?? null;
        unset($details['city']);
        $user = User::create($details);

        if ($user->role === 'guide') {
            GuideProfile::create(['user_id' => $user->id, 'city' => $city]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'Your account is ready.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}