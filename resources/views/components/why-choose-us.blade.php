{{--
    resources/views/components/why-choose-us.blade.php

    Usage:
        <x-why-choose-us />
        <x-why-choose-us :features="$features" />
--}}
@php
    $features = $features ?? [
        [
            'title' => 'Trained Guides',
            'description' => 'Well-trained and background verified Saathis.',
            'icon' => 'user',
        ],
        [
            'title' => 'Personal Assistance',
            'description' => 'Support at every step of your hospital visit.',
            'icon' => 'heart',
        ],
        [
            'title' => 'Time Saving',
            'description' => 'Less confusion, more efficiency.',
            'icon' => 'clock',
        ],
        [
            'title' => 'Privacy & Safety',
            'description' => 'Your information is handled responsibly.',
            'icon' => 'shield-check',
        ],
    ];
@endphp

<section class="bg-gradient-to-b from-sky-50 to-white p-5">
    <div class="mx-auto max-w-7xl px-6">

        {{-- Heading --}}
        <div class="mb-12 text-center">
            <h3 class="text-3xl font-extrabold text-slate-900 md:text-4xl">
                Why Choose <span class="text-emerald-600">Aapka Sarthi?</span>
            </h3>
            <p class="mt-3 text-base text-blue-900/70 md:text-lg">
                A real person. By your side. Hospital assistance when you need it.
            </p>
        </div>

        {{-- Features --}}
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4 lg:gap-0">
            @foreach ($features as $index => $feature)
                <div
                    class="flex flex-col items-center px-6 text-center
                           {{ $index > 0 ? 'lg:border-l lg:border-slate-200' : '' }}"
                >
                    <span class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-b from-sky-100 to-emerald-50 text-blue-700">
                        @switch($feature['icon'])
                            @case('user')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <circle cx="12" cy="8" r="3.5" />
                                    <path stroke-linecap="round" d="M5 20c0-3.6 3.1-6 7-6s7 2.4 7 6" />
                                </svg>
                                @break
                            @case('heart')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20s-7-4.3-9.5-8.7C1 8 2.5 5 5.7 5c1.8 0 3.1 1 4.3 2.5C11.2 6 12.5 5 14.3 5c3.2 0 4.7 3 3.2 6.3C15 15.7 12 20 12 20z" />
                                </svg>
                                @break
                            @case('clock')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <circle cx="12" cy="12" r="9" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3.5 2" />
                                </svg>
                                @break
                            @case('shield-check')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V6l7-3z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.5l2 2 4-4.5" />
                                </svg>
                                @break
                            @default
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <circle cx="12" cy="12" r="9" />
                                </svg>
                        @endswitch
                    </span>

                    <h3 class="mt-5 text-lg font-bold text-slate-900">{{ $feature['title'] }}</h3>
                    <p class="mt-2 max-w-[220px] text-sm leading-relaxed text-slate-500">
                        {{ $feature['description'] }}
                    </p>
                </div>
            @endforeach
        </div>

    </div>
</section>
