<?php

declare(strict_types=1);

namespace App\Domain\History;

use App\Domain\Shared\NameOrder;

/**
 * LOG-02 — one changed value with its old and its new value. `subject` names
 * whose value it is where the entry holds several of one kind — the player
 * whose points changed. `null` on the old side is a value that did not exist
 * yet, on the new side one that is gone.
 */
final readonly class Change
{
    public function __construct(
        public ChangedField $field,
        public int|string|null $old,
        public int|string|null $new,
        public ?string $subject = null,
    ) {}

    /**
     * The values of `$after` that differ from `$before`, in the order of
     * `$after`; a key missing from `$before` was not there before.
     *
     * @param  array<value-of<ChangedField>, int|string|null>  $before
     * @param  array<value-of<ChangedField>, int|string|null>  $after
     * @return list<self>
     */
    public static function between(array $before, array $after): array
    {
        $changes = [];

        foreach ($after as $field => $new) {
            $old = $before[$field] ?? null;

            if ($old !== $new) {
                $changes[] = new self(ChangedField::from($field), $old, $new);
            }
        }

        return $changes;
    }

    /**
     * MD-01, MD-04 — each player whose points a matchday save entered or
     * changed, by name A–Z (D3).
     *
     * @param  array<int, string>  $names  player id => name
     * @param  array<int, int>  $before  player id => points, only those entered
     * @param  array<int, int>  $after  player id => points
     * @return list<self>
     */
    public static function points(array $names, array $before, array $after): array
    {
        $changes = [];

        foreach ($after as $playerId => $points) {
            $old = $before[$playerId] ?? null;

            if ($old !== $points) {
                $changes[] = new self(ChangedField::Points, $old, $points, $names[$playerId] ?? '');
            }
        }

        usort($changes, fn (self $a, self $b): int => NameOrder::compare((string) $a->subject, (string) $b->subject));

        return $changes;
    }

    /**
     * SEA-02 — a season's players before and after, each side by name A–Z
     * (D3); no change when the same players take part.
     *
     * @param  list<string>  $before
     * @param  list<string>  $after
     * @return list<self>
     */
    public static function players(array $before, array $after): array
    {
        usort($before, NameOrder::compare(...));
        usort($after, NameOrder::compare(...));

        if ($before === $after) {
            return [];
        }

        return [new self(ChangedField::Players, $before === [] ? null : implode(', ', $before), $after === [] ? null : implode(', ', $after))];
    }

    /**
     * @return array{field: value-of<ChangedField>, subject: string|null, old: int|string|null, new: int|string|null}
     */
    public function toArray(): array
    {
        return ['field' => $this->field->value, 'subject' => $this->subject, 'old' => $this->old, 'new' => $this->new];
    }
}
