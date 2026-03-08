<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support;

use Cline\Struct\Contracts\ComputesValueInterface;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class LazyDisplayNameComputer implements ComputesValueInterface
{
    public function compute(array $properties): mixed
    {
        return $properties['name'].' profile';
    }
}
