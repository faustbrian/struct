<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Collections;

use Cline\Struct\Contracts\TransformsCollectionValueInterface;

/**
 * Base attribute for deterministic collection transforms.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
abstract readonly class AbstractCollectionTransformer implements TransformsCollectionValueInterface
{
    public function supportsLists(): bool
    {
        return true;
    }
}
