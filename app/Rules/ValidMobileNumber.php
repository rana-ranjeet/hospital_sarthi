<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidMobileNumber implements Rule
{
    public function __construct(private string $countryCode)
    {
    }

    public function passes($attribute, $value): bool
    {
        $country = config('patient.country_calling_codes.'.$this->countryCode);
        if (! $country || ! is_string($value) || ! preg_match('/^\d+$/', $value)) {
            return false;
        }

        if (strlen($value) < $country[1] || strlen($value) > $country[2]) {
            return false;
        }

        $pattern = config('patient.mobile_patterns.'.$this->countryCode);

        return ! $pattern || preg_match($pattern, $value) === 1;
    }

    public function message(): string
    {
        $country = config('patient.country_calling_codes.'.$this->countryCode);
        if (! $country) {
            return 'Choose a valid country calling code.';
        }

        $lengthMessage = $country[1] === $country[2]
            ? "must contain exactly {$country[1]} digits for {$country[0]}"
            : "must contain {$country[1]} to {$country[2]} digits for {$country[0]}";

        return ':attribute '.$lengthMessage.'.';
    }
}