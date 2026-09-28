@php($fieldIdPrefix = $idPrefix ?? '')
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="{{ $fieldIdPrefix }}mobile_number">Mobile number</label>
        <div class="input-group">
            @include('components.country-code-select', ['name' => 'mobile_country_code', 'id' => $fieldIdPrefix.'mobile_country_code', 'value' => old('mobile_country_code', $patient?->mobile_country_code ?: '+91')])
            <input class="form-control" id="{{ $fieldIdPrefix }}mobile_number" name="mobile_number" type="tel" inputmode="numeric" pattern="[0-9]*" maxlength="15" autocomplete="tel-national" data-digits-only value="{{ old('mobile_number', $patient?->mobile_number ?: $patient?->phone) }}" required>
        </div>
        @error('mobile_number')<small class="field-error">{{ $message }}</small>@enderror
        @error('mobile_country_code')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="{{ $fieldIdPrefix }}alternate_mobile_number">Alternative mobile number <span class="optional">(optional)</span></label>
        <div class="input-group">
            @include('components.country-code-select', ['name' => 'alternate_country_code', 'id' => $fieldIdPrefix.'alternate_country_code', 'value' => old('alternate_country_code', $patient?->alternate_country_code ?: '+91')])
            <input class="form-control" id="{{ $fieldIdPrefix }}alternate_mobile_number" name="alternate_mobile_number" type="tel" inputmode="numeric" pattern="[0-9]*" maxlength="15" autocomplete="off" data-digits-only value="{{ old('alternate_mobile_number', $patient?->alternate_mobile_number) }}">
        </div>
        @error('alternate_mobile_number')<small class="field-error">{{ $message }}</small>@enderror
        @error('alternate_country_code')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="col-sm-6 col-lg-3">
        <label class="form-label" for="{{ $fieldIdPrefix }}age">Age <span class="optional">(optional)</span></label>
        <input class="form-control" id="{{ $fieldIdPrefix }}age" name="age" type="number" inputmode="numeric" min="0" max="120" step="1" value="{{ old('age', $patient?->age) }}">
        @error('age')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="col-sm-6 col-lg-3">
        <label class="form-label" for="{{ $fieldIdPrefix }}gender">Gender <span class="optional">(optional)</span></label>
        <select class="form-select" id="{{ $fieldIdPrefix }}gender" name="gender">
            <option value="">Select gender</option>
            @foreach(config('patient.genders') as $gender)
                <option value="{{ $gender }}" @selected(old('gender', $patient?->gender) === $gender)>{{ $gender }}</option>
            @endforeach
        </select>
        @error('gender')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="col-sm-6 col-lg-3">
        <label class="form-label" for="{{ $fieldIdPrefix }}blood_group">Blood group <span class="optional">(optional)</span></label>
        <select class="form-select" id="{{ $fieldIdPrefix }}blood_group" name="blood_group">
            <option value="">Select blood group</option>
            @foreach(config('patient.blood_groups') as $bloodGroup)
                <option value="{{ $bloodGroup }}" @selected(old('blood_group', $patient?->blood_group) === $bloodGroup)>{{ $bloodGroup }}</option>
            @endforeach
        </select>
        @error('blood_group')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="col-sm-6 col-lg-3">
        <label class="form-label" for="{{ $fieldIdPrefix }}relationship_with_patient">Relationship with Patient <span class="optional">(optional)</span></label>
        <select class="form-select" id="{{ $fieldIdPrefix }}relationship_with_patient" name="relationship_with_patient" data-relationship-select>
            <option value="">Select relationship</option>
            @foreach(config('patient.relationships') as $relationship)
                <option value="{{ $relationship }}" @selected(old('relationship_with_patient', $patient?->relationship_with_patient ?: 'Self') === $relationship)>{{ $relationship }}</option>
            @endforeach
        </select>
        @error('relationship_with_patient')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="col-md-6" data-other-relationship-field @if(old('relationship_with_patient', $patient?->relationship_with_patient) !== 'Other') hidden @endif>
        <label class="form-label" for="{{ $fieldIdPrefix }}other_relationship">Please specify relationship</label>
        <input class="form-control" id="{{ $fieldIdPrefix }}other_relationship" name="other_relationship" maxlength="120" value="{{ old('other_relationship', $patient?->other_relationship) }}" @if(old('relationship_with_patient', $patient?->relationship_with_patient) === 'Other') required @endif>
        @error('other_relationship')<small class="field-error">{{ $message }}</small>@enderror
    </div>
</div>
@once
    @push('scripts')
        <script>
            document.addEventListener('input', (event) => {
                if (event.target.matches('[data-digits-only]')) {
                    event.target.value = event.target.value.replace(/\D/g, '').slice(0, 15);
                }
            }, true);
            document.addEventListener('change', (event) => {
                if (event.target.matches('[data-relationship-select]')) {
                    const otherField = event.target.closest('form').querySelector('[data-other-relationship-field]');
                    const showOther = event.target.value === 'Other';
                    otherField.hidden = !showOther;
                    otherField.querySelector('input').required = showOther;
                    if (!showOther) otherField.querySelector('input').value = '';
                }
            });
        </script>
    @endpush
@endonce