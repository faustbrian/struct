<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\After;
use Cline\Struct\Attributes\AfterLast;
use Cline\Struct\Attributes\Ascii;
use Cline\Struct\Attributes\Before;
use Cline\Struct\Attributes\BeforeLast;
use Cline\Struct\Attributes\Between;
use Cline\Struct\Attributes\BetweenFirst;
use Cline\Struct\Attributes\Headline;
use Cline\Struct\Attributes\Limit;
use Cline\Struct\Attributes\Lowercase;
use Cline\Struct\Attributes\Slug;
use Cline\Struct\Attributes\Squish;
use Cline\Struct\Attributes\Take;
use Cline\Struct\Attributes\Titlecase;
use Cline\Struct\Attributes\Transliterate;
use Cline\Struct\Attributes\Trim;
use Cline\Struct\Attributes\Uppercase;
use Cline\Struct\Attributes\Words;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class StringAttributeData extends AbstractData
{
    public function __construct(
        #[Trim()]
        #[Squish()]
        #[Headline()]
        public string $headline,
        #[Trim()]
        #[Slug()]
        public string $slug,
        #[Trim()]
        #[Limit(12)]
        public string $summary,
        #[After(':')]
        public string $after,
        #[AfterLast('/')]
        public string $afterLast,
        #[Before(':')]
        public string $before,
        #[BeforeLast('/')]
        public string $beforeLast,
        #[Between('[', ']')]
        public string $between,
        #[BetweenFirst('[', ']')]
        public string $betweenFirst,
        #[Lowercase()]
        #[Take(5)]
        public string $snippet,
        #[Titlecase()]
        public string $title,
        #[Uppercase()]
        public string $shout,
        #[Ascii()]
        public string $ascii,
        #[Transliterate(unknown: '', strict: true)]
        public string $transliterated,
        #[Words(3)]
        public string $words,
        #[Trim()]
        public ?string $nullable = null,
        #[Trim()]
        public int $count = 0,
    ) {}
}
