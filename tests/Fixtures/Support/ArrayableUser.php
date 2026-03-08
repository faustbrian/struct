<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class ArrayableUser implements Arrayable
{
    public function __construct(
        private string $name,
        private bool $verified,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'verified' => $this->verified,
        ];
    }
}
