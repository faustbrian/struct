<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\Computed;
use Cline\Struct\Attributes\MapName;
use Cline\Struct\Attributes\StringifyUsing;
use Cline\Struct\Enums\NameMapper;
use Cline\Struct\Stringifiers\JsonStringifier;
use SensitiveParameter;
use Tests\Fixtures\Support\DisplayNameComputer;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[MapName(NameMapper::SnakeCase)]
#[StringifyUsing(JsonStringifier::class)]
final readonly class ComputedUserData extends AbstractData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        #[SensitiveParameter()]
        public string $password,
        #[Computed(DisplayNameComputer::class)]
        public string $displayName = '',
    ) {}
}
