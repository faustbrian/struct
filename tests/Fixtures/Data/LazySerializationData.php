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
use Cline\Struct\Attributes\Computed;
use Cline\Struct\Attributes\IncludeWhen;
use Cline\Struct\Attributes\Lazy;
use Cline\Struct\Attributes\LazyGroup;
use Cline\Struct\Support\DataCollection;
use Tests\Fixtures\Support\AdminVisibilityCondition;
use Tests\Fixtures\Support\LazyAnalyticsResolver;
use Tests\Fixtures\Support\LazyDisplayNameComputer;

/**
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class LazySerializationData extends AbstractData
{
    public function __construct(
        public int $id,
        public string $name,
        #[Lazy()]
        public string $bio = '',
        #[LazyGroup('details')]
        public string $location = '',
        #[Lazy(LazyAnalyticsResolver::class)]
        public array $analytics = [],
        #[Computed(LazyDisplayNameComputer::class)]
        #[Lazy()]
        public string $displayName = '',
        #[IncludeWhen(AdminVisibilityCondition::class)]
        #[Lazy()]
        public string $adminNotes = '',
        #[AsDataCollection(LazyPostData::class)]
        public DataCollection $posts = new DataCollection(),
    ) {}
}
