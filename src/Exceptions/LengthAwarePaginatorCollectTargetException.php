<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Exceptions;

use Illuminate\Pagination\LengthAwarePaginator;

use function sprintf;

/**
 * Raised when length-aware paginator items are collected into an unsupported target.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class LengthAwarePaginatorCollectTargetException extends AbstractCollectTargetException
{
    public static function fromTarget(?string $target): self
    {
        return new self(sprintf(
            'LengthAwarePaginator input can only collect into [%s], [%s] given.',
            LengthAwarePaginator::class,
            $target ?? 'null',
        ));
    }
}
