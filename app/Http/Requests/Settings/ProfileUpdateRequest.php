<?php

namespace App\Http\Requests\Settings;

use App\Concerns\ProfileValidationRules;
use App\Models\Setting;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = $this->profileRules($this->user()->id);

        // D16 — no mail could confirm a new address, and an unverified one locks the user out.
        $rules['email'][] = function (string $attribute, mixed $value, Closure $fail): void {
            if ($value !== $this->user()->email && ! Setting::mailEnabled()) {
                $fail(__('Your email address cannot be changed while email is switched off.'));
            }
        };

        return $rules;
    }
}
