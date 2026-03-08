<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

use Illuminate\Http\Request;

/**
 * Resolves an HTTP request into data object input.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface RequestPayloadResolverInterface
{
    /**
     * Resolve the request into the payload array used for data hydration.
     *
     * @param  class-string<DataObjectInterface> $dataClass
     * @return array<string, mixed>
     */
    public function resolve(Request $request, string $dataClass): array;
}
