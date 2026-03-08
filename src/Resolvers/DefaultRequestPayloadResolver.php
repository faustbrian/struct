<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Resolvers;

use Cline\Struct\Contracts\DataObjectInterface;
use Cline\Struct\Contracts\RequestPayloadResolverInterface;
use Illuminate\Http\Request;

use function is_string;

/**
 * Resolves request data by returning only string-keyed input values.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class DefaultRequestPayloadResolver implements RequestPayloadResolverInterface
{
    /**
     * Resolve a request into the payload array used for data hydration.
     *
     * @param  class-string<DataObjectInterface> $dataClass
     * @return array<string, mixed>
     */
    public function resolve(Request $request, string $dataClass): array
    {
        $requestPayload = [];

        foreach ($request->all() as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $requestPayload[$key] = $value;
        }

        return $requestPayload;
    }
}
