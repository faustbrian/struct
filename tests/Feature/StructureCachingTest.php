<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Metadata\MetadataFactory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Tests\Fixtures\Data\MappedUserData;
use Tests\Fixtures\Data\SongData;

describe('Structure caching', function (): void {
    beforeEach(function (): void {
        $fixturesDirectory = __DIR__.'/../Fixtures/Data';

        config()->set('cache.default', 'array');
        config()->set('cache.stores.array', ['driver' => 'array']);
        config()->set('struct.structure_caching', [
            'enabled' => true,
            'directories' => [$fixturesDirectory],
            'cache' => [
                'store' => 'array',
                'prefix' => 'struct-test',
                'duration' => null,
            ],
            'reflection_discovery' => [
                'enabled' => true,
                'base_path' => base_path('tests/Fixtures'),
                'root_namespace' => 'Tests\\Fixtures',
            ],
        ]);

        clearMetadataFactoryRuntimeCache();
        Cache::store('array')->clear();
    });

    test('persists metadata in the configured cache store and restores it after runtime cache reset', function (): void {
        // Arrange
        $factory = resolve(MetadataFactory::class);

        // Act
        $factory->for(MappedUserData::class);

        $payload = Cache::store('array')->get(structureCacheKey(MappedUserData::class));

        expect($payload)->toBeArray();

        $payload['properties']['fullName']['outputName'] = 'headline';
        Cache::store('array')->forever(structureCacheKey(MappedUserData::class), $payload);

        clearMetadataFactoryRuntimeCache();

        $metadata = $factory->for(MappedUserData::class);

        // Assert
        expect($metadata->properties['fullName']->outputName)->toBe('headline');
    });

    test('warms and clears discovered dto metadata with artisan commands', function (): void {
        // Act
        $cacheExitCode = Artisan::call('struct:cache');

        // Assert
        expect($cacheExitCode)->toBe(0)
            ->and(Cache::store('array')->has(structureCacheKey(SongData::class)))->toBeTrue();

        // Act
        $clearExitCode = Artisan::call('struct:clear');

        // Assert
        expect($clearExitCode)->toBe(0)
            ->and(Cache::store('array')->has(structureCacheKey(SongData::class)))->toBeFalse();
    });

    test('discovers dto classes from configured directories without a root namespace', function (): void {
        // Arrange
        config()->set('struct.structure_caching.reflection_discovery.root_namespace');

        // Act
        $factory = resolve(MetadataFactory::class);
        $classes = $factory->discoverDataClasses();
        $property = new ReflectionProperty($factory, 'discoveredClasses');

        // Assert
        expect($classes)->toContain(SongData::class, MappedUserData::class)
            ->and($property->getValue($factory))->toBe($classes)
            ->and($factory->discoverDataClasses())->toBe($classes);
    });
});

function clearMetadataFactoryRuntimeCache(): void
{
    resolve(MetadataFactory::class)->clearRuntimeCache();
}

function structureCacheKey(string $class): string
{
    return 'struct-test:metadata:'.$class;
}
