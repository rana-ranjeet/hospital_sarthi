{{--
    resources/views/components/book-guide-modal.blade.php

    Usage: include this once inside your layout (e.g. before </body>),
    then trigger it from any button with:
        x-on:click="$dispatch('open-book-guide-modal')"
    or simply:
        <button x-data @click="$dispatch('open-book-guide-modal')">Book a guide</button>

    Requires Alpine.js (https://alpinejs.dev) loaded on the page.
--}}
<div
    x-data="bookGuideModal()"
    x-on:open-book-guide-modal.window="open()"
    x-show="isOpen"
    x-cloak
    class="guide-book-modal-overlay fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4"
    style="display: none;"
>
    <div
        x-show="isOpen"
        x-on:click.outside="close()"
        x-transition
        class="guide-book-modal-panel relative flex max-h-[90vh] w-full max-w-md flex-col overflow-y-auto rounded-2xl bg-white shadow-xl"
    >
        {{-- Header --}}
        <div class="guide-book-modal-header sticky top-0 z-10 border-b border-slate-100 bg-white px-6 pb-4 pt-6">
            <div class="flex items-start justify-between">
                <p class="text-xs font-bold tracking-wide text-teal-600">BOOK A COMPANION</p>

                <div class="guide-book-modal-progress flex items-center gap-2">
                    <template x-for="n in 3" :key="n">
                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-full text-sm font-semibold"
                            :class="n === step
                                ? 'bg-teal-600 text-white'
                                : (n < step ? 'bg-teal-100 text-teal-700' : 'bg-slate-100 text-slate-400')"
                            x-text="n"
                        ></span>
                    </template>

                    <button
                        type="button"
                        x-on:click="close()"
                        class="ml-2 flex h-7 w-7 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                        aria-label="Close"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <h2 class="guide-book-modal-title mt-3 text-2xl font-bold leading-tight text-slate-900">Plan your hospital visit</h2>
            <p class="guide-book-modal-hospital text-sm text-slate-500" x-text="hospital.name"></p>
        </div>

        {{-- Body --}}
        <div class="guide-book-modal-body px-6 py-5">

            {{-- Step 1: service + date/time --}}
            <div class="guide-book-modal-step" x-show="step === 1" x-cloak>
                <label class="guide-book-modal-label mb-1.5 block text-sm font-semibold text-slate-900">What would you like help with?</label>
                <select
                    x-model="form.serviceId"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                >
                    <option value="" disabled>Select a service</option>
                    <template x-for="service in services" :key="service.id">
                        <option :value="service.id" x-text="service.label + ' · ₹' + service.price"></option>
                    </template>
                </select>

                <div class="guide-book-date-grid mt-5 grid grid-cols-2 gap-4">
                    <div>
                        <label class="guide-book-modal-label mb-1.5 block text-sm font-semibold text-slate-900">Preferred date</label>
                        <input
                            type="date"
                            x-model="form.date"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                        >
                    </div>
                    <div>
                        <label class="guide-book-modal-label mb-1.5 block text-sm font-semibold text-slate-900">Preferred time</label>
                        <input
                            type="time"
                            x-model="form.time"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                        >
                    </div>
                </div>

                <template x-if="selectedService">
                    <div class="guide-book-service-summary mt-5 flex items-start gap-3 rounded-xl bg-slate-50 p-4">
                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-100 text-teal-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="9" />
                                <path stroke-linecap="round" d="M12 8v4l2.5 2.5" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-slate-900" x-text="selectedService.label"></p>
                            <p class="mt-0.5 text-sm text-slate-500" x-text="selectedService.description"></p>
                        </div>
                    </div>
                </template>

                <p x-show="errors.step1" x-text="errors.step1" class="mt-3 text-sm text-red-600"></p>

                <button
                    type="button"
                    x-on:click="goToStep(2)"
                    class="guide-book-action mt-6 flex w-full items-center justify-center gap-2 rounded-full bg-teal-600 py-3 font-semibold text-white hover:bg-teal-700"
                >
                    See available guides
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </button>
            </div>

            {{-- Step 2: guide + patient details --}}
            <div class="guide-book-modal-step" x-show="step === 2" x-cloak>
                <div class="mb-1.5 flex items-baseline justify-between">
                    <label class="block text-sm font-semibold text-slate-900">Choose your guide</label>
                    <span class="text-xs font-medium text-slate-500" x-text="guides.length + ' available'"></span>
                </div>
                <p class="mb-3 text-xs text-slate-500">Verified companions available for this time</p>

                <div class="guide-book-guide-grid grid grid-cols-2 gap-3">
                    <template x-for="guide in guides" :key="guide.id">
                        <button
                            type="button"
                            x-on:click="form.guideId = guide.id"
                            class="guide-book-guide-card rounded-xl border p-3 text-left transition"
                            :class="form.guideId === guide.id ? 'border-teal-500 ring-1 ring-teal-500' : 'border-slate-200 hover:border-slate-300'"
                        >
                            <template x-if="guide.photo">
                                <img :src="guide.photo" :alt="guide.name" class="h-10 w-10 rounded-full object-cover">
                            </template>
                            <template x-if="!guide.photo">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-teal-100 text-sm font-semibold text-teal-700" x-text="guide.initials"></span>
                            </template>
                            <p class="mt-2 text-sm font-semibold text-slate-900" x-text="guide.name"></p>
                            <p class="text-xs text-slate-500">
                                <span x-text="guide.rating ? guide.rating + ' · ' : 'Verified · '"></span>
                                <span x-text="guide.years + ' years'"></span>
                            </p>
                            <p class="text-xs text-slate-400" x-text="guide.languages || 'Languages on request'"></p>
                        </button>
                    </template>
                </div>

                <div class="guide-book-date-grid mt-5 grid grid-cols-2 gap-4">
                    <div>
                        <label class="guide-book-modal-label mb-1.5 block text-sm font-semibold text-slate-900">Patient / family member name</label>
                        <input
                            type="text"
                            x-model="form.patientName"
                            placeholder="Full name"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                        >
                    </div>
                    <div>
                        <label class="guide-book-modal-label mb-1.5 block text-sm font-semibold text-slate-900">Mobile number</label>
                        <input
                            type="tel"
                            x-model="form.mobile"
                            placeholder="9876543210"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                        >
                    </div>
                </div>

                <label class="guide-book-modal-label mb-1.5 mt-5 block text-sm font-semibold text-slate-900">Anything your guide should know?</label>
                <textarea
                    x-model="form.note"
                    rows="3"
                    placeholder="Optional note, such as mobility support or a first-time visit"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                ></textarea>

                <p x-show="errors.step2" x-text="errors.step2" class="mt-3 text-sm text-red-600"></p>

                <div class="mt-6 flex gap-3">
                    <button
                        type="button"
                        x-on:click="step = 1"
                        class="guide-book-action guide-book-action-secondary flex items-center justify-center rounded-full border border-slate-300 px-5 py-3 font-semibold text-slate-700 hover:border-slate-400"
                    >
                        Back
                    </button>
                    <button
                        type="button"
                        x-on:click="goToStep(3)"
                        class="guide-book-action flex flex-1 items-center justify-center gap-2 rounded-full bg-teal-600 py-3 font-semibold text-white hover:bg-teal-700"
                    >
                        Review and pay
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Step 3: confirmation + payment --}}
            <div class="guide-book-modal-step" x-show="step === 3" x-cloak>
                <div class="flex items-start gap-3">
                    <img :src="selectedGuide?.photo" :alt="selectedGuide?.name" class="h-10 w-10 rounded-full object-cover">
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-slate-900" x-text="hospital.name"></p>
                        <p class="text-sm text-slate-500" x-text="selectedService?.label"></p>
                    </div>
                    <span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-700">
                        <span x-text="bookingStatus === 'success' ? 'Request sent' : 'Request pending'"></span>
                    </span>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-4 border-t border-slate-100 pt-5">
                    <div>
                        <p class="text-xs font-medium text-slate-500">Date &amp; time</p>
                        <p class="text-sm font-semibold text-slate-900" x-text="formattedDateTime"></p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500">Patient</p>
                        <p class="text-sm font-semibold text-slate-900" x-text="form.patientName"></p>
                    </div>
                </div>

                <div class="mt-5 rounded-xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="6" width="18" height="12" rx="2" />
                            <path stroke-linecap="round" d="M3 10h18" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Booking request</p>
                            <p class="text-sm text-slate-500">No payment is collected. The guide will respond to your request.</p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between border-t border-slate-200 pt-4">
                        <p class="text-sm font-medium text-slate-600">Service price</p>
                        <p class="text-lg font-bold text-slate-900" x-text="'₹' + (selectedService?.price ?? 0)"></p>
                    </div>
                </div>

                <div x-show="bookingStatus === 'success'" class="mt-3 rounded-lg bg-teal-50 p-3 text-sm text-teal-800" role="status">
                    <p x-text="bookingMessage"></p>
                    <p class="mt-1 font-semibold" x-text="'Booking ID: ' + bookingId"></p>
                </div>

                <p x-show="errors.step3" x-text="errors.step3" class="mt-3 text-sm text-red-600 text-center"></p>

                <button
                    type="button"
                    x-on:click="pay()"
                    :disabled="bookingStatus === 'processing' || bookingStatus === 'success'"
                    class="guide-book-action mt-5 flex w-full items-center justify-center gap-2 rounded-full bg-teal-600 py-3 font-semibold text-white hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-70"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                    </svg>
                    <span x-text="bookingStatus === 'processing' ? 'Sending your request…' : (bookingStatus === 'success' ? 'Request sent' : 'Send booking request')"></span>
                </button>

                <p class="mt-3 text-center text-xs text-slate-400">Payment status remains pending until a payment provider is connected.</p>
            </div>

        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            function bookGuideModal() {
                return {
                    isOpen: false,
                    step: 1,
                    bookingStatus: 'idle',
                    bookingId: null,
                    bookingMessage: '',
                    errors: {},

                    hospital: {
                        id: @json($modalHospital->id ?? ($hospital->id ?? null)),
                        name: @json($modalHospital->name ?? ($hospital->name ?? 'Hospital')),
                    },

                    services: @json($modalServices ?? []),
                    guides: @json($modalGuides ?? []),

                    form: {
                        serviceId: @json($modalServices[0]['id'] ?? ''),
                        date: '',
                        time: '10:00',
                        guideId: null,
                        patientName: '',
                        mobile: '',
                        note: '',
                    },

                    open() {
                        this.isOpen = true;
                        this.step = 1;
                        this.bookingStatus = 'idle';
                        this.bookingId = null;
                        this.bookingMessage = '';
                        this.errors = {};
                    },

                            if (!this.form.serviceId) {
                                this.errors.step1 = 'Select a service to continue.';
                                return;
                            }
                            if (!this.form.date || !this.form.time) {
                        this.isOpen = false;
                    },

                            if (!this.hospital.id) {
                                this.errors.step1 = 'No hospital with an available guide is ready for booking right now.';
                                return;
                            }
                    get selectedService() {
                        return this.services.find(s => String(s.id) === String(this.form.serviceId)) ?? null;
                    },

                    get selectedGuide() {
                        return this.guides.find(g => g.id === this.form.guideId) ?? null;
                    },

                    get formattedDateTime() {
                        if (!this.form.date) return '';
                        const d = new Date(this.form.date + 'T' + (this.form.time || '00:00'));
                        return d.toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' }) + ' · ' + (this.form.time || '');
                    },

                    goToStep(target) {
                        this.errors = {};

                        if (target === 2) {
                            if (!this.form.serviceId || !this.form.date || !this.form.time) {
                                this.errors.step1 = 'Choose a service, date and time to continue.';
                                return;
                            }
                        }

                        if (target === 3) {
                            if (!this.form.guideId) {
                                this.errors.step2 = 'Select a guide to continue.';
                                return;
                            }
                            if (!this.form.patientName || !this.form.mobile) {
                                this.errors.step2 = 'Add the patient name and mobile number to continue.';
                                return;
                            }
                        }

                        this.step = target;
                    },

                    async pay() {
                        if (this.bookingStatus === 'processing' || this.bookingStatus === 'success') return;

                        this.errors = {};
                        this.bookingStatus = 'processing';

                        try {
                            const response = await fetch("{{ route('bookings.store') }}", {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                                },
                                body: JSON.stringify({
                                    hospital_id: this.hospital.id,
                                    service_id: this.form.serviceId,
                                    guide_id: this.form.guideId,
                                    date: this.form.date,
                                    time: this.form.time,
                                    patient_name: this.form.patientName,
                                    mobile: this.form.mobile,
                                    note: this.form.note,
                                }),
                            });
                            const result = await response.json();

                            if (!response.ok || !result.success) {
                                const validationMessages = Object.values(result.errors ?? {}).flat();
                                throw new Error(validationMessages.join(' ') || result.message || 'We could not create that booking. Please try another time.');
                            }

                            this.bookingId = result.booking_id;
                            this.bookingMessage = result.message;
                            this.bookingStatus = 'success';
                        } catch (error) {
                            this.bookingStatus = 'failed';
                            this.errors.step3 = error.message || 'We could not create that booking. Please try another time.';
                        }
                    },
                };
            }
        </script>
    @endpush
@endonce
