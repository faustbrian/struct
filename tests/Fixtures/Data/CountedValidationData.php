<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\UseValidator;
use Tests\Fixtures\Validation\CountingValidationMutator;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[UseValidator(CountingValidationMutator::class)]
final readonly class CountedValidationData extends AbstractData
{
    public function __construct(
        public string $name,
    ) {}
}
