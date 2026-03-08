<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Exceptions;

use Illuminate\Pagination\CursorPaginator;

use function sprintf;

/**
 * Raised when cursor paginator items are collected into an unsupported target.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class CursorPaginatorCollectTargetException extends AbstractCollectTargetException
{
    public static function fromTarget(?string $target): self
    {
        return new self(sprintf(
            'CursorPaginator input can only collect into [%s], [%s] given.',
            CursorPaginator::class,
            $target ?? 'null',
        ));
    }
}
