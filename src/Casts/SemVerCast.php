<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Casts;

use Cline\SemVer\Constraint;
use Cline\SemVer\Version;
use Cline\Struct\Contracts\CastInterface;
use Cline\Struct\Metadata\PropertyMetadata;
use InvalidArgumentException;

use function is_string;
use function throw_unless;

/**
 * Casts values to and from `Version` and `Constraint` instances.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class SemVerCast implements CastInterface
{
    public function get(PropertyMetadata $property, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Version || $value instanceof Constraint) {
            return $value;
        }

        throw_unless(is_string($value), InvalidArgumentException::class, 'SemVerCast only supports Version, Constraint, or string values.');

        return $this->targetClass($property) === Constraint::class
            ? Constraint::parse($value)
            : Version::parse($value);
    }

    public function set(PropertyMetadata $property, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $version = $this->get($property, $value);

        return $version instanceof Version || $version instanceof Constraint
            ? (string) $version
            : $value;
    }

    /**
     * @return class-string<Constraint|Version>
     */
    private function targetClass(PropertyMetadata $property): string
    {
        foreach ($property->types as $type) {
            if ($type === Constraint::class) {
                return Constraint::class;
            }

            if ($type === Version::class) {
                return Version::class;
            }
        }

        return Version::class;
    }
}
