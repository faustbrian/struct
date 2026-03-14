<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Attributes\Strings;

use Attribute;
use Cline\Struct\Exceptions\InvalidGeneratedValueAttributeException;
use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Type\Integer as IntegerObject;
use Ramsey\Uuid\Uuid as RamseyUuid;

use function class_exists;

/**
 * Generates a UUID when the input key is missing.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Uuid extends AbstractGeneratedValueAttribute
{
    public function __construct(
        public int $version = 7,
        public ?int $localDomain = null,
        public int|string|null $localIdentifier = null,
        public ?string $node = null,
        public ?int $clockSeq = null,
        public ?string $namespace = null,
        public ?string $name = null,
        bool $lowerCase = false,
    ) {
        parent::__construct($lowerCase);
    }

    public function generate(): string
    {
        if (!class_exists(RamseyUuid::class)) {
            throw InvalidGeneratedValueAttributeException::forMissingDependency('Uuid', 'ramsey/uuid');
        }

        $uuid = match ($this->version) {
            1 => RamseyUuid::uuid1($this->node, $this->clockSeq),
            2 => RamseyUuid::uuid2(
                $this->localDomain ?? RamseyUuid::DCE_DOMAIN_PERSON,
                $this->localIdentifier === null ? null : new IntegerObject($this->localIdentifier),
                $this->node === null ? null : new Hexadecimal($this->node),
                $this->clockSeq,
            ),
            3 => RamseyUuid::uuid3($this->namespace(), $this->name()),
            4 => RamseyUuid::uuid4(),
            5 => RamseyUuid::uuid5($this->namespace(), $this->name()),
            6 => RamseyUuid::uuid6(
                $this->node === null ? null : new Hexadecimal($this->node),
                $this->clockSeq,
            ),
            7 => RamseyUuid::uuid7(),
            default => throw InvalidGeneratedValueAttributeException::forUnsupportedUuidVersion($this->version),
        };

        return $this->normalize($uuid->toString());
    }

    private function namespace(): string
    {
        return match ($this->namespace) {
            'dns' => RamseyUuid::NAMESPACE_DNS,
            'url' => RamseyUuid::NAMESPACE_URL,
            'oid' => RamseyUuid::NAMESPACE_OID,
            'x500' => RamseyUuid::NAMESPACE_X500,
            null => throw InvalidGeneratedValueAttributeException::forMissingUuidArgument($this->version, 'namespace'),
            default => $this->namespace,
        };
    }

    private function name(): string
    {
        return $this->name
            ?? throw InvalidGeneratedValueAttributeException::forMissingUuidArgument($this->version, 'name');
    }
}
