<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Livewire;

use Cline\Struct\Contracts\DataObjectInterface;
use Cline\Struct\Exceptions\InvalidLivewireDataClassException;
use Cline\Struct\Exceptions\InvalidLivewireDataPayloadException;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Override;

use function is_a;
use function is_array;
use function is_string;
use function throw_if;
use function throw_unless;

/**
 * Integrates Struct data objects with Livewire's synthesizer system.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class DataSynth extends Synth
{
    public static string $key = 'bldto';

    /**
     * Determine whether the given target should be handled by this synthesizer.
     */
    public static function match(mixed $target): bool
    {
        return $target instanceof DataObjectInterface;
    }

    /**
     * Convert a data object into Livewire's dehydrated payload format.
     *
     * @return array{0: array<string, mixed>, 1: array{class: class-string<DataObjectInterface>}}
     */
    public function dehydrate(DataObjectInterface $target, callable $dehydrateChild): array
    {
        /** @var array<string, mixed> $data */
        $data = $target->toArray();

        foreach ($data as $key => $child) {
            $data[$key] = $dehydrateChild($key, $child);
        }

        return [
            $data,
            ['class' => $target::class],
        ];
    }

    /**
     * Rebuild a Struct data object from Livewire's dehydrated payload.
     */
    public function hydrate(mixed $value, mixed $meta, callable $hydrateChild): DataObjectInterface
    {
        throw_if(
            !is_array($meta)
            || !isset($meta['class'])
            || !is_string($meta['class'])
            || !is_a($meta['class'], DataObjectInterface::class, true),
            InvalidLivewireDataClassException::fromMetadata(),
        );

        throw_unless(is_array($value), InvalidLivewireDataPayloadException::fromPayload());

        foreach ($value as $key => $child) {
            $value[$key] = $hydrateChild($key, $child);
        }

        /** @var class-string<DataObjectInterface> $class */
        $class = $meta['class'];

        /** @var array<string, mixed> $payload */
        $payload = $value;

        return $class::create($payload);
    }

    /**
     * @param DataObjectInterface $target
     * @param string              $key
     */
    #[Override()]
    public function get(&$target, $key): mixed
    {
        return $target->{$key};
    }

    /**
     * @param DataObjectInterface $target
     * @param string              $key
     */
    public function set(&$target, $key, mixed $value): void
    {
        $target = $target->with(...[$key => $value]);
    }
}
