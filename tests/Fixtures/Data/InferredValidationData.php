<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Carbon\CarbonImmutable;
use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsDataList;
use Cline\Struct\Attributes\MapName;
use Cline\Struct\Enums\DataListType;
use Cline\Struct\Enums\NameMapper;
use Cline\Struct\Support\DataList;
use Tests\Fixtures\Enums\UserStatus;

/**
 * @psalm-immutable
 * @author Brian Faust <brian@cline.sh>
 */
#[MapName(NameMapper::SnakeCase)]
final readonly class InferredValidationData extends AbstractData
{
    public function __construct(
        public string $name,
        public int $age,
        public bool $active,
        public UserStatus $status,
        public CarbonImmutable $publishedAt,
        #[AsDataList(DataListType::Int)]
        public DataList $scores,
    ) {}
}
