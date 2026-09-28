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
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="guide-book-modal-label mb-1.5 block text-sm font-semibold text-slate-900">City</label>
                        <select x-model="selectedCity" x-on:change="form.hospitalId = ''; guides = []; form.guideId = null" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                            <option value="">Select a city</option>
                            <template x-for="city in cities" :key="city"><option :value="city" x-text="city"></option></template>
                        </select>
                    </div>
                    <div>
                        <label class="guide-book-modal-label mb-1.5 block text-sm font-semibold text-slate-900">Hospital</label>
                        <select x-model="form.hospitalId" x-on:change="guides = []; form.guideId = null" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500" :disabled="!selectedCity || filteredHospitals.length === 0">
                            <option value="">Select a hospital</option>
                            <template x-for="hospitalOption in filteredHospitals" :key="hospitalOption.id"><option :value="hospitalOption.id" x-text="hospitalOption.name"></option></template>
                        </select>
                        <p x-show="selectedCity && filteredHospitals.length === 0" class="mt-1 text-xs text-slate-500">No active hospitals listed in this city yet.</p>
                    </div>
                </div>

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
                    <span class="text-xs font-medium text-slate-500" x-text="guidesLoading ? 'Checking availability…' : guides.length + ' available'"></span>
                </div>
                <p class="mb-3 text-xs text-slate-500">Verified companions available for this time</p>
                <p x-show="guideSearchMessage" x-text="guideSearchMessage" class="mb-3 text-sm text-slate-600" role="status"></p>

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

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="guide-book-modal-label mb-1.5 block text-sm font-semibold text-slate-900" for="booking-patient-name">Patient name</label>
                        <input id="booking-patient-name" type="text" x-model="form.patientName" maxlength="120" placeholder="Full name" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="guide-book-modal-label mb-1.5 block text-sm font-semibold text-slate-900" for="booking-mobile-number">Mobile number</label>
                        <div class="flex gap-2">
                            @include('components.country-code-select', ['name' => 'mobile_country_code', 'id' => 'booking-mobile-country-code', 'xModel' => 'form.mobileCountryCode', 'class' => 'w-40 shrink-0 rounded-lg border border-slate-300 px-2 py-2.5 text-sm text-slate-800'])
                            <input id="booking-mobile-number" type="tel" inputmode="numeric" pattern="[0-9]*" maxlength="15" data-digits-only x-model="form.mobileNumber" placeholder="Mobile number" class="min-w-0 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                        </div>
                    </div>
                    <div>
                        <label class="guide-book-modal-label mb-1.5 block text-sm font-semibold text-slate-900" for="booking-alternate-mobile-number">Alternative mobile <span class="font-normal text-slate-500">(optional)</span></label>
                        <div class="flex gap-2">
                            @include('components.country-code-select', ['name' => 'alternate_country_code', 'id' => 'booking-alternate-country-code', 'xModel' => 'form.alternateCountryCode', 'class' => 'w-40 shrink-0 rounded-lg border border-slate-300 px-2 py-2.5 text-sm text-slate-800'])
                            <input id="booking-alternate-mobile-number" type="tel" inputmode="numeric" pattern="[0-9]*" maxlength="15" data-digits-only x-model="form.alternateMobileNumber" placeholder="Alternative number" class="min-w-0 w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                        </div>
                    </div>
                    <div>
                        <label class="guide-book-modal-label mb-1.5 block text-sm font-semibold text-slate-900" for="booking-age">Age <span class="font-normal text-slate-500">(optional)</span></label>
                        <input id="booking-age" type="number" inputmode="numeric" min="0" max="120" step="1" x-model="form.age" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="guide-book-modal-label mb-1.5 block text-sm font-semibold text-slate-900" for="booking-gender">Gender <span class="font-normal text-slate-500">(optional)</span></label>
                        <select id="booking-gender" x-model="form.gender" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                            <option value="">Select gender</option>
                            @foreach(config('patient.genders') as $gender)<option value="{{ $gender }}">{{ $gender }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="guide-book-modal-label mb-1.5 block text-sm font-semibold text-slate-900" for="booking-blood-group">Blood group <span class="font-normal text-slate-500">(optional)</span></label>
                        <select id="booking-blood-group" x-model="form.bloodGroup" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                            <option value="">Select blood group</option>
                            @foreach(config('patient.blood_groups') as $bloodGroup)<option value="{{ $bloodGroup }}">{{ $bloodGroup }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="guide-book-modal-label mb-1.5 block text-sm font-semibold text-slate-900" for="booking-relationship">Relationship with Patient</label>
                        <select id="booking-relationship" x-model="form.relationshipWithPatient" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                            @foreach(config('patient.relationships') as $relationship)<option value="{{ $relationship }}">{{ $relationship }}</option>@endforeach
                        </select>
                    </div>
                    <div x-show="form.relationshipWithPatient === 'Other'" x-cloak>
                        <label class="guide-book-modal-label mb-1.5 block text-sm font-semibold text-slate-900" for="booking-other-relationship">Please specify relationship</label>
                        <input id="booking-other-relationship" type="text" maxlength="120" x-model="form.otherRelationship" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
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
                <a
                    x-show="needsLogin"
                    href="{{ route('login') }}"
                    class="mt-2 block text-center text-sm font-semibold text-teal-700 underline"
                >Log in with a patient account</a>

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
        @php
            $patientDefaults = auth()->check() && auth()->user()->role === 'patient' ? [
                'patientName' => auth()->user()->name,
                'mobileCountryCode' => auth()->user()->mobile_country_code ?: '+91',
                'mobileNumber' => auth()->user()->mobile_number ?: auth()->user()->phone,
                'alternateCountryCode' => auth()->user()->alternate_country_code ?: '+91',
                'alternateMobileNumber' => auth()->user()->alternate_mobile_number,
                'age' => auth()->user()->age,
                'gender' => auth()->user()->gender,
                'bloodGroup' => auth()->user()->blood_group,
                'relationshipWithPatient' => auth()->user()->relationship_with_patient ?: 'Self',
                'otherRelationship' => auth()->user()->other_relationship,
            ] : [];
        @endphp
        <script>
            function bookGuideModal() {
                return {
                    isOpen: false,
                    step: 1,
                    bookingStatus: 'idle',
                    bookingId: null,
                    bookingMessage: '',
                    needsLogin: false,
                    errors: {},
                    cities: @json($cityOptions ?? []),
                    hospitals: @json($hospitalOptions ?? []),
                    patientDefaults: {{ \Illuminate\Support\Js::from($patientDefaults) }},
                    selectedCity: '',
                    guidesLoading: false,
                    guideSearchMessage: '',

                    services: @json($modalServices ?? []),
                    guides: [],

                    form: {
                        hospitalId: '',
                        serviceId: @json($modalServices[0]['id'] ?? ''),
                        date: '',
                        time: '10:00',
                        guideId: null,
                        patientName: '',
                        mobileCountryCode: '+91',
                        mobileNumber: '',
                        alternateCountryCode: '+91',
                        alternateMobileNumber: '',
                        age: '',
                        gender: '',
                        bloodGroup: '',
                        relationshipWithPatient: 'Self',
                        otherRelationship: '',
                        note: '',
                    },

                    get filteredHospitals() {
                        return this.hospitals.filter(hospital => hospital.city === this.selectedCity);
                    },

                    get hospital() {
                        return this.hospitals.find(hospital => String(hospital.id) === String(this.form.hospitalId)) ?? null;
                    },

                    open() {
                        this.isOpen = true;
                        this.step = 1;
                        this.bookingStatus = 'idle';
                        this.bookingId = null;
                        this.bookingMessage = '';
                        this.needsLogin = false;
                        this.errors = {};
                        this.selectedCity = '';
                        this.form.hospitalId = '';
                        this.form.guideId = null;
                        this.guides = [];
                        this.guideSearchMessage = '';
                        Object.assign(this.form, this.patientDefaults);
                    },

                    close() {
                        this.isOpen = false;
                    },

                    get selectedService() {
                        return this.services.find(s => String(s.id) === String(this.form.serviceId)) ?? null;
                    },

                    get selectedGuide() {
                        return this.guides.find(g => String(g.id) === String(this.form.guideId)) ?? null;
                    },

                    get formattedDateTime() {
                        if (!this.form.date) return '';
                        const d = new Date(this.form.date + 'T' + (this.form.time || '00:00'));
                        return d.toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' }) + ' · ' + (this.form.time || '');
                    },

                    async goToStep(target) {
                        this.errors = {};

                        if (target === 2) {
                            if (!this.form.serviceId) {
                                this.errors.step1 = 'Select a service to continue.';
                                return;
                            }
                            if (!this.form.date || !this.form.time) {
                                this.errors.step1 = 'Choose a service, date and time to continue.';
                                return;
                            }
                            if (!this.form.hospitalId) {
                                this.errors.step1 = 'Choose a city and hospital to continue.';
                                return;
                            }

                            this.guidesLoading = true;
                            this.guides = [];
                            this.form.guideId = null;
                            this.guideSearchMessage = '';
                            try {
                                const query = new URLSearchParams({
                                    hospital_id: this.form.hospitalId,
                                    date: this.form.date,
                                    time: this.form.time,
                                });
                                const response = await fetch(`{{ route('api.guides.index') }}?${query}`, { headers: { Accept: 'application/json' } });
                                if (!response.ok) throw new Error('Guide availability could not be checked. Please try again.');
                                const availableGuides = await response.json();
                                this.guides = availableGuides.map(guide => ({
                                    id: guide.id,
                                    name: guide.name,
                                    rating: null,
                                    years: guide.years_experience,
                                    languages: (guide.languages ?? []).join(', '),
                                    initials: guide.name.split(/\s+/).map(part => part[0]).slice(0, 2).join('').toUpperCase(),
                                    photo: guide.photo,
                                }));
                                if (this.guides.length === 0) {
                                    this.guideSearchMessage = 'No guides are available at this hospital for the selected time. Try another date or time.';
                                }
                                this.step = target;
                            } catch (error) {
                                this.errors.step1 = error.message;
                                return;
                            } finally {
                                this.guidesLoading = false;
                            }
                            return;
                        }

                        if (target === 3) {
                            if (!this.form.guideId) {
                                this.errors.step2 = 'Select a guide to continue.';
                                return;
                            }
                            if (!this.form.patientName || !this.form.mobileNumber) {
                                this.errors.step2 = 'Add the patient name and mobile number to continue.';
                                return;
                            }
                            if (this.form.relationshipWithPatient === 'Other' && !this.form.otherRelationship.trim()) {
                                this.errors.step2 = 'Specify the relationship to continue.';
                                return;
                            }
                        }

                        this.step = target;
                    },

                    async pay() {
                        if (this.bookingStatus === 'processing' || this.bookingStatus === 'success') return;

                        this.errors = {};
                        this.needsLogin = false;
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
                                    hospital_id: this.hospital?.id,
                                    service_id: this.form.serviceId,
                                    guide_id: this.form.guideId,
                                    date: this.form.date,
                                    time: this.form.time,
                                    patient_name: this.form.patientName,
                                    mobile_country_code: this.form.mobileCountryCode,
                                    mobile_number: this.form.mobileNumber,
                                    alternate_country_code: this.form.alternateCountryCode,
                                    alternate_mobile_number: this.form.alternateMobileNumber,
                                    age: this.form.age,
                                    gender: this.form.gender,
                                    blood_group: this.form.bloodGroup,
                                    relationship_with_patient: this.form.relationshipWithPatient,
                                    other_relationship: this.form.otherRelationship,
                                    note: this.form.note,
                                }),
                            });
                            const result = await response.json();

                            if (response.status === 401 || response.status === 403) {
                                this.needsLogin = true;
                                throw new Error('Log in with a patient account to save this booking.');
                            }

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
