<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Spec\RequirementCatalogue;
use App\Spec\StatusLedger;
use Illuminate\Console\Command;

final class SpecIndexCommand extends Command
{
    protected $signature = 'spec:index';

    protected $description = 'Parse docs/REQUIREMENTS.md and add any new requirement to the status ledger';

    public function handle(): int
    {
        $catalogue = RequirementCatalogue::default();
        $ledger = StatusLedger::default();

        $ids = $catalogue->ids();
        $added = $ledger->addMissing($ids);
        $ledger->write($ids);

        $unknown = array_diff($ledger->ids(), $ids);

        $this->components->info(sprintf(
            '%d requirements across %d areas.',
            count($ids),
            count($catalogue->areaNames()),
        ));

        if ($added !== []) {
            $this->components->warn(sprintf(
                '%d new requirement(s) added to the ledger as `planned`: %s',
                count($added),
                implode(', ', $added),
            ));
        }

        if ($unknown !== []) {
            $this->components->error(sprintf(
                'The ledger lists %d id(s) that docs/REQUIREMENTS.md does not declare: %s',
                count($unknown),
                implode(', ', $unknown),
            ));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
