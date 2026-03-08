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
use Tests\Fixtures\Validation\MutatedValidationMutator;

/**
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
#[UseValidator(MutatedValidationMutator::class)]
final readonly class MutatedValidationData extends AbstractData
{
    public function __construct(
        public string $name,
        public string $code,
        public ?string $forbidden = null,
    ) {}
}
