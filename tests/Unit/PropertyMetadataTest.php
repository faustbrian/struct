<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Metadata\PropertyMetadata;
use Tests\Fixtures\Casts\CountingCast;
use Tests\Fixtures\Data\MappedUserData;

describe('PropertyMetadata', function (): void {
    beforeEach(function (): void {
        CountingCast::$instances = 0;
    });

    test('defers cached cast construction until first use', function (): void {
        $reflection = new ReflectionClass(MappedUserData::class);
        $parameter = $reflection->getConstructor()->getParameters()[0];
        $property = PropertyMetadata::fromCachePayload(
            MappedUserData::class,
            $reflection,
            $parameter,
            [
                'name' => 'id',
                'inputName' => 'id',
                'outputName' => 'id',
                'types' => ['string'],
                'typeKinds' => ['string'],
                'castClass' => CountingCast::class,
                'isEncrypted' => true,
            ],
        );

        expect(CountingCast::$instances)->toBe(0);

        $property->cast?->get($property, 'value');
        $property->cast?->set($property, 'value');

        expect(CountingCast::$instances)->toBe(1)
            ->and($property->isEncrypted)->toBeTrue();
    });
});
