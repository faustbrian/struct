<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Stringifiers;

use Cline\Struct\Contracts\DataObjectInterface;
use Cline\Struct\Contracts\StringifierInterface;
use Cline\Struct\Serialization\SerializationOptions;
use Symfony\Component\Yaml\Yaml;

/**
 * Serializes data objects into YAML.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class YamlStringifier implements StringifierInterface
{
    public function stringify(
        DataObjectInterface $dto,
        ?SerializationOptions $options = null,
    ): string {
        return Yaml::dump($dto->toArray(serialization: $options ?? new SerializationOptions()));
    }
}
