<?php

declare(strict_types=1);

namespace App\Http\Users\UpdateMailSettings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * D16 — the one switch for outgoing mail.
 */
final class UpdateMailSettingsRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'mail_enabled' => ['required', 'boolean'],
        ];
    }
}
