<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\Computed;
use Cline\Struct\Attributes\MapName;
use Cline\Struct\Enums\NameMapper;
use Tests\Fixtures\Support\ComputedKeysComputer;
use Tests\Fixtures\Support\DisplayNameComputer;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[MapName(NameMapper::SnakeCase)]
final readonly class MultiComputedData extends AbstractData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        #[Computed(DisplayNameComputer::class)]
        public string $fullName = '',
        #[Computed(ComputedKeysComputer::class)]
        public string $summary = '',
    ) {}
}
