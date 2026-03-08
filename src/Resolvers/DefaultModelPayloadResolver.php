<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Resolvers;

use Cline\Struct\Contracts\DataObjectInterface;
use Cline\Struct\Contracts\ModelPayloadResolverInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;

use function is_array;

/**
 * Resolves model payloads by normalizing arrays and arrayable sources.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class DefaultModelPayloadResolver implements ModelPayloadResolverInterface
{
    /**
     * Resolve a model or array-like source into the payload array for hydration.
     *
     * @param  array<string, mixed>|Arrayable<string, mixed>|Model $source
     * @param  class-string<DataObjectInterface>                   $dataClass
     * @return array<string, mixed>
     */
    public function resolve(array|Arrayable|Model $source, string $dataClass): array
    {
        if (is_array($source)) {
            return $source;
        }

        return $source->toArray();
    }
}
