<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Eloquent;

use Cline\Struct\AbstractData;
use Cline\Struct\Contracts\DataObjectInterface;
use Cline\Struct\Serialization\SerializationContext;
use Cline\Struct\Serialization\SerializationOptions;
use Cline\Struct\Support\DataCollection;
use Cline\Struct\Support\RecursionGuard;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Stringable;

use const JSON_THROW_ON_ERROR;

use function is_array;
use function is_string;
use function json_decode;
use function json_encode;

/**
 * @author Brian Faust <brian@cline.sh>
 * @implements CastsAttributes<DataCollection<int, DataObjectInterface>, mixed>
 * @psalm-immutable
 */
final readonly class AsDataCollection implements CastsAttributes, SerializesCastableAttributes, Stringable
{
    public function __construct(
        private string $dtoClass,
    ) {}

    public function __toString(): string
    {
        return self::class.':'.$this->dtoClass;
    }

    /**
     * @param class-string<DataObjectInterface> $dtoClass
     */
    public static function of(string $dtoClass): self
    {
        return new self($dtoClass);
    }

    /**
     * @param  mixed                                    $model
     * @param  array<string, mixed>                     $attributes
     * @return DataCollection<int, DataObjectInterface>
     */
    public function get($model, string $key, mixed $value, array $attributes): DataCollection
    {
        if ($value === null) {
            return new DataCollection();
        }

        /** @var class-string<DataObjectInterface> $dtoClass */
        $dtoClass = $this->dtoClass;
        $decoded = is_string($value) ? json_decode($value, true) : $value;
        $source = is_array($decoded) ? $decoded : [];
        $payloads = [];

        foreach ($source as $index => $item) {
            $payloads[$index] = is_array($item) ? $item : [];
        }

        /** @var array<int, DataObjectInterface> $items */
        $items = $dtoClass::collect($payloads);

        return new DataCollection($items);
    }

    /**
     * @param  mixed                      $model
     * @param  array<string, mixed>       $attributes
     * @return array<string, null|string>
     */
    public function set($model, string $key, mixed $value, array $attributes): array
    {
        /** @var class-string<DataObjectInterface> $dtoClass */
        $dtoClass = $this->dtoClass;
        $source = $value instanceof DataCollection || $value instanceof Collection
            ? $value->all()
            : (is_array($value) ? $value : []);
        $serialization = new SerializationOptions();
        $context = new SerializationContext(
            new RecursionGuard(),
            $serialization,
        );
        $items = [];
        $rawPayloads = [];

        foreach ($source as $index => $item) {
            if ($item instanceof DataObjectInterface) {
                $items[$index] = $this->serializeDto($item, $serialization, $context);

                continue;
            }

            if ($item instanceof Arrayable) {
                $items[$index] = $item->toArray();

                continue;
            }

            if (is_string($item)) {
                $item = json_decode($item, true);
            }

            $rawPayloads[$index] = is_array($item) ? $item : ['value' => $item];
        }

        if ($rawPayloads !== []) {
            /** @var array<int, DataObjectInterface> $normalizedItems */
            $normalizedItems = $dtoClass::collect($rawPayloads);

            foreach ($normalizedItems as $index => $dto) {
                $items[$index] = $this->serializeDto($dto, $serialization, $context);
            }
        }

        return [$key => json_encode($items, JSON_THROW_ON_ERROR)];
    }

    /**
     * @param mixed                $model
     * @param array<string, mixed> $attributes
     */
    public function serialize($model, string $key, mixed $value, array $attributes): mixed
    {
        if (!$value instanceof DataCollection) {
            return $value;
        }

        $serialization = new SerializationOptions();
        $context = new SerializationContext(
            new RecursionGuard(),
            $serialization,
        );
        $items = [];

        foreach ($value->all() as $itemKey => $dto) {
            $items[$itemKey] = $dto instanceof DataObjectInterface
                ? $this->serializeDto($dto, $serialization, $context)
                : [];
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeDto(
        DataObjectInterface $dto,
        SerializationOptions $options,
        SerializationContext $context,
    ): array {
        if ($dto instanceof AbstractData) {
            return $dto->toArrayUsingContext($context);
        }

        return $dto->toArray(serialization: $options);
    }
}
