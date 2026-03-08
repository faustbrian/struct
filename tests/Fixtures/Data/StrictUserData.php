<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\ForbidSuperfluousKeys;
use Tests\Fixtures\Enums\UserStatus;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[ForbidSuperfluousKeys()]
final readonly class StrictUserData extends AbstractData
{
    public function __construct(
        public int $id,
        public string $name,
        public UserStatus $status,
    ) {}
}
