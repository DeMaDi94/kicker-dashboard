<?php

declare(strict_types=1);

namespace App\Spec;

/**
 * One numbered requirement from the catalogue.
 */
final readonly class Requirement
{
    public function __construct(
        public string $id,
        public string $areaPrefix,
        public string $areaName,
        public string $text,
    ) {}

    public function summary(int $length = 90): string
    {
        $singleLine = preg_replace('/\s+/u', ' ', $this->text) ?? '';

        return mb_strimwidth($singleLine, 0, $length, '…');
    }
}
