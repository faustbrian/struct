<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tests\Fixtures\Data\FactoryUserData;
use Tests\Fixtures\Data\LifecycleFactoryData;

describe('Factory', function (): void {
    beforeEach(function (): void {
        // Arrange
        $this->userFactory = FactoryUserData::factory()->verified();
        $this->lifecycleFactory = LifecycleFactoryData::factory();
    });

    describe('Happy', function (): void {
        test('builds dto instances through a laravel-style factory API', function (): void {
            // Arrange
            $factory = $this->userFactory
                ->sequence(
                    ['name' => 'First'],
                    ['name' => 'Second'],
                )
                ->count(2);

            // Act
            $items = $factory->make();

            // Assert
            expect($items)->toHaveCount(2)
                ->and($items[0]->name)->toBe('First')
                ->and($items[0]->verified)->toBeTrue()
                ->and($items[1]->name)->toBe('Second');
        });

        test('supports makeOne raw and create aliases', function (): void {
            // Arrange
            $factory = $this->userFactory;

            // Act
            $raw = $factory->raw([
                'name' => 'Raw',
            ]);
            $one = $factory->makeOne([
                'name' => 'One',
            ]);
            $created = $factory->times(2)->create([
                'name' => 'Created',
            ]);

            // Assert
            expect($raw)->toBe([
                'name' => 'Raw',
                'verified' => true,
            ])->and($one->name)->toBe('One')
                ->and($one->verified)->toBeTrue()
                ->and($created)->toHaveCount(2)
                ->and($created[0]->name)->toBe('Created');
        });
    });

    describe('Edge', function (): void {
        test('applies configured lifecycle callbacks for immutable dtos', function (): void {
            // Arrange
            $factory = $this->lifecycleFactory;

            // Act
            $made = $factory->makeOne([
                'name' => 'Brian',
            ]);
            $created = $factory->createOne([
                'name' => 'Brian',
            ]);

            // Assert
            expect($made->name)->toBe('Brian-made')
                ->and($created->name)->toBe('Brian-made-created');
        });

        test('supports lazy dto generation for multiple instances', function (): void {
            // Arrange
            $factory = $this->userFactory->times(3);

            // Act
            $items = $factory->lazy([
                'name' => 'Lazy',
            ])->all();

            // Assert
            expect($items)->toHaveCount(3)
                ->and($items[0]->name)->toBe('Lazy');
        });
    });

    describe('Sad', function (): void {});
});
