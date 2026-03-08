<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Eloquent\AsDataCollection;
use Tests\Fixtures\Data\FactoryUserData;

describe('Eloquent Compatibility', function (): void {
    beforeEach(function (): void {
        $this->caster = AsDataCollection::of(FactoryUserData::class);
    });

    describe('Happy Paths', function (): void {
        test('casts an eloquent compatibility caster with the expected string format', function (): void {
            // Arrange

            // Act
            $actual = (string) $this->caster;

            // Assert
            expect($actual)->toBe(AsDataCollection::class.':'.FactoryUserData::class);
        });
    });

    describe('Sad Paths', function (): void {});

    describe('Edge Cases', function (): void {});
});
