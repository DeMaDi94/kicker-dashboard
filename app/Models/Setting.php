<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * D16 — the installation's settings, edited by an admin: one row.
 *
 * @property int $id
 * @property bool $mail_enabled
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['mail_enabled'])]
class Setting extends Model
{
    /**
     * D16 — outgoing mail starts switched off.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'mail_enabled' => false,
    ];

    public static function current(): self
    {
        return static::query()->firstOrNew();
    }

    /**
     * D16 — whether the app sends any mail at all.
     */
    public static function mailEnabled(): bool
    {
        return static::current()->mail_enabled;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mail_enabled' => 'boolean',
        ];
    }
}
