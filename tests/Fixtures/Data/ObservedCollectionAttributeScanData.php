<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsDataList;
use Cline\Struct\Enums\DataListType;
use Cline\Struct\Metadata\ClassMetadata;
use Cline\Struct\Support\CreationContext;
use Cline\Struct\Support\DataList;
use Override;
use Tests\Fixtures\Attributes\ObserveCollectionAttributeScan;
use Tests\Fixtures\Support\ObservedCollectionAttributeScans;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class ObservedCollectionAttributeScanData extends AbstractData
{
    public function __construct(
        #[AsDataList(DataListType::String)]
        #[ObserveCollectionAttributeScan()]
        public DataList $items,
    ) {}

    public static function reset(): void
    {
        ObservedCollectionAttributeScans::$count = 0;
    }

    /**
     * @param  array<array-key, mixed> $input
     * @return array<array-key, mixed>
     */
    #[Override()]
    protected static function prepareInput(ClassMetadata $metadata, array $input, ?CreationContext $context = null): array
    {
        return $input;
    }
}
