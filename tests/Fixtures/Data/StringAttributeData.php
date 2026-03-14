<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\Strings\After;
use Cline\Struct\Attributes\Strings\AfterLast;
use Cline\Struct\Attributes\Strings\Ascii;
use Cline\Struct\Attributes\Strings\Before;
use Cline\Struct\Attributes\Strings\BeforeLast;
use Cline\Struct\Attributes\Strings\Between;
use Cline\Struct\Attributes\Strings\BetweenFirst;
use Cline\Struct\Attributes\Strings\CamelCase;
use Cline\Struct\Attributes\Strings\ChopEnd;
use Cline\Struct\Attributes\Strings\ChopStart;
use Cline\Struct\Attributes\Strings\Deduplicate;
use Cline\Struct\Attributes\Strings\Finish;
use Cline\Struct\Attributes\Strings\Headline;
use Cline\Struct\Attributes\Strings\KebabCase;
use Cline\Struct\Attributes\Strings\Limit;
use Cline\Struct\Attributes\Strings\Lowercase;
use Cline\Struct\Attributes\Strings\Mask;
use Cline\Struct\Attributes\Strings\Numbers;
use Cline\Struct\Attributes\Strings\PadBoth;
use Cline\Struct\Attributes\Strings\PadLeft;
use Cline\Struct\Attributes\Strings\PadRight;
use Cline\Struct\Attributes\Strings\PascalCase;
use Cline\Struct\Attributes\Strings\Repeat;
use Cline\Struct\Attributes\Strings\Replace;
use Cline\Struct\Attributes\Strings\ReplaceEnd;
use Cline\Struct\Attributes\Strings\ReplaceFirst;
use Cline\Struct\Attributes\Strings\ReplaceLast;
use Cline\Struct\Attributes\Strings\ReplaceStart;
use Cline\Struct\Attributes\Strings\Reverse;
use Cline\Struct\Attributes\Strings\Slug;
use Cline\Struct\Attributes\Strings\SnakeCase;
use Cline\Struct\Attributes\Strings\Squish;
use Cline\Struct\Attributes\Strings\Start;
use Cline\Struct\Attributes\Strings\StudlyCase;
use Cline\Struct\Attributes\Strings\Take;
use Cline\Struct\Attributes\Strings\Titlecase;
use Cline\Struct\Attributes\Strings\Transliterate;
use Cline\Struct\Attributes\Strings\Trim;
use Cline\Struct\Attributes\Strings\Unwrap;
use Cline\Struct\Attributes\Strings\Uppercase;
use Cline\Struct\Attributes\Strings\Words;
use Cline\Struct\Attributes\Strings\Wrap;

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
