<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Support;

use Cline\Struct\Exceptions\RecursiveHydrationException;
use ReflectionReference;

use function get_debug_type;
use function get_object_vars;
use function is_array;
use function is_object;
use function spl_object_id;

/**
 * Detects recursive references before nested payloads are hydrated.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class HydrationGuard
{
    /** @var array<int|string, true> */
    private array $activeReferences = [];

    /** @var array<int, true> */
    private array $activeObjects = [];

    /**
     * Assert that the given payload does not contain recursive references.
     *
     * @param  array<mixed>                $input
     * @throws RecursiveHydrationException
     */
    public function assertNoRecursion(array &$input): void
    {
        $this->inspectArray($input, 'root');
    }

    /**
     * Walk an array branch while tracking reference cycles.
     *
     * @param  array<mixed>                $input
     * @throws RecursiveHydrationException
     */
    private function inspectArray(array &$input, string $context): void
    {
        foreach ($input as $key => &$value) {
            $reference = ReflectionReference::fromArrayElement($input, $key);
            $referenceId = $reference?->getId();

            if ($referenceId === null) {
                $this->inspectValue($value, $context.'.'.$key);

                continue;
            }

            if (isset($this->activeReferences[$referenceId])) {
                throw RecursiveHydrationException::detected($context.'.'.$key);
            }

            $this->activeReferences[$referenceId] = true;
            $this->inspectValue($value, $context.'.'.$key);
            unset($this->activeReferences[$referenceId]);
        }
    }

    /**
     * Walk a scalar, array, or object value while tracking object cycles.
     *
     * @throws RecursiveHydrationException
     */
    private function inspectValue(mixed &$value, string $context): void
    {
        if (is_array($value)) {
            $this->inspectArray($value, $context);

            return;
        }

        if (!is_object($value)) {
            return;
        }

        $objectId = spl_object_id($value);

        if (isset($this->activeObjects[$objectId])) {
            throw RecursiveHydrationException::detected($context.'<'.get_debug_type($value).'>');
        }

        $this->activeObjects[$objectId] = true;

        $properties = get_object_vars($value);

        foreach ($properties as $property => &$propertyValue) {
            $this->inspectValue($propertyValue, $context.'.'.$property);
        }

        unset($this->activeObjects[$objectId]);
    }
}
