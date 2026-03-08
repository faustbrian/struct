<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Metadata\MetadataFactory;
use Cline\Struct\Resolvers\DefaultModelPayloadResolver;
use Cline\Struct\Resolvers\DefaultRequestPayloadResolver;
use Cline\Struct\Support\CreationContext;
use Cline\Struct\Validation\ValidationFactory;
use Tests\Fixtures\Data\NestedPayloadData;
use Tests\Fixtures\Data\SongData;
use Tests\Fixtures\Support\DisplayNameComputer;

describe('CreationContext', function (): void {
    test('reuses nested dto contexts for the same class', function (): void {
        $context = new CreationContext(
            resolve(MetadataFactory::class)->for(NestedPayloadData::class),
        );

        $child = $context->child(SongData::class);
        $sameChild = $context->child(SongData::class);

        expect($child)->toBeInstanceOf(CreationContext::class)
            ->and($child)->toBe($sameChild)
            ->and($child->metadata->class)->toBe(SongData::class);
    });

    test('shares resolved helper caches with child contexts', function (): void {
        $context = new CreationContext(
            resolve(MetadataFactory::class)->for(NestedPayloadData::class),
        );

        $child = $context->child(SongData::class);

        expect($child->validationFactory())->toBe($context->validationFactory())
            ->and($child->computer(DisplayNameComputer::class))
            ->toBe($context->computer(DisplayNameComputer::class));
    });

    test('shares hydration guards with child contexts', function (): void {
        $context = new CreationContext(
            resolve(MetadataFactory::class)->for(NestedPayloadData::class),
        );

        $child = $context->child(SongData::class);

        expect($child->hydrationGuard())->toBe($context->hydrationGuard());
    });

    test('caches invalid computer lookups without throwing', function (): void {
        $context = new CreationContext(
            resolve(MetadataFactory::class)->for(NestedPayloadData::class),
        );

        expect($context->computer('Tests\\Fixtures\\Support\\MissingComputer'))->toBeNull()
            ->and($context->computer('stdClass'))->toBeNull();
    });

    test('instantiates unbound computers without routing through the container', function (): void {
        $context = new CreationContext(
            resolve(MetadataFactory::class)->for(NestedPayloadData::class),
        );
        $resolves = 0;

        app()->resolving(DisplayNameComputer::class, function () use (&$resolves): void {
            ++$resolves;
        });

        expect($context->computer(DisplayNameComputer::class))
            ->toBeInstanceOf(DisplayNameComputer::class)
            ->and($context->computer(DisplayNameComputer::class))
            ->toBeInstanceOf(DisplayNameComputer::class)
            ->and($resolves)->toBe(0);
    });

    test('caches computer resolution strategies after the first lookup', function (): void {
        $context = new CreationContext(
            resolve(MetadataFactory::class)->for(NestedPayloadData::class),
        );
        $cache = new ReflectionProperty($context, 'cache');
        $strategies = new ReflectionProperty($cache->getValue($context), 'computerStrategies');

        expect($strategies->getValue($cache->getValue($context)))->toBe([]);

        $context->computer(DisplayNameComputer::class);

        expect($strategies->getValue($cache->getValue($context)))->toHaveKey(DisplayNameComputer::class)
            ->and($strategies->getValue($cache->getValue($context))[DisplayNameComputer::class])
            ->toBe('direct');
    });

    test('instantiates default helpers without routing through the container', function (): void {
        $context = new CreationContext(
            resolve(MetadataFactory::class)->for(NestedPayloadData::class),
        );
        $validationFactory = $context->validationFactory();
        $requestResolver = $context->requestPayloadResolver();
        $modelResolver = $context->modelPayloadResolver();

        expect($validationFactory)->toBeInstanceOf(ValidationFactory::class)
            ->and($requestResolver)->toBeInstanceOf(DefaultRequestPayloadResolver::class)
            ->and($modelResolver)->toBeInstanceOf(DefaultModelPayloadResolver::class)
            ->and($context->validationFactory())->toBe($validationFactory)
            ->and($context->requestPayloadResolver())->toBe($requestResolver)
            ->and($context->modelPayloadResolver())->toBe($modelResolver);
    });

    test('caches helper resolution strategies after the first lookup', function (): void {
        $context = new CreationContext(
            resolve(MetadataFactory::class)->for(NestedPayloadData::class),
        );
        $cache = new ReflectionProperty($context, 'cache');
        $strategies = new ReflectionProperty($cache->getValue($context), 'helperStrategies');

        expect($strategies->getValue($cache->getValue($context)))->toBe([]);

        $context->requestPayloadResolver();

        expect($strategies->getValue($cache->getValue($context)))->not->toBe([]);
    });
});
