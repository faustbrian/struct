<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Tests\Fixtures\ValidationAttributes\Min;

/**
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class CascadingProfileData extends AbstractData
{
    public function __construct(
        #[Min(3)]
        public string $name,
        public int $age,
    ) {}
}
