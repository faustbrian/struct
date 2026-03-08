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
use DOMDocument;
use JsonException;

use const JSON_THROW_ON_ERROR;

use function is_scalar;
use function json_encode;

/**
 * Serializes data objects into a simple XML document.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class XmlStringifier implements StringifierInterface
{
    /**
     * @throws JsonException
     */
    public function stringify(
        DataObjectInterface $dto,
        ?SerializationOptions $options = null,
    ): string {
        $document = new DOMDocument('1.0', 'UTF-8');
        $root = $document->createElement('dto');
        $document->appendChild($root);

        foreach ($dto->toArray(serialization: $options ?? new SerializationOptions()) as $key => $value) {
            $content = is_scalar($value) ? (string) $value : json_encode($value, JSON_THROW_ON_ERROR);
            $node = $document->createElement((string) $key, $content);
            $root->appendChild($node);
        }

        return $document->saveXML() ?: '';
    }
}
