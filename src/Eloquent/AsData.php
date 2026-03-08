<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Eloquent;

use Cline\Struct\Contracts\DataObjectInterface;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Contracts\Support\Arrayable;

use const JSON_THROW_ON_ERROR;

use function is_array;
use function is_string;
use function json_decode;
use function json_encode;

/**
 * @author Brian Faust <brian@cline.sh>
 * @implements CastsAttributes<?DataObjectInterface, mixed>
 * @psalm-immutable
 */
final readonly class AsData implements CastsAttributes, SerializesCastableAttributes
{
    public function __construct(
        private string $dtoClass,
    ) {}

    /**
     * @param mixed                $model
     * @param array<string, mixed> $attributes
     */
    public function get($model, string $key, mixed $value, array $attributes): ?DataObjectInterface
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DataObjectInterface) {
            return $value;
        }

        /** @var class-string<DataObjectInterface> $dtoClass */
        $dtoClass = $this->dtoClass;
        $payload = is_string($value) ? json_decode($value, true) : $value;

        if ($payload instanceof Arrayable) {
            $payload = $payload->toArray();
        }

        if (!is_array($payload)) {
            $payload = [];
        }

        /** @var array<string, mixed> $payload */
        return $dtoClass::create($payload);
    }

    /**
     * @param  mixed                      $model
     * @param  array<string, mixed>       $attributes
     * @return array<string, null|string>
     */
    public function set($model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            return [$key => null];
        }

        if ($value instanceof DataObjectInterface) {
            return [$key => json_encode($value->toArray(), JSON_THROW_ON_ERROR)];
        }

        if ($value instanceof Arrayable) {
            /** @var array<string, mixed> $arrayValue */
            $arrayValue = $value->toArray();
            $value = $arrayValue;
        }

        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        /** @var class-string<DataObjectInterface> $dtoClass */
        $dtoClass = $this->dtoClass;

        /** @var array<string, mixed> $payload */
        $payload = is_array($value) ? $value : ['value' => $value];
        $value = $dtoClass::create($payload);

        return [$key => json_encode($value->toArray(), JSON_THROW_ON_ERROR)];
    }

    /**
     * @param mixed                $model
     * @param array<string, mixed> $attributes
     */
    public function serialize($model, string $key, mixed $value, array $attributes): mixed
    {
        return $value instanceof DataObjectInterface ? $value->toArray() : $value;
    }
}
