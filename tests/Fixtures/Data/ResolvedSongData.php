<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\UseModelPayloadResolver;
use Cline\Struct\Attributes\UseRequestPayloadResolver;
use Tests\Fixtures\Support\LocalModelPayloadResolver;
use Tests\Fixtures\Support\LocalRequestPayloadResolver;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[UseRequestPayloadResolver(LocalRequestPayloadResolver::class)]
#[UseModelPayloadResolver(LocalModelPayloadResolver::class)]
final readonly class ResolvedSongData extends AbstractData
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {}
}
