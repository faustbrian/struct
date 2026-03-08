<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\StringifyUsing;
use Cline\Struct\Stringifiers\XmlStringifier;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[StringifyUsing(XmlStringifier::class)]
final readonly class XmlStringifiedData extends AbstractData
{
    public function __construct(
        public string $title,
    ) {}
}
