<?php

namespace App\Http\Requests\Settings;

use App\Concerns\PasswordValidationRules;
use App\Domain\Users\AccountGuard;
use App\Http\Users\Shared\ActiveAdmins;
use App\Http\Users\Shared\GuardRefusalMessage;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ProfileDeleteRequest extends FormRequest
{
    use PasswordValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => $this->currentPasswordRules(),
        ];
    }

    /**
     * B15 — the last admin cannot delete their own account either.
     *
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $user = $this->user();
                $refusal = $user instanceof User ? AccountGuard::selfDeletion($user->assignedRole(), ActiveAdmins::count()) : null;

                if ($refusal !== null) {
                    $validator->errors()->add('account', GuardRefusalMessage::for($refusal));
                }
            },
        ];
    }
}
