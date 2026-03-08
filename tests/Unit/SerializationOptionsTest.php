<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Serialization\SerializationOptions;

describe('SerializationOptions', function (): void {
    test('reuses the same instance when no nested paths are configured', function (): void {
        // Arrange
        $options = new SerializationOptions();

        // Act
        $child = $options->child('posts');

        // Assert
        expect($child)->toBe($options);
    });

    test('trims nested include and exclude paths for children', function (): void {
        // Arrange
        $options = new SerializationOptions(
            include: ['posts.author.profile.bio'],
            exclude: ['posts.author.email'],
        );

        // Act
        $child = $options->child('posts');

        // Assert
        expect($child)->not->toBe($options)
            ->and($child->include)->toBe(['author.profile.bio'])
            ->and($child->exclude)->toBe(['author.email']);
    });

    test('tracks whether scoped include/exclude paths are configured', function (): void {
        // Arrange
        $default = new SerializationOptions();
        $scoped = new SerializationOptions(
            include: ['posts.author.profile.bio'],
            exclude: ['posts.author.email'],
        );

        // Act / Assert
        expect($default->hasScopedPaths())->toBeFalse()
            ->and($scoped->hasScopedPaths())->toBeTrue();
    });

    test('captures date format settings from config once per options instance', function (): void {
        // Arrange
        config([
            'struct.date_format' => 'Y-m-d H:i:s',
            'struct.date_timezone' => 'America/New_York',
        ]);
        $options = new SerializationOptions();

        // Assert
        expect($options->date->format)->toBe('Y-m-d H:i:s')
            ->and($options->date->timezone)->toBe('America/New_York');
    });

    test('reuses the configured date format across default option instances', function (): void {
        config([
            'struct.date_format' => 'Y-m-d H:i:s',
            'struct.date_timezone' => 'America/New_York',
        ]);

        $first = new SerializationOptions();
        $second = new SerializationOptions();

        expect($first->date)->toBe($second->date)
            ->and($first->date->format)->toBe('Y-m-d H:i:s')
            ->and($first->date->timezone)->toBe('America/New_York');
    });

    test('reuses the container default options instance', function (): void {
        $first = resolve(SerializationOptions::class);
        $second = resolve(SerializationOptions::class);

        expect($first)->toBe($second)
            ->and($first->usesDefaultProjection())->toBeTrue()
            ->and($first->child('posts'))->toBe($first);
    });

    test('reuses the resolved date configuration across derived option instances', function (): void {
        config([
            'struct.date_format' => 'Y-m-d H:i:s',
            'struct.date_timezone' => 'America/New_York',
        ]);
        $options = new SerializationOptions(include: ['posts.author.profile.bio']);

        expect($options->child('posts')->date)->toBe($options->date)
            ->and($options->withInclude('posts')->date)->toBe($options->date)
            ->and($options->withExclude('email')->date)->toBe($options->date)
            ->and($options->withGroups('details')->date)->toBe($options->date)
            ->and($options->withContext(['viewer' => 'admin'])->date)->toBe($options->date)
            ->and($options->withSensitive()->date)->toBe($options->date);
    });

    test('materializes scoped child options lazily', function (): void {
        $options = new SerializationOptions(
            include: ['posts.author.profile.bio'],
            exclude: ['posts.author.email'],
        );
        $cache = new ReflectionProperty($options, 'cache');

        $childOptions = new ReflectionProperty($cache->getValue($options), 'childOptionsByPath');

        expect($childOptions->getValue($cache->getValue($options)))->toBe([]);

        $child = $options->child('posts');

        expect($child->include)->toBe(['author.profile.bio'])
            ->and($child->exclude)->toBe(['author.email'])
            ->and($childOptions->getValue($cache->getValue($options)))->toHaveKey('posts')
            ->and($options->child('posts'))->toBe($child);
    });
});
