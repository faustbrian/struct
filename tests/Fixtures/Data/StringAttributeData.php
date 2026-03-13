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
use Cline\Struct\Attributes\CamelCase;
use Cline\Struct\Attributes\ChopEnd;
use Cline\Struct\Attributes\ChopStart;
use Cline\Struct\Attributes\Deduplicate;
use Cline\Struct\Attributes\Finish;
use Cline\Struct\Attributes\Headline;
use Cline\Struct\Attributes\KebabCase;
use Cline\Struct\Attributes\Limit;
use Cline\Struct\Attributes\Lowercase;
use Cline\Struct\Attributes\Mask;
use Cline\Struct\Attributes\Numbers;
use Cline\Struct\Attributes\PadBoth;
use Cline\Struct\Attributes\PadLeft;
use Cline\Struct\Attributes\PadRight;
use Cline\Struct\Attributes\PascalCase;
use Cline\Struct\Attributes\Repeat;
use Cline\Struct\Attributes\Replace;
use Cline\Struct\Attributes\ReplaceEnd;
use Cline\Struct\Attributes\ReplaceFirst;
use Cline\Struct\Attributes\ReplaceLast;
use Cline\Struct\Attributes\ReplaceStart;
use Cline\Struct\Attributes\Reverse;
use Cline\Struct\Attributes\Slug;
use Cline\Struct\Attributes\SnakeCase;
use Cline\Struct\Attributes\Squish;
use Cline\Struct\Attributes\Start;
use Cline\Struct\Attributes\StudlyCase;
use Cline\Struct\Attributes\Take;
use Cline\Struct\Attributes\Titlecase;
use Cline\Struct\Attributes\Transliterate;
use Cline\Struct\Attributes\Trim;
use Cline\Struct\Attributes\Unwrap;
use Cline\Struct\Attributes\Uppercase;
use Cline\Struct\Attributes\Words;
use Cline\Struct\Attributes\Wrap;

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
        #[SnakeCase()]
        public string $snake,
        #[KebabCase()]
        public string $kebab,
        #[CamelCase()]
        public string $camel,
        #[StudlyCase()]
        public string $studly,
        #[PascalCase()]
        public string $pascal,
        #[Start('prefix-')]
        public string $started,
        #[Finish('-suffix')]
        public string $finished,
        #[Wrap('[')]
        public string $wrapped,
        #[Unwrap('"')]
        public string $unwrapped,
        #[ChopStart('prefix-')]
        public string $choppedStart,
        #[ChopEnd('-suffix')]
        public string $choppedEnd,
        #[Numbers()]
        public string $numbers,
        #[Deduplicate('-')]
        public string $deduplicated,
        #[Replace('world', 'there')]
        public string $replaced,
        #[ReplaceFirst('foo', 'bar')]
        public string $replaceFirst,
        #[ReplaceLast('foo', 'bar')]
        public string $replaceLast,
        #[ReplaceStart('foo', 'bar')]
        public string $replaceStart,
        #[ReplaceEnd('foo', 'bar')]
        public string $replaceEnd,
        #[Mask('*', 2, 4)]
        public string $masked,
        #[PadLeft(5, '0')]
        public string $padLeft,
        #[PadRight(5, '0')]
        public string $padRight,
        #[PadBoth(6, '0')]
        public string $padBoth,
        #[Reverse()]
        public string $reversed,
        #[Repeat(3)]
        public string $repeated,
        #[Trim()]
        public ?string $nullable = null,
        #[Trim()]
        public int $count = 0,
    ) {}
}
