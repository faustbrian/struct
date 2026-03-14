<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Contracts;

/**
 * Declares a meta attribute that wraps the next Laravel collection transform.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface WrapsLaravelCollectionTransformInterface
{
    public function wrap(TransformsLaravelCollectionValueInterface $next): TransformsLaravelCollectionValueInterface;
}
