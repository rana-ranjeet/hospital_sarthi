@props([
    'name',
    'id',
    'value' => '+91',
    'xModel' => null,
    'class' => 'form-select',
])
<select name="{{ $name }}" id="{{ $id }}" class="{{ $class }}" @if($xModel) x-model="{{ $xModel }}" @endif>
    @foreach(config('patient.country_calling_codes') as $code => $country)
        <option value="{{ $code }}" @selected(! $xModel && (string) $value === $code)>{{ $country[0] }} ({{ $code }})</option>
    @endforeach
</select>