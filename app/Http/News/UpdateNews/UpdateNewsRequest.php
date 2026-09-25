<?php

declare(strict_types=1);

namespace App\Http\News\UpdateNews;

use App\Http\News\Shared\ChangeNewsRequest;
use App\Http\News\Shared\NewsTextRules;
use Illuminate\Contracts\Validation\ValidationRule;

final class UpdateNewsRequest extends ChangeNewsRequest
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
