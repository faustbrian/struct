<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Serialization;

use Cline\Struct\Contracts\DataObjectInterface;

use function resolve;

/**
 * Fluent serializer wrapper for applying options to a data object.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class DataSerializer
{
    private SerializationOptions $options;

    /**
     * @param DataObjectInterface  $data    Data object being serialized.
     * @param SerializationOptions $options Serialization options applied to the object.
     */
    public function __construct(
        private DataObjectInterface $data,
        ?SerializationOptions $options = null,
    ) {
        $this->options = $options ?? resolve(SerializationOptions::class);
    }

    /**
     * Return a serializer copy with additional include paths.
     */
    public function include(string ...$paths): self
    {
        return new self($this->data, $this->options->withInclude(...$paths));
    }

    /**
     * Return a serializer copy with additional exclude paths.
     */
    public function exclude(string ...$paths): self
    {
        return new self($this->data, $this->options->withExclude(...$paths));
    }

    /**
     * Return a serializer copy with additional serialization groups.
     */
    public function groups(string ...$groups): self
    {
        return new self($this->data, $this->options->withGroups(...$groups));
    }

    /**
     * Return a serializer copy with merged context.
     *
     * @param array<string, mixed> $context
     */
    public function context(array $context): self
    {
        return new self($this->data, $this->options->withContext($context));
    }

    /**
     * Return a serializer copy with sensitive fields enabled or disabled.
     */
    public function includeSensitive(bool $include = true): self
    {
        return new self($this->data, $this->options->withSensitive($include));
    }

    /**
     * Serialize the data object to an array using the current options.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data->toArray(
            includeSensitive: $this->options->includeSensitive,
            include: $this->options->include,
            exclude: $this->options->exclude,
            groups: $this->options->groups,
            context: $this->options->context,
        );
    }

    /**
     * Serialize the data object to JSON using the current options.
     */
    public function toJson(int $options = 0): string
    {
        return $this->data->toJson(
            options: $options,
            includeSensitive: $this->options->includeSensitive,
            include: $this->options->include,
            exclude: $this->options->exclude,
            groups: $this->options->groups,
            context: $this->options->context,
        );
    }
}
