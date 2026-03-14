<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Money;

use Attribute;
use Cline\Struct\Casts\MoneyCast;
use Cline\Struct\Contracts\ProvidesCastClassInterface;

/**
 * Configures built-in money casting for a property.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class AsMoney implements ProvidesCastClassInterface
{
    public function __construct(
        public ?string $currency = null,
        public bool $minor = false,
    ) {}

    public function castClass(): string
    {
        return MoneyCast::class;
    }
}
