<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Strings;

use Cline\Struct\Contracts\GeneratesMissingValueInterface;

use function strtolower;

/**
 * Base attribute for missing-value string generators.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
abstract readonly class AbstractGeneratedValueAttribute implements GeneratesMissingValueInterface
{
    public function __construct(
        public bool $lowerCase = false,
    ) {}

    protected function normalize(string $value): string
    {
        return $this->lowerCase ? strtolower($value) : $value;
    }
}
