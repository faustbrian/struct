<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support;

use Cline\Struct\Contracts\RequestPayloadResolverInterface;
use Illuminate\Http\Request;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class LocalRequestPayloadResolver implements RequestPayloadResolverInterface
{
    public function resolve(Request $request, string $dataClass): array
    {
        /** @var array<string, mixed> $payload */
        $payload = $request->all();
        $payload['title'] = 'local-request:'.$payload['title'];

        return $payload;
    }
}
