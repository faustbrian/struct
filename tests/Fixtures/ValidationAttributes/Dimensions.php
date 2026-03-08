<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\ValidationAttributes;

use Attribute;
use Cline\Struct\Attributes\AbstractValidationRuleAttribute;
use Illuminate\Validation\Rule;

use function array_filter;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final readonly class Dimensions extends AbstractValidationRuleAttribute
{
    public function __construct(
        public ?int $width = null,
        public ?int $height = null,
        public ?int $minWidth = null,
        public ?int $minHeight = null,
        public ?int $maxWidth = null,
        public ?int $maxHeight = null,
        public ?float $ratio = null,
        public ?float $minRatio = null,
        public ?float $maxRatio = null,
    ) {}

    public function rules(): array
    {
        $constraints = array_filter([
            'width' => $this->width,
            'height' => $this->height,
            'min_width' => $this->minWidth,
            'min_height' => $this->minHeight,
            'max_width' => $this->maxWidth,
            'max_height' => $this->maxHeight,
            'ratio' => $this->ratio,
            'min_ratio' => $this->minRatio,
            'max_ratio' => $this->maxRatio,
        ], static fn (mixed $value): bool => $value !== null);

        return [Rule::dimensions($constraints)];
    }
}
