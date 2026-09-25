<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Visits\PublicPage;
use App\Domain\Visits\VisitRetention;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * VIS-01, VIS-02 — one counted visit of a public page by a guest: the page,
 * the time and the day's visitor mark, nothing else.
 *
 * @property int $id
 * @property PublicPage $page
 * @property string $visitor
 * @property Carbon $visited_at
 */
#[Fillable(['page', 'visitor', 'visited_at'])]
class Visit extends Model
{
    use MassPrunable;

    public $timestamps = false;

    /**
     * VIS-03 — pruned nightly by `model:prune` (D14).
     *
     * @return Builder<self>
     */
    public function prunable(): Builder
    {
        return static::where('visited_at', '<', VisitRetention::cutoff(now()->toDateTimeImmutable()));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'page' => PublicPage::class,
            'visited_at' => 'datetime',
        ];
    }
}
