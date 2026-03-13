<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes;

use Attribute;
use Illuminate\Support\Str;

/**
 * Converts a string property to a URL-friendly slug.
 *
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Slug extends AbstractStringTransformer
{
    /**
     * @param array<string, string> $dictionary
     */
    public function __construct(
        public string $separator = '-',
        public ?string $language = 'en',
        public array $dictionary = ['@' => 'at'],
    ) {}

    public function transform(string $value): string
    {
        return Str::slug($value, $this->separator, $this->language, $this->dictionary);
    }
}
