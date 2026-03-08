<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

use Attribute;
use Cline\Struct\Contracts\ValidatorMutatorInterface;

/**
 * Assigns a validator mutator class to a data object.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class UseValidator
{
    /**
     * @param class-string<ValidatorMutatorInterface> $mutator Validator mutator class to resolve.
     */
    public function __construct(
        public string $mutator,
    ) {}
}
