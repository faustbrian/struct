<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsDataCollection;
use Cline\Struct\Support\DataCollection;

/**
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class CascadingRootData extends AbstractData
{
    public function __construct(
        public string $title,
        public CascadingProfileData $profile,
        #[AsDataCollection(CascadingProfileData::class)]
        public DataCollection $contacts,
    ) {}
}
