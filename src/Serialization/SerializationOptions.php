<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Serialization;

use Cline\Struct\Support\DateFormat;

use function array_fill_keys;
use function array_merge;
use function array_values;
use function explode;
use function str_starts_with;
use function strpos;
use function substr;

/**
 * Immutable options object that drives conditional serialization behavior.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class SerializationOptions
{
    /** @var array<int, string> */
    public array $include;

    /** @var array<int, string> */
    public array $exclude;

    /** @var array<int, string> */
    public array $groups;

    public DateFormat $date;

    /** @var array<string, true> */
    private array $includeLookup;

    /** @var array<string, true> */
    private array $excludeLookup;

    /** @var array<string, true> */
    private array $groupLookup;

    /** @var array<string, array<int, string>> */
    private array $scopedIncludesByPath;

    /** @var array<string, array<int, string>> */
    private array $scopedExcludesByPath;

    private SerializationOptionsCache $cache;

    private bool $hasIncludeWildcard;

    private bool $hasExcludeWildcard;

    private bool $hasScopedPaths;

    private bool $hasScopedIncludes;

    private bool $usesDefaultProjection;

    /**
     * @param array<int, string>   $include
     * @param array<int, string>   $exclude
     * @param array<int, string>   $groups
     * @param array<string, mixed> $context
     */
    public function __construct(
        public bool $includeSensitive = false,
        array $include = [],
        array $exclude = [],
        array $groups = [],
        /** @var array<string, mixed> */
        public array $context = [],
        ?DateFormat $date = null,
    ) {
        if ($include === [] && $exclude === [] && $groups === [] && $this->context === []) {
            $this->include = [];
            $this->exclude = [];
            $this->groups = [];
            $this->includeLookup = [];
            $this->excludeLookup = [];
            $this->groupLookup = [];
            $this->scopedIncludesByPath = [];
            $this->scopedExcludesByPath = [];
            $this->hasIncludeWildcard = false;
            $this->hasExcludeWildcard = false;
            $this->hasScopedPaths = false;
            $this->hasScopedIncludes = false;
            $this->usesDefaultProjection = true;
            $this->date = $date ?? DateFormat::fromConfig();
            $this->cache = new SerializationOptionsCache();

            return;
        }

        $this->include = array_values($include);
        $this->exclude = array_values($exclude);
        $this->groups = array_values($groups);

        $this->includeLookup = array_fill_keys($this->include, true);
        $this->excludeLookup = array_fill_keys($this->exclude, true);
        $this->groupLookup = array_fill_keys($this->groups, true);
        $this->scopedIncludesByPath = $this->indexScopedPaths($this->include);
        $this->scopedExcludesByPath = $this->indexScopedPaths($this->exclude);
        $this->hasIncludeWildcard = isset($this->includeLookup['*']);
        $this->hasExcludeWildcard = isset($this->excludeLookup['*']);
        $this->hasScopedPaths = $this->scopedIncludesByPath !== []
            || $this->scopedExcludesByPath !== [];
        $this->hasScopedIncludes = $this->scopedIncludesByPath !== [];
        $this->usesDefaultProjection = $this->include === []
            && $this->exclude === []
            && $this->groups === []
            && $this->context === [];
        $this->date = $date ?? DateFormat::fromConfig();
        $this->cache = new SerializationOptionsCache();
    }

    /**
     * Return a copy with additional include paths.
     */
    public function withInclude(string ...$paths): self
    {
        return new self(
            includeSensitive: $this->includeSensitive,
            include: array_values(array_merge($this->include, $paths)),
            exclude: $this->exclude,
            groups: $this->groups,
            context: $this->context,
            date: $this->date,
        );
    }

    /**
     * Return a copy with additional exclude paths.
     */
    public function withExclude(string ...$paths): self
    {
        return new self(
            includeSensitive: $this->includeSensitive,
            include: $this->include,
            exclude: array_values(array_merge($this->exclude, $paths)),
            groups: $this->groups,
            context: $this->context,
            date: $this->date,
        );
    }

    /**
     * Return a copy with additional active serialization groups.
     */
    public function withGroups(string ...$groups): self
    {
        return new self(
            includeSensitive: $this->includeSensitive,
            include: $this->include,
            exclude: $this->exclude,
            groups: array_values(array_merge($this->groups, $groups)),
            context: $this->context,
            date: $this->date,
        );
    }

    /**
     * Return a copy with merged serialization context values.
     *
     * @param array<string, mixed> $context
     */
    public function withContext(array $context): self
    {
        return new self(
            includeSensitive: $this->includeSensitive,
            include: $this->include,
            exclude: $this->exclude,
            groups: $this->groups,
            context: [...$this->context, ...$context],
            date: $this->date,
        );
    }

    public function hasScopedPaths(): bool
    {
        return $this->hasScopedPaths;
    }

    /**
     * Return a copy with sensitive fields enabled or disabled.
     */
    public function withSensitive(bool $includeSensitive = true): self
    {
        return new self(
            includeSensitive: $includeSensitive,
            include: $this->include,
            exclude: $this->exclude,
            groups: $this->groups,
            context: $this->context,
            date: $this->date,
        );
    }

    /**
     * Determine whether a path is explicitly included.
     */
    public function shouldIncludePath(string $path): bool
    {
        if ($this->hasIncludeWildcard) {
            return true;
        }

        if (isset($this->includeLookup[$path])) {
            return true;
        }

        if (!$this->hasScopedIncludes) {
            return false;
        }

        return isset($this->scopedIncludesByPath[$path]);
    }

    /**
     * Determine whether a path is explicitly excluded.
     */
    public function shouldExcludePath(string $path): bool
    {
        return $this->hasExcludeWildcard || isset($this->excludeLookup[$path]);
    }

    /**
     * Determine whether any of the property's groups are active.
     *
     * @param array<int, string> $propertyGroups
     */
    public function includesGroup(array $propertyGroups): bool
    {
        foreach ($propertyGroups as $group) {
            if (isset($this->groupLookup[$group])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return child options scoped to a nested path.
     */
    public function child(string $path): self
    {
        if (!$this->hasScopedPaths) {
            return $this;
        }

        if (isset($this->cache->childOptionsByPath[$path])) {
            return $this->cache->childOptionsByPath[$path];
        }

        $include = $this->scopedIncludesByPath[$path] ?? null;
        $exclude = $this->scopedExcludesByPath[$path] ?? null;

        if ($include !== null || $exclude !== null) {
            return $this->cache->childOptionsByPath[$path] = $this->newChildOptions(
                $include ?? [],
                $exclude ?? [],
            );
        }

        if ($this->cache->unscopedChildOptions instanceof self) {
            return $this->cache->unscopedChildOptions;
        }

        return $this->cache->unscopedChildOptions = $this->newChildOptions([], []);
    }

    public function usesDefaultProjection(): bool
    {
        return $this->usesDefaultProjection;
    }

    /**
     * @param  array<int, string>                $paths
     * @return array<string, array<int, string>>
     */
    private function indexScopedPaths(array $paths): array
    {
        $indexed = [];

        foreach ($paths as $candidate) {
            if (strpos($candidate, '.') === false) {
                continue;
            }

            [$path, $child] = explode('.', $candidate, 2);

            if ($child === '') {
                continue;
            }

            if (str_starts_with($child, '*.')) {
                $child = substr($child, 2);
            }

            if ($child === '') {
                continue;
            }

            $indexed[$path][] = $child;
        }

        return $indexed;
    }

    /**
     * @param array<int, string> $include
     * @param array<int, string> $exclude
     */
    private function newChildOptions(array $include, array $exclude): self
    {
        return new self(
            includeSensitive: $this->includeSensitive,
            include: $include,
            exclude: $exclude,
            groups: $this->groups,
            context: $this->context,
            date: $this->date,
        );
    }
}

/**
 * @author Brian Faust <brian@cline.sh>
 * @internal
 */
final class SerializationOptionsCache
{
    /** @var array<string, SerializationOptions> */
    public array $childOptionsByPath = [];

    public ?SerializationOptions $unscopedChildOptions = null;
}
