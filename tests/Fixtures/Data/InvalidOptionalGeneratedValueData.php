<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\Strings\Uuid;
use Cline\Struct\Support\Optional;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class InvalidOptionalGeneratedValueData extends AbstractData
{
    public function __construct(
        #[Uuid()]
        public Optional|string $id = new Optional(),
    ) {}
}
