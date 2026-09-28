<!doctype html>
<html lang="en">
<head>
     <script src="https://cdn.tailwindcss.com"></script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Book a verified hospital guide for practical help navigating your visit.">
    <title>AapkaSarthi | Navigate with confidence</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/hero-home.css') }}" rel="stylesheet">
    <link href="{{ asset('css/account-profile.css') }}" rel="stylesheet">
    <link href="{{ asset('css/stats-bar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/services-section.css') }}" rel="stylesheet">
    <link href="{{ asset('css/home-footer.css') }}" rel="stylesheet">
    <link href="{{ asset('css/book-guide-modal.css') }}" rel="stylesheet">
    <link href="{{ asset('css/home-compact.css') }}" rel="stylesheet">
</head>
<body class="cura-home" id="top">
    <header class="cura-header">
        <div class="cura-header-inner">
            <a href="{{ route('home') }}" class="cura-brand" aria-label="AapkaSarthi home">
                <span class="cura-brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 19 5v6c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V5l7-3Z"/><path d="m9.5 12 1.8 1.8 3.2-3.8"/></svg>
                </span>
                <span class="cura-brand-copy"><strong>AapkaSarthi</strong><small>NAVIGATE WITH CONFIDENCE</small></span>
            </a>

            <nav class="cura-nav" aria-label="Main navigation">
                <a href="#hospitals">Hospitals</a>
                <a href="#services">Services</a>
                <a href="#how-it-works">How it works</a>
              <a href="https://wa.me/919522104158?text=Hello%20Hospital%20Sarthi%2C%20I%20want%20to%20make%20a%20booking." target="_blank">
    contact us
</a>

            </nav>

            <div class="cura-header-actions">
                @auth
                    <a class="account-profile-link" href="{{ route('dashboard') }}" aria-label="Profile: {{ auth()->user()->name }}" title="{{ auth()->user()->name }}">
                        <span class="account-profile-avatar">
                            @if(auth()->user()->avatar_url)<img src="{{ auth()->user()->avatar_url }}" alt="">@else<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path stroke-linecap="round" d="M4.5 20a7.5 7.5 0 0115 0"/></svg>@endif
                        </span>
                        <span class="account-profile-name">{{ auth()->user()->name }}</span>
                    </a>
                @else
                    <a class="cura-button cura-button-small" href="{{ route('login') }}">Log in</a>
                    <a class="cura-button cura-button-small" href="{{ route('register') }}">Sign Up</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        <section class="cura-hero">
            <div class="cura-hero-inner">
                <div class="cura-hero-copy">
                    <span class="cura-eyebrow"><span></span> Verified companions, human support</span>
                    <h1>Get personal assistance <span> At the hospital.</span></h1>
                    <p class="cura-lede">Book a verified hospital guide to help you navigate registration, departments, tests, billing and the moments in between.</p>

                    <div class="cura-actions">
                        <button type="button" class="cura-button" x-data @click="$dispatch('open-book-guide-modal')">Book a guide
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </button>
                        <a class="cura-button cura-button-secondary" href="tel:+919522104158">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                            Contact us
                        </a>
                    </div>

                    <div class="cura-proof">
                        <span class="cura-proof-item"><span class="cura-pulse" aria-hidden="true"></span>{{ $hospitals->count() }} hospitals listed</span>
                        <span class="cura-proof-divider" aria-hidden="true"></span>
                        <span class="cura-proof-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/></svg>Secure booking requests</span>
                    </div>
                    <p class="sr-only">A familiar face</p>
                    <p class="sr-only">A familiar face for the hospital visit ahead.</p>
                </div>

                <div class="cura-hero-visual">
                    <img src="https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1400&q=85" alt="A bright hospital corridor with clear wayfinding" fetchpriority="high">
                    <div class="cura-image-caption">
                        <span class="cura-caption-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5.5 9v11h13V9M9 20v-6h6v6"/><path d="M12 5v4m-2-2h4"/></svg></span>
                        <span><strong>Your visit, made easier</strong><small>A familiar face for the visit ahead, from first counter to last.</small></span>
                        <svg class="cura-caption-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-label="Verified"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg>
                    </div>
                    <div class="sr-only">A familiar face</div>
                </div>
            </div>
        </section>
    @include('components.why-choose-us')
@include('components.stats-bar')
@include('components.services-section')
@include('components.how-it-works')
@include('components.reviews-slider')
@include('components.trust-faq-cta')
        

        

        

        {{-- <section class="cura-section cura-faq" id="faq">
            <span class="cura-eyebrow"><span></span> Good to know</span>
            <h2>Guides provide practical help, not medical care.</h2>
            <p>Your guide can help with wayfinding, registration, queues and other visit logistics. For clinical questions, speak with your care team.</p>
            <p class="sr-only">Not medical care or advice.</p>
            <p class="sr-only">Not medical care or advice.</p>
        </section> --}}
    </main>

    <footer class="cura-footer">
        <div class="cura-footer-main">
            <a href="{{ route('home') }}" class="cura-footer-brand">AapkaSarthi<span>Navigate with confidence</span></a>
            <nav class="cura-footer-links" aria-label="Footer navigation">
                <a href="#hospitals">Hospitals</a>
                <a href="#services">Services</a>
                <a href="#how-it-works">How it works</a>
                <a href="#faq">FAQ</a>
                <a href="{{ route('login') }}">Log in</a>
                <a href="{{ route('register') }}">Create account</a>
                <a href="mailto:hello@aapkasarthi.in">Contact</a>
            </nav>
        </div>
        <div class="cura-footer-bottom"><span>© {{ date('Y') }} AapkaSarthi · Here with you.</span><span>Practical support only. Not medical care or advice.</span><a href="#top">Back to top ↑</a></div>
    </footer>
    @include('components.book-guide-modal')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
    @stack('scripts')
</body>
</html>
