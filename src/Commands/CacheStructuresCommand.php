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

use function count;
use function sprintf;

/**
 * Warm cached Struct metadata for discovered data objects.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class CacheStructuresCommand extends Command
{
    #[Override()]
    protected $signature = 'struct:cache';

    #[Override()]
    protected $description = 'Warm cached metadata structures for Struct data objects.';

    public function handle(MetadataFactory $metadataFactory): int
    {
        $classes = $metadataFactory->discoverDataClasses();

        foreach ($classes as $class) {
            $metadataFactory->for($class);
        }

        $this->info(sprintf('Cached metadata for %d data object(s).', count($classes)));

        return self::SUCCESS;
    }
}
