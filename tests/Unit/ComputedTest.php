<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tests\Fixtures\Data\MultiComputedData;

describe('MultiComputedData', function (): void {
    beforeEach(function (): void {
        $this->dto = MultiComputedData::create([
            'first_name' => 'Brian',
            'last_name' => 'Faust',
        ]);
    });

    describe('Happy Paths', function (): void {
        test('returns computed full name and keeps summary as raw attribute list', function (): void {
            // Arrange
            $dto = $this->dto;

            // Act
            $fullName = $dto->fullName;
            $summary = $dto->summary;

            // Assert
            expect($fullName)->toBe('Brian Faust')
                ->and($summary)->toBe('firstName,lastName');
        });
    });

    describe('Sad Paths', function (): void {});

    describe('Edge Cases', function (): void {});

    describe('Regressions', function (): void {});
});
