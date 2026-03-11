<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

use Attribute;

/**
 * Marks a property as requiring encrypted persistence in consumer packages.
 *
 * Struct exposes the encryption requirement as metadata so packages with a
 * storage boundary can apply Laravel's encrypter consistently when they
 * persist or hydrate values.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Encrypted {}
