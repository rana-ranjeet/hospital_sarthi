{{--
    resources/views/components/reviews-slider.blade.php

    A testimonial/review carousel. Uses Alpine.js for the slide state and
    native CSS scroll-snap for smooth swiping/dragging (no external
    carousel library needed).

    Usage:
        <x-reviews-slider />
        <x-reviews-slider :reviews="$reviews" />
--}}
@php
    $reviews = $reviews ?? [
        [
            'name' => 'Priya Sharma',
            'role' => 'Patient, Aster Grove Hospital',
            'avatar' => 'https://i.pravatar.cc/80?img=32',
            'rating' => 5,
            'quote' => 'My Saathi met me right at the entrance and stayed with my mother through her whole OPD visit. It took away so much stress.',
        ],
        [
            'name' => 'Karan Mehta',
            'role' => 'Patient, City Care Hospital',
            'avatar' => 'https://i.pravatar.cc/80?img=15',
            'rating' => 5,
            'quote' => 'I was traveling from another city and had no idea how the hospital worked. Having someone guide me through billing and tests made all the difference.',
        ],
        [
            'name' => 'Anjali Desai',
            'role' => 'Family member, Sunrise Multispeciality',
            'avatar' => 'https://i.pravatar.cc/80?img=48',
            'rating' => 4,
            'quote' => 'Booking was simple and the guide was on time and genuinely helpful. Would definitely use this again for my parents.',
        ],
        [
            'name' => 'Ravi Gupta',
            'role' => 'Patient, Lotus Health Centre',
            'avatar' => 'https://i.pravatar.cc/80?img=60',
            'rating' => 5,
            'quote' => 'The discharge process is usually so confusing. My companion handled the paperwork and billing counters while I just had to relax.',
        ],
        [
            'name' => 'Meera Nair',
            'role' => 'Patient, Aster Grove Hospital',
            'avatar' => 'https://i.pravatar.cc/80?img=26',
            'rating' => 5,
            'quote' => 'Transparent pricing, no surprises, and a genuinely kind guide. Exactly what I needed for a first-time hospital visit.',
        ],
    ];
@endphp

<section
    class="bg-slate-50 py-20"
    x-data="{
        index: 0,
        perView: 1,
        total: {{ count($reviews) }},
        setPerView() {
            this.perView = window.innerWidth >= 1024 ? 3 : (window.innerWidth >= 640 ? 2 : 1);
            if (this.index > this.total - this.perView) this.index = Math.max(0, this.total - this.perView);
        },
        get maxIndex() { return Math.max(0, this.total - this.perView); },
        next() { this.index = Math.min(this.index + 1, this.maxIndex); this.scrollToIndex(); },
        prev() { this.index = Math.max(this.index - 1, 0); this.scrollToIndex(); },
        goTo(i) { this.index = Math.min(Math.max(i, 0), this.maxIndex); this.scrollToIndex(); },
        scrollToIndex() {
            const track = $refs.track;
            const card = track.children[0];
            if (!card) return;
            const gap = parseFloat(getComputedStyle(track).columnGap || 0);
            track.scrollTo({ left: this.index * (card.offsetWidth + gap), behavior: 'smooth' });
        }
    }"
    x-init="setPerView(); window.addEventListener('resize', () => setPerView())"
>
    <div class="mx-auto max-w-7xl px-6">

        {{-- Heading + arrows --}}
        <div class="mb-10 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold tracking-wide text-teal-600">TESTIMONIALS</p>
                <h2 class="mt-2 text-3xl font-extrabold text-slate-900 md:text-4xl">What people are saying</h2>
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="button"
                    x-on:click="prev()"
                    :disabled="index === 0"
                    class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 text-slate-600 hover:border-slate-400 disabled:cursor-not-allowed disabled:opacity-40"
                    aria-label="Previous reviews"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button
                    type="button"
                    x-on:click="next()"
                    :disabled="index >= maxIndex"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-teal-600 text-white hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-40"
                    aria-label="Next reviews"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Slider track --}}
        <div
            x-ref="track"
            class="flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth pb-2"
            style="scrollbar-width: none; -ms-overflow-style: none;"
        >
            @foreach ($reviews as $review)
                <div class="w-full shrink-0 snap-start sm:w-[calc(50%-0.75rem)] lg:w-[calc(33.333%-1rem)]">
                    <div class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">

                        {{-- Stars --}}
                        <div class="flex items-center gap-1 text-amber-400">
                            @for ($i = 0; $i < 5; $i++)
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="{{ $i < $review['rating'] ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1">
                                    <path d="M10 1.5l2.6 5.3 5.8.8-4.2 4.1 1 5.8L10 14.8l-5.2 2.7 1-5.8-4.2-4.1 5.8-.8L10 1.5z" />
                                </svg>
                            @endfor
                        </div>

                        {{-- Quote --}}
                        <p class="mt-4 flex-1 text-sm leading-relaxed text-slate-600">
                            &ldquo;{{ $review['quote'] }}&rdquo;
                        </p>

                        {{-- Author --}}
                        <div class="mt-6 flex items-center gap-3 border-t border-slate-100 pt-5">
                            <img src="{{ $review['avatar'] }}" alt="{{ $review['name'] }}" class="h-11 w-11 rounded-full object-cover">
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ $review['name'] }}</p>
                                <p class="text-xs text-slate-500">{{ $review['role'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Dots --}}
        <div class="mt-8 flex items-center justify-center gap-2">
            <template x-for="i in maxIndex + 1" :key="i">
                <button
                    type="button"
                    x-on:click="goTo(i - 1)"
                    class="h-2 rounded-full transition-all"
                    :class="index === (i - 1) ? 'w-6 bg-teal-600' : 'w-2 bg-slate-300'"
                    :aria-label="'Go to slide ' + i"
                ></button>
            </template>
        </div>

    </div>
</section>

<style>
    /* Hide scrollbar for the review track (Chrome/Safari) */
    section [x-ref="track"]::-webkit-scrollbar { display: none; }
</style>
