<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;

/**
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class LazyPostData extends AbstractData
{
    public function __construct(
        public string $title,
        public LazyAuthorData $author,
    ) {}
}
