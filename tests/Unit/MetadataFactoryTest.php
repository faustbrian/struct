<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Metadata\MetadataFactory;
use Tests\Fixtures\Casts\CountingCast;
use Tests\Fixtures\Data\CountedCastData;

describe('MetadataFactory', function (): void {
    beforeEach(function (): void {
        CountingCast::$instances = 0;
        resolve(MetadataFactory::class)->clearRuntimeCache();
    });

    test('defers reflected cast construction until first use', function (): void {
        $metadata = resolve(MetadataFactory::class)->for(CountedCastData::class);

        expect(CountingCast::$instances)->toBe(0);

        $metadata->properties['first']->cast?->get($metadata->properties['first'], 'value');
        $metadata->properties['second']->cast?->get($metadata->properties['second'], 'value');

        expect(CountingCast::$instances)->toBe(1)
            ->and($metadata->properties['first']->cast)
            ->toBe($metadata->properties['second']->cast);
    });

    test('short-circuits empty property attribute scans', function (): void {
        $factory = resolve(MetadataFactory::class);
        $method = new ReflectionMethod($factory, 'inspectPropertyAttributes');

        $attributes = $method->invoke($factory, []);

        expect($attributes->validationRules)->toBe([])
            ->and($attributes->itemValidationRules)->toBe([])
            ->and($attributes->lazyGroups)->toBe([])
            ->and($attributes->includeConditions)->toBe([])
            ->and($attributes->excludeConditions)->toBe([])
            ->and($attributes->castClass)->toBeNull();
    });

    test('reuses the resolved cache repository within one factory instance', function (): void {
        $factory = resolve(MetadataFactory::class);
        $property = new ReflectionProperty($factory, 'cacheStoreRepository');
        $method = new ReflectionMethod($factory, 'cacheStore');

        expect($property->getValue($factory))->toBeNull();

        $first = $method->invoke($factory);
        $second = $method->invoke($factory);

        expect($property->getValue($factory))->toBe($first)
            ->and($second)->toBe($first);
    });
});
