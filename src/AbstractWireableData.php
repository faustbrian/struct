<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct;

use Livewire\Wireable;

use function is_array;

/**
 * Base class for struct DTOs that participate in Livewire wireable serialization.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
abstract readonly class AbstractWireableData extends AbstractData implements Wireable
{
    /**
     * Hydrate an instance from a Livewire payload.
     */
    public static function fromLivewire(mixed $value): static
    {
        /** @var array<string, mixed> $payload */
        $payload = is_array($value) ? $value : [];

        return static::create($payload);
    }

    /**
     * Export this DTO as a Livewire-compatible array payload.
     *
     * @return array<string, mixed>
     */
    public function toLivewire(): array
    {
        return $this->toArray();
    }
}
