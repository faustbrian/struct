<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support;

use Cline\Struct\Contracts\ModelPayloadResolverInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;

use function is_array;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class LocalModelPayloadResolver implements ModelPayloadResolverInterface
{
    public function resolve(array|Arrayable|Model $source, string $dataClass): array
    {
        $payload = is_array($source) ? $source : $source->toArray();
        $payload['title'] = 'local-model:'.$payload['title'];

        return $payload;
    }
}
