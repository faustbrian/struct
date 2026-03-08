<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;

/**
 * Resolves a model or array-like source into data object input.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface ModelPayloadResolverInterface
{
    /**
     * Resolve the source into the payload array used for data hydration.
     *
     * @param  array<string, mixed>|Arrayable<string, mixed>|Model $source
     * @param  class-string<DataObjectInterface>                   $dataClass
     * @return array<string, mixed>
     */
    public function resolve(array|Arrayable|Model $source, string $dataClass): array;
}
