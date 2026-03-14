<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\PostalCode;

use Attribute;
use Cline\Struct\Casts\PostalCodeCast;
use Cline\Struct\Contracts\ProvidesCastClassInterface;

/**
 * Configures built-in postal code casting for a property.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class AsPostalCode implements ProvidesCastClassInterface
{
    public function __construct(
        public ?string $country = null,
    ) {}

    public function castClass(): string
    {
        return PostalCodeCast::class;
    }
}
