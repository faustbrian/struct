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
use Cline\Struct\Attributes\Validate;
use Cline\Struct\Enums\DataListType;
use Cline\Struct\Enums\NameMapper;
use Cline\Struct\Support\DataList;
use Cline\Struct\Support\Optional;
use Tests\Fixtures\Enums\UserStatus;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[MapName(NameMapper::SnakeCase)]
final readonly class MappedUserData extends AbstractData
{
    public function __construct(
        public int $id,
        #[Validate('required|min:3')]
        public string $fullName,
        public CarbonImmutable $createdAt,
        public UserStatus $status,
        #[AsDataList(DataListType::Int)]
        public DataList $tags = new DataList(),
        public Optional|int $age = new Optional(),
        public Optional|string|null $email = new Optional(),
    ) {}
}
