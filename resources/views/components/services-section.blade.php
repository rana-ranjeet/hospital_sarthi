{{--
    resources/views/components/services-section.blade.php

    Usage:
        <x-services-section :services="$services" />

    If $services is not passed, default services are used.
--}}

@php
    $services = $services ?? [

        [
            'title' => 'OPD Registration Assistance',
            'description' => 'Perfect for patients who only need help getting started at the hospital.',
            'features' => [
                'OPD registration assistance',
                'Token/queue guidance',
                'Help locating the correct department',
                'Guidance inside the hospital',
            ],
            'duration' => '60 min',
            'price' => 299,
            'icon' => 'clipboard-check',
        ],

        [
            'title' => 'OPD + Doctor Assistance',
            'description' => 'For patients who need support from registration until their doctor consultation.',
            'features' => [
                'OPD registration',
                'Token & queue assistance',
                'Department navigation',
                "Assistance reaching the doctor's room",
                'Non-medical assistance during the hospital visit',
            ],
            'duration' => '90 min',
            'price' => 399,
            'icon' => 'stethoscope',
        ],

        [
            'title' => 'OPD + Doctor + Lab Assistance',
            'description' => 'For patients who need assistance with their consultation and laboratory process.',
            'features' => [
                'OPD registration',
                'Token & queue assistance',
                'Doctor consultation navigation',
                'Lab/sample collection guidance',
                'Lab billing assistance',
                'Report collection guidance',
            ],
            'duration' => '120 min',
            'price' => 449,
            'icon' => 'file-check',
        ],

        [
            'title' => 'Complete Hospital Assistance',
            'description' => 'Our comprehensive OPD assistance package for patients who need help throughout multiple hospital processes.',
            'features' => [
                'OPD registration',
                'Token & queue assistance',
                'Doctor consultation assistance',
                'Lab/sample collection assistance',
                'Lab billing assistance',
                'X-Ray assistance',
                'Ultrasound assistance',
                'Department-to-department navigation',
                'General non-medical hospital coordination',
            ],
            'duration' => '180 min',
            'price' => 699,
            'icon' => 'hospital',
        ],

        [
            'title' => 'Hourly Assistance',
            'description' => 'Need assistance only for a specific period? Book a companion on an hourly basis.',
            'features' => [
                'Registration',
                'Doctor visits',
                'Tests & diagnostics',
                'Billing',
                'Reports',
                'Pharmacy',
                'Hospital navigation',
                'General non-medical assistance',
            ],
            'duration' => 'Per Hour',
            'price' => 119,
            'icon' => 'clock',
        ],

        [
            'title' => 'Full-Day Assistance',
            'description' => 'For patients who need a companion for most or all of their hospital visit.',
            'features' => [
                'OPD registration',
                'Doctor consultation assistance',
                'Tests & diagnostics assistance',
                'X-Ray & ultrasound navigation',
                'Billing assistance',
                'Pharmacy assistance',
                'Report collection guidance',
                'Department navigation',
                'General non-medical patient support',
            ],
            'duration' => 'Full Day',
            'price' => 999,
            'icon' => 'user-round-check',
        ],

    ];
@endphp


<section class="cura-services" aria-labelledby="cura-services-title">

    <div class="cura-services-inner">

        <header class="cura-services-heading">

            <div>
                <p class="cura-services-eyebrow">
                    YOUR HOSPITAL JOURNEY, SIMPLIFIED
                </p>

                <h2 id="cura-services-title">
                    A helping hand when you need it most.
                </h2>
            </div>

            <p class="cura-services-intro">
                From registration and doctor visits to tests, billing and reports,
                our companions stay by your side and make every step of your hospital visit easier and less stressful.
            </p>

        </header>


        <div class="cura-services-grid">

            @foreach ($services as $index => $service)

                <article class="cura-service-card">

                    {{-- Icon + Number --}}
                    <div class="cura-service-card-top">

                        <span class="cura-service-icon">

                            @switch($service['icon'])

                                @case('clipboard-check')
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="1.75"
                                         aria-hidden="true">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M9 5h6M9 3h6a1 1 0 011 1v2H8V4a1 1 0 011-1z" />
                                        <rect x="5" y="5" width="14" height="16" rx="2" />
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M9 12l2 2 4-4" />
                                    </svg>
                                    @break

                                @case('file-check')
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="1.75"
                                         aria-hidden="true">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M8 3h6l4 4v13a1 1 0 01-1 1H8a1 1 0 01-1-1V4a1 1 0 011-1z" />
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M9.5 13.5l1.5 1.5 3-3" />
                                    </svg>
                                    @break

                                @case('stethoscope')
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="1.75"
                                         aria-hidden="true">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M6 4v5a4 4 0 008 0V4" />
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M10 15v1a4 4 0 004 4 4 4 0 004-4v-1" />
                                        <circle cx="19" cy="9" r="1.5" />
                                    </svg>
                                    @break

                                @case('hospital')
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="1.75"
                                         aria-hidden="true">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M4 21V5a1 1 0 011-1h14a1 1 0 011 1v16" />
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M9 8h6M12 5v6M8 21v-4h8v4M7 12h2M15 12h2" />
                                    </svg>
                                    @break

                                @case('clock')
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="1.75"
                                         aria-hidden="true">
                                        <circle cx="12" cy="12" r="9" />
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M12 7v5l3 2" />
                                    </svg>
                                    @break

                                @case('user-round-check')
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="1.75"
                                         aria-hidden="true">
                                        <circle cx="9" cy="8" r="3" />
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M3 20a6 6 0 0112 0" />
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M16 16l2 2 4-4" />
                                    </svg>
                                    @break

                                @default
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="1.75"
                                         aria-hidden="true">
                                        <circle cx="12" cy="12" r="9" />
                                        <path stroke-linecap="round"
                                              d="M12 8v4l3 2" />
                                    </svg>

                            @endswitch

                        </span>


                        <span class="cura-service-number">
                            {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                        </span>

                    </div>


                    {{-- Title --}}
                    <h3 class="cura-service-title">
                        {{ $service['title'] }}
                        <span class="service-price">
                            — ₹{{ number_format($service['price']) }}
                        </span>
                    </h3>


                    {{-- Description --}}
                    <p class="cura-service-description">
                        {{ $service['description'] }}
                    </p>


                    {{-- Includes --}}
                    <div class="cura-service-includes">

                        <h4>Includes:</h4>

                        <ul class="cura-service-features">

                            @foreach ($service['features'] as $feature)

                                <li>
                                    <span class="feature-bullet">•</span>
                                    <span>{{ $feature }}</span>
                                </li>

                            @endforeach

                        </ul>

                    </div>


                    {{-- Duration + Booking --}}
                    <div class="cura-service-meta">

                        <span class="cura-service-duration">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="1.75"
                                 aria-hidden="true">
                                <circle cx="12" cy="12" r="9" />
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M12 7v5l3 2" />
                            </svg>

                            {{ $service['duration'] }}

                        </span>


                        <button
                            type="button"
                            @click="$dispatch('open-book-guide-modal')"
                            class="cura-service-book"
                        >
                            Book Now
                    </button>

                    </div>

                </article>

            @endforeach


            {{-- Custom Assistance Card --}}

            <article class="cura-service-card cura-custom-service">

                <div class="cura-service-card-top">

                    <span class="cura-service-icon">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="1.75"
                             aria-hidden="true">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M21 11.5a8.4 8.4 0 01-9 8.5 9.4 9.4 0 01-4-.9L3 20l1.2-4.4A8.4 8.4 0 013 11.5a9 9 0 1118 0z" />
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M8 12h.01M12 12h.01M16 12h.01" />
                        </svg>

                    </span>

                </div>


                <h3 class="cura-service-title">
                    Need something different?
                </h3>


                <p class="cura-service-description">
                    Tell us what assistance you need, and we'll help you choose
                    the right plan.
                </p>


                <a
                    href="https://wa.me/919522104158?text={{ urlencode('Hello Hospital Sarthi, I need help choosing the right patient assistance plan.') }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="cura-service-book"
                >
                    Book a Companion
                </a>

            </article>

        </div>

    </div>

</section>


<style>

.cura-service-title {
    color: #183b2b;
}

.service-price {
    color: #198754;
    font-weight: 700;
    white-space: nowrap;
}

.cura-service-includes {
    margin-top: 20px;
}

.cura-service-includes h4 {
    margin: 0 0 12px;
    color: #183b2b;
    font-size: 16px;
    font-weight: 700;
}

.cura-service-features {
    list-style: none;
    margin: 0;
    padding: 0;
}

.cura-service-features li {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 8px;
    color: #4d5c55;
    font-size: 14px;
    line-height: 1.55;
}

.feature-bullet {
    flex-shrink: 0;
    color: #198754;
    font-weight: 700;
    font-size: 17px;
    line-height: 1.35;
}

.cura-service-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-top: 22px;
    padding-top: 18px;
    border-top: 1px solid #e8eeeb;
}

.cura-service-duration {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #66736d;
    font-size: 14px;
    font-weight: 600;
}

.cura-service-duration svg {
    width: 18px;
    height: 18px;
    color: #198754;
}

.cura-service-book {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 20px;
    border-radius: 8px;
    background: #198754;
    color: #fff !important;
    text-decoration: none !important;
    font-size: 14px;
    font-weight: 700;
    transition: all 0.2s ease;
}

.cura-service-book:hover {
    background: #146c43;
    color: #fff !important;
    transform: translateY(-1px);
}

.cura-custom-service {
    text-align: center;
}

.cura-custom-service .cura-service-card-top {
    justify-content: center;
}

.cura-custom-service .cura-service-description {
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.cura-custom-service .cura-service-book {
    margin-top: 10px;
}

@media (max-width: 768px) {

    .cura-service-title {
        font-size: 20px;
        line-height: 1.4;
    }

    .service-price {
        display: inline-block;
    }

    .cura-service-meta {
        flex-direction: column;
        align-items: stretch;
    }

    .cura-service-book {
        width: 100%;
    }

}

</style>

{{-- =========================
     HOW IT WORKS SECTION
========================= --}}

