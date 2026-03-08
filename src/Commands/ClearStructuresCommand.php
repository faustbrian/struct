<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Commands;

use Cline\Struct\Metadata\MetadataFactory;
use Illuminate\Console\Command;
use Override;

/**
 * Clear cached Struct metadata from runtime and persistent stores.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ClearStructuresCommand extends Command
{
    #[Override()]
    protected $signature = 'struct:clear';

    #[Override()]
    protected $description = 'Clear cached metadata structures for Struct data objects.';

    public function handle(MetadataFactory $metadataFactory): int
    {
        $metadataFactory->clearRuntimeCache();
        $metadataFactory->clearPersistentCache();

        $this->info('Cleared cached Struct metadata.');

        return self::SUCCESS;
    }
}
