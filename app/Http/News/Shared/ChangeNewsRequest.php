<?php

declare(strict_types=1);

namespace App\Http\News\Shared;

use App\Domain\News\NewsAccess;
use App\Domain\Users\Permission;
use App\Models\NewsItem;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * NEWS-03 — only the author and an admin change or delete a news post.
 */
abstract class ChangeNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $news = $this->route('news');
        $user = $this->user();

        return $news instanceof NewsItem
            && $user instanceof User
            && NewsAccess::mayChange($news->user_id, $user->id, $user->can(Permission::ManageNews->value));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }
}
