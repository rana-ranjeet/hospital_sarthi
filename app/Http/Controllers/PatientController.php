<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\ValidMobileNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->profileRules($request));
        $request->user()->update($validated);

        return back()->with('status', 'Your patient profile was updated.');
    }

    public static function profileRules(Request $request): array
    {
        $countryCodes = array_keys(config('patient.country_calling_codes'));
        $mobileCountryCode = (string) $request->input('mobile_country_code', '+91');
        $alternateCountryCode = (string) $request->input('alternate_country_code', '+91');

        return [
            'name' => ['required', 'string', 'max:120'],
            'mobile_country_code' => ['required', 'string', Rule::in($countryCodes)],
            'mobile_number' => ['required', 'string', 'regex:/^\d+$/', new ValidMobileNumber($mobileCountryCode)],
            'alternate_country_code' => ['required', 'string', Rule::in($countryCodes)],
            'alternate_mobile_number' => ['nullable', 'string', 'regex:/^\d+$/', new ValidMobileNumber($alternateCountryCode)],
            'age' => ['nullable', 'integer', 'between:0,120'],
            'gender' => ['nullable', Rule::in(config('patient.genders'))],
            'blood_group' => ['nullable', Rule::in(config('patient.blood_groups'))],
            'relationship_with_patient' => ['nullable', Rule::in(config('patient.relationships'))],
            'other_relationship' => ['nullable', 'required_if:relationship_with_patient,Other', 'string', 'max:120'],
        ];
    }
}