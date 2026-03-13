<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\SemVer\Constraint;
use Cline\SemVer\Version;
use Cline\Struct\AbstractData;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class SemVerData extends AbstractData
{
    public function __construct(
        public Version $version,
        public Constraint $constraint,
    ) {}
}
