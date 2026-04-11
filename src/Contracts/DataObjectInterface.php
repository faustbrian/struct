<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

use Cline\Struct\Factories\AbstractFactory;
use Cline\Struct\Serialization\DataSerializer;
use Cline\Struct\Serialization\SerializationOptions;
use Cline\Struct\Support\DataCollection;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use JsonSerializable;
use Stringable;

/**
 * @author Brian Faust <brian@cline.sh>
 * @extends Arrayable<string, mixed>
 */
interface DataObjectInterface extends Arrayable, Castable, Jsonable, JsonSerializable, Stringable
{
    /**
     * @param array<string, mixed> $input
     */
    public static function create(array $input): static;

    /**
     * @param array<string, mixed> $input
     */
    public static function createWithValidation(array $input): static;

    /**
     * @param array<string, mixed>|Arrayable<string, mixed>|Model $source
     */
    public static function createFromModel(array|Arrayable|Model $source): static;

    /**
     * @param array<string, mixed>|Arrayable<string, mixed>|Model $source
     */
    public static function createFromModelWithValidation(array|Arrayable|Model $source): static;

    public static function createFromRequest(Request $request): static;

    public static function createFromRequestWithValidation(Request $request): static;

    /**
     * @param  array<array-key, mixed>|Collection<array-key, mixed>|CursorPaginator<array-key, mixed>|LengthAwarePaginator<array-key, mixed>                                       $items
     * @return array<array-key, static>|Collection<array-key, static>|CursorPaginator<array-key, static>|DataCollection<array-key, static>|LengthAwarePaginator<array-key, static>
     */
    public static function collect(
        array|Collection|LengthAwarePaginator|CursorPaginator $items,
    ): array|Collection|LengthAwarePaginator|CursorPaginator|DataCollection;

    /**
     * @param  array<array-key, mixed>|Collection<array-key, mixed>|CursorPaginator<array-key, mixed>|LengthAwarePaginator<array-key, mixed>                                       $items
     * @param  'array'|class-string                                                                                                                                                $into
     * @return array<array-key, static>|Collection<array-key, static>|CursorPaginator<array-key, static>|DataCollection<array-key, static>|LengthAwarePaginator<array-key, static>
     */
    public static function collectInto(
        array|Collection|LengthAwarePaginator|CursorPaginator $items,
        string $into,
    ): array|Collection|LengthAwarePaginator|CursorPaginator|DataCollection;

    public static function factory(): AbstractFactory;

    public function with(mixed ...$overrides): static;

    public function serializer(?SerializationOptions $options = null): DataSerializer;

    /**
     * @param  array<int, string>   $include
     * @param  array<int, string>   $exclude
     * @param  array<int, string>   $groups
     * @param  array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function toArray(
        bool $includeSensitive = false,
        array $include = [],
        array $exclude = [],
        array $groups = [],
        array $context = [],
        ?SerializationOptions $serialization = null,
    ): array;

    /**
     * @param mixed                $options
     * @param array<int, string>   $include
     * @param array<int, string>   $exclude
     * @param array<int, string>   $groups
     * @param array<string, mixed> $context
     */
    public function toJson(
        $options = 0,
        bool $includeSensitive = false,
        array $include = [],
        array $exclude = [],
        array $groups = [],
        array $context = [],
        ?SerializationOptions $serialization = null,
    ): string;
}
