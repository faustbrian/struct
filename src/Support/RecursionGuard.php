<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Support;

use Cline\Struct\Exceptions\RecursiveSerializationException;

use function spl_object_id;

/**
 * Tracks serialized objects to prevent recursive serialization loops.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class RecursionGuard
{
    /** @var array<int, true> */
    private array $objectIds = [];

    /**
     * Mark an object as currently being serialized.
     *
     * @throws RecursiveSerializationException
     */
    public function enter(object $object): void
    {
        $objectId = spl_object_id($object);

        if (isset($this->objectIds[$objectId])) {
            throw RecursiveSerializationException::detected($object::class.'#'.$objectId);
        }

        $this->objectIds[$objectId] = true;
    }

    /**
     * Remove an object from the active serialization stack.
     */
    public function leave(object $object): void
    {
        unset($this->objectIds[spl_object_id($object)]);
    }
}
