<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Book a verified hospital guide for practical help navigating your visit.">
    <title>CuraGuide | Navigate with confidence</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/hero-home.css') }}" rel="stylesheet">
    <link href="{{ asset('css/home-footer.css') }}" rel="stylesheet">
    <link href="{{ asset('css/book-guide-modal.css') }}" rel="stylesheet">
</head>
<body class="cura-home" id="top">
    <header class="cura-header">
        <div class="cura-header-inner">
            <a href="{{ route('home') }}" class="cura-brand" aria-label="CuraGuide home">
                <span class="cura-brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 19 5v6c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V5l7-3Z"/><path d="m9.5 12 1.8 1.8 3.2-3.8"/></svg>
                </span>
                <span class="cura-brand-copy"><strong>CuraGuide</strong><small>NAVIGATE WITH CONFIDENCE</small></span>
            </a>

            <nav class="cura-nav" aria-label="Main navigation">
                <a href="#hospitals">Hospitals</a>
                <a href="#services">Services</a>
                <a href="#how-it-works">How it works</a>
              <a href="https://wa.me/919522104158?text=Hello%20Hospital%20Sarthi%2C%20I%20want%20to%20make%20a%20booking." target="_blank">
    Bookings
</a>

            </nav>

            <div class="cura-header-actions">
                @auth
                    <a class="cura-text-link" href="{{ route('dashboard') }}">My account</a>
                @else
                    <a class="cura-text-link" href="{{ route('login') }}">Log in</a>
                    <a class="cura-button cura-button-small" href="{{ route('register') }}">Get started</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        <section class="cura-hero">
            <div class="cura-hero-inner">
                <div class="cura-hero-copy">
                    <span class="cura-eyebrow"><span></span> Verified companions, human support</span>
                    <h1>Get personal assistance <span>inside the hospital.</span></h1>
                    <p class="cura-lede">Book a verified hospital guide to help you navigate registration, departments, tests, billing and the moments in between.</p>

                    <div class="cura-actions">
                        <button type="button" class="cura-button" x-data @click="$dispatch('open-book-guide-modal')">Book a guide
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </button>
                        <a class="cura-button cura-button-secondary" href="#hospitals">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                            Find a hospital
                        </a>
                    </div>

                    <div class="cura-proof">
                        <span class="cura-proof-item"><span class="cura-pulse" aria-hidden="true"></span>{{ $hospitals->count() }} hospitals listed</span>
                        <span class="cura-proof-divider" aria-hidden="true"></span>
                        <span class="cura-proof-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/></svg>Secure booking requests</span>
                    </div>
                </div>

                <div class="cura-hero-visual">
                    <img src="https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1400&q=85" alt="A bright hospital corridor with clear wayfinding" fetchpriority="high">
                    <div class="cura-image-caption">
                        <span class="cura-caption-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5.5 9v11h13V9M9 20v-6h6v6"/><path d="M12 5v4m-2-2h4"/></svg></span>
                        <span><strong>Your visit, made easier</strong><small>A familiar face for the visit ahead, from first counter to last.</small></span>
                        <svg class="cura-caption-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-label="Verified"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg>
                    </div>
                </div>
            </div>
        </section>

        <section class="cura-section cura-hospitals" id="hospitals">
            <div class="cura-section-heading">
                <div><span class="cura-eyebrow"><span></span> Find local support</span><h2>Hospitals near you</h2></div>
                <p>Choose the hospital for your visit and meet guides who know the way around.</p>
            </div>
            <div class="cura-hospital-list">
                @forelse($hospitals as $hospital)
                    <article class="cura-hospital-row">
                        <span class="cura-hospital-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V6l7-3 7 3v15M9 9h.01M15 9h.01M9 13h.01M15 13h.01M10 21v-4h4v4"/></svg></span>
                        <div class="cura-hospital-info"><strong>{{ $hospital->name }}</strong><span>{{ $hospital->city }} · {{ $hospital->address }}</span></div>
                        <a class="cura-row-link" href="{{ route('hospitals.guides', $hospital) }}">Meet the guides <span aria-hidden="true">→</span></a>
                    </article>
                @empty
                    <p class="cura-empty">Hospitals are being added to the directory. Please check back soon.</p>
                @endforelse
            </div>
        </section>

        <section class="cura-section cura-support" id="services">
            <div class="cura-section-heading">
                <div><span class="cura-eyebrow"><span></span> Practical, non-medical help</span><h2>Support for the steps in between</h2></div>
                <p>Guides help with hospital navigation and coordination. They do not provide medical advice or treatment.</p>
            </div>
            <div class="cura-service-list">
                <span>OPD registration</span><span>Tokens & queues</span><span>Tests and reports</span><span>Billing and pharmacy</span>
            </div>
        </section>

        <section class="cura-how" id="how-it-works">
            <div class="cura-section cura-how-inner">
                <span class="cura-eyebrow"><span></span> A simpler hospital visit</span>
                <div class="cura-steps"><div><b>01</b><strong>Choose your hospital</strong><span>Find the place you are visiting.</span></div><div><b>02</b><strong>Meet your guide</strong><span>Choose a verified local companion.</span></div><div><b>03</b><strong>Arrive with a plan</strong><span>Get practical support on the day.</span></div></div>
            </div>
        </section>

        <section class="cura-section cura-faq" id="faq">
            <span class="cura-eyebrow"><span></span> Good to know</span>
            <h2>Guides provide practical help, not medical care.</h2>
            <p>Your guide can help with wayfinding, registration, queues and other visit logistics. For clinical questions, speak with your care team.</p>
        </section>
    </main>

    <footer class="cura-footer">
        <div class="cura-footer-main">
            <a href="{{ route('home') }}" class="cura-footer-brand">CuraGuide<span>Navigate with confidence</span></a>
            <nav class="cura-footer-links" aria-label="Footer navigation">
                <a href="#hospitals">Hospitals</a>
                <a href="#services">Services</a>
                <a href="#how-it-works">How it works</a>
                <a href="#faq">FAQ</a>
                <a href="{{ route('login') }}">Log in</a>
                <a href="{{ route('register') }}">Create account</a>
                <a href="mailto:hello@hospitalsarthi.in">Contact</a>
            </nav>
        </div>
        <div class="cura-footer-bottom"><span>© {{ date('Y') }} CuraGuide · Here with you.</span><span>Practical support only. Not medical care or advice.</span><a href="#top">Back to top ↑</a></div>
    </footer>
    @include('components.book-guide-modal')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
    @stack('scripts')
</body>
</html>
