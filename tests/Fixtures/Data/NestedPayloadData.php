<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use DateTimeImmutable;
use SensitiveParameter;
use Tests\Fixtures\Enums\UserStatus;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class NestedPayloadData extends AbstractData
{
    public function __construct(
        public UserStatus $status,
        public DateTimeImmutable $createdAt,
        public SongData $song,
        public array $payload,
        #[SensitiveParameter()]
        public string $secret = 'hidden',
    ) {}
}
