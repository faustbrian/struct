<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Exceptions\DataValidationException;
use Tests\Fixtures\Data\MappedUserData;

describe('Validation', function (): void {
    beforeEach(function (): void {
        $this->payload = [
            'id' => 1,
            'full_name' => 'No',
            'created_at' => '2026-03-07T10:00:00+00:00',
            'status' => 'active',
        ];
    });

    describe('Happy Paths', function (): void {
        test('creates dto without validation when create is used', function (): void {
            // Arrange
            $payload = $this->payload;

            // Act
            $dto = MappedUserData::create($payload);

            // Assert
            expect($dto->fullName)->toBe('No');
        });
    });

    describe('Sad Paths', function (): void {
        test('throws validation exception when explicit validation is requested', function (): void {
            // Arrange
            $payload = $this->payload;

            // Act & Assert
            expect(fn (): MappedUserData => MappedUserData::createWithValidation($payload))
                ->toThrow(DataValidationException::class);
        });
    });

    describe('Edge Cases', function (): void {});

    describe('Regressions', function (): void {});
});
