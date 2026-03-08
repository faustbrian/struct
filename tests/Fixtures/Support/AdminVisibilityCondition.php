<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Support;

use Cline\Struct\Contracts\DataObjectInterface;
use Cline\Struct\Contracts\SerializationConditionInterface;
use Cline\Struct\Metadata\PropertyMetadata;
use Cline\Struct\Serialization\SerializationOptions;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class AdminVisibilityCondition implements SerializationConditionInterface
{
    public function shouldInclude(
        DataObjectInterface $data,
        PropertyMetadata $property,
        SerializationOptions $options,
    ): bool {
        return ($options->context['is_admin'] ?? false) === true;
    }
}
