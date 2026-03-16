<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Casts;

use Cline\Struct\Contracts\CastInterface;
use Cline\Struct\Metadata\PropertyMetadata;
use Cline\Struct\Support\DateFormat;
use DateTimeInterface;

/**
 * Casts values to and from `DateTimeInterface`-compatible instances.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class DateTimeInterfaceCast implements CastInterface
{
    private DateFormat $date;

    public function __construct(?DateFormat $date = null)
    {
        $this->date = $date ?? DateFormat::fromConfig();
    }

    /**
     * Cast an incoming value into a CarbonImmutable instance when needed.
     */
    public function get(PropertyMetadata $property, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->date->parseCarbonImmutableValue($value);
    }

    /**
     * Prepare the given value for storage from a DateTime-compatible value.
     */
    public function set(PropertyMetadata $property, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface && $this->date->timezone === null) {
            return $value->format($this->date->format);
        }

        return $value instanceof DateTimeInterface
            ? $this->date->formatValue($value)
            : $value;
    }
}
