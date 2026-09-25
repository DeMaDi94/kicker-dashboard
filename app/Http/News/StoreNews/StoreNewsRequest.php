<?php

declare(strict_types=1);

namespace App\Http\News\StoreNews;

use App\Http\News\Shared\NewsTextRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreNewsRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['text' => NewsTextRules::rules()];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['text' => __('News post')];
    }
}
