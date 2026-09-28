{{--
    resources/views/components/trust-faq-cta.blade.php

    Three stacked sections:
      1. Trust & Safety strip
      2. Frequently Asked Questions (accordion, needs Alpine.js)
      3. Bottom "Don't Navigate Alone" CTA banner

    Usage:
        <x-trust-faq-cta />
        <x-trust-faq-cta :trust="$trust" :faqs="$faqs" />

    The "Book a Sarthi" button dispatches 'open-book-guide-modal', matching
    the booking modal built earlier (resources/views/components/book-guide-modal.blade.php).
--}}
@php
    $trust = $trust ?? [
        ['title' => 'Verified Guides', 'description' => 'Background checked & trained', 'icon' => 'calendar-check'],
        ['title' => 'Privacy', 'description' => 'Your information is secure', 'icon' => 'shield-check'],
        ['title' => 'Transparent Pricing', 'description' => 'Know the cost before you book', 'icon' => 'check-circle'],
        ['title' => 'No Hidden Charges', 'description' => 'Clear communication at every step', 'icon' => 'shield-check'],
    ];

    $faqs = $faqs ?? [
        ['question' => 'Is Aapka Sarthi a medical service?', 'answer' => 'No. Our companions provide non-medical, practical support for navigating the hospital — they do not diagnose, treat or give medical advice.'],
        ['question' => 'Can I book for my parents?', 'answer' => 'Yes, you can book a Sarthi for any family member. Just add their details as the patient during booking.'],
        ['question' => 'Will the Saathi stay with me?', 'answer' => 'Yes, your Saathi stays with you for the full duration of the service you booked, from arrival to completion.'],
        ['question' => 'Can I book before travelling to another city?', 'answer' => 'Yes, you can book in advance for any of our partner hospitals before you arrive in the city.'],
        ['question' => 'What if I need to cancel my booking?', 'answer' => 'You can cancel or reschedule your booking from your dashboard up to a few hours before the appointment time.'],
        ['question' => 'How much does it cost?', 'answer' => 'Pricing depends on the service you choose and is always shown upfront before you confirm and pay.'],
    ];
@endphp

{{-- 1. Trust & Safety strip --}}
<section class="bg-sky-50 py-8">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-x-10 gap-y-6 divide-slate-200 px-6 md:flex-nowrap md:divide-x">
        <div class="pr-8">
            <h3 class="text-xl font-extrabold text-slate-900">Trust &amp; Safety</h3>
            <p class="mt-1 text-sm text-slate-500">Because your peace of mind matters.</p>
        </div>

        @foreach ($trust as $item)
            <div class="flex items-start gap-3 pl-0 md:pl-8">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                    @switch($item['icon'])
                        @case('calendar-check')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <rect x="4" y="5" width="16" height="15" rx="2" />
                                <path stroke-linecap="round" d="M8 3v4M16 3v4M4 10h16" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l2 2 4-4" />
                            </svg>
                            @break
                        @case('check-circle')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <circle cx="12" cy="12" r="9" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 12.5l2.3 2.3L16 10" />
                            </svg>
                            @break
                        @default
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V6l7-3z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.5l2 2 4-4.5" />
                            </svg>
                    @endswitch
                </span>
                <div>
                    <p class="text-sm font-bold text-slate-900">{{ $item['title'] }}</p>
                    <p class="text-xs leading-snug text-slate-500">{{ $item['description'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>

{{-- 2. FAQ accordion --}}
<section class="bg-sky-50 pb-14">
    <div class="mx-auto max-w-7xl px-6">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900">Frequently Asked Questions</h2>
                <p class="mt-1 text-sm text-slate-500">Got questions? We've got answers.</p>
            </div>
            {{-- <a href="{{ route('faqs') }}" --}}
               class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-white px-5 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50">
                View All FAQs
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2" x-data="{ open: null }">
            @foreach ($faqs as $index => $faq) 
                <div class="rounded-xl border border-slate-200 bg-white">
                    <button
                        type="button"
                        x-on:click="open = open === {{ $index }} ? null : {{ $index }}"
                        class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left text-sm font-semibold text-slate-800"
                    >
                        <span>{{ $faq['question'] }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-blue-600 transition-transform"
                             :class="open === {{ $index }} ? 'rotate-45' : ''"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                    <div x-show="open === {{ $index }}" x-collapse x-cloak>
                        <p class="px-5 pb-4 text-sm leading-relaxed text-slate-500">{{ $faq['answer'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- 3. Bottom CTA banner --}}
<section class="bg-gradient-to-r from-emerald-700 via-sky-800 to-blue-900 py-8">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-6 px-6">
        <div class="flex items-center gap-5">
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <circle cx="9" cy="8" r="3" />
                    <path stroke-linecap="round" d="M3.5 19c0-3 2.5-5.5 5.5-5.5s5.5 2.5 5.5 5.5" />
                    <circle cx="17" cy="9" r="2.3" />
                    <path stroke-linecap="round" d="M15 19c0-2.2 1-4 3-4.8" />
                </svg>
            </span>
            <div>
                <h3 class="text-xl font-extrabold text-white">Don't Navigate Alone.</h3>
                <p class="mt-1 text-sm text-sky-100">Book a Sarthi and get the support you need for your hospital visit.</p>
            </div>
        </div>

        <button
            type="button"
            x-data
            x-on:click="$dispatch('open-book-guide-modal')"
            class="inline-flex shrink-0 items-center gap-2 rounded-full bg-emerald-500 px-6 py-3 font-semibold text-white hover:bg-emerald-600"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <rect x="4" y="5" width="16" height="15" rx="2" />
                <path stroke-linecap="round" d="M8 3v4M16 3v4M4 10h16" />
            </svg>
            Book a Sarthi
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
        </button>
    </div>
</section>
