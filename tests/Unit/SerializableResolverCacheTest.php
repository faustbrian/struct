<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Serialization\SerializableResolverCache;
use Tests\Fixtures\Support\AdminVisibilityCondition;

describe('SerializableResolverCache', function (): void {
    test('returns null for missing classes without throwing', function (): void {
        $cache = new SerializableResolverCache();

        expect($cache->resolve('Tests\\Fixtures\\Support\\MissingSerializable'))->toBeNull()
            ->and($cache->resolve('Tests\\Fixtures\\Support\\MissingSerializable'))->toBeNull();
    });

    test('instantiates unbound serializables without routing through the container', function (): void {
        $cache = new SerializableResolverCache();
        $resolves = 0;

        app()->resolving(AdminVisibilityCondition::class, function () use (&$resolves): void {
            ++$resolves;
        });

        expect($cache->resolve(AdminVisibilityCondition::class))
            ->toBeInstanceOf(AdminVisibilityCondition::class)
            ->and($cache->resolve(AdminVisibilityCondition::class))
            ->toBeInstanceOf(AdminVisibilityCondition::class)
            ->and($resolves)->toBe(0);
    });

    test('caches resolver strategies after the first lookup', function (): void {
        $cache = new SerializableResolverCache();
        $property = new ReflectionProperty($cache, 'strategies');

        expect($property->getValue($cache))->toBe([]);

        $cache->resolve(AdminVisibilityCondition::class);
        $strategies = $property->getValue($cache);

        expect($strategies)->toHaveKey(AdminVisibilityCondition::class)
            ->and($strategies[AdminVisibilityCondition::class])->toBe('direct');
    });
});
