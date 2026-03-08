<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\UseFactory;
use Tests\Fixtures\Factories\FactoryUserDataFactory;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[UseFactory(FactoryUserDataFactory::class)]
final readonly class FactoryUserData extends AbstractData
{
    public function __construct(
        public string $name,
        public bool $verified = false,
    ) {}
}
