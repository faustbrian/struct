<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Exceptions\RecursiveHydrationException;
use Tests\Fixtures\Data\RecursiveInputData;

describe('RecursiveInputData hydration', function (): void {
    beforeEach(function (): void {
        $payload = [];
        $payload['self'] = &$payload;

        $this->payload = $payload;
    });

    describe('Happy Paths', function (): void {
        // Reserved for non-recursive hydration behavior.
    });

    describe('Sad Paths', function (): void {
        test('throws RecursiveHydrationException for self-referential payload', function (): void {
            // Arrange
            $payload = $this->payload;

            // Act
            $action = fn (): RecursiveInputData => RecursiveInputData::create([
                'payload' => $payload,
            ]);

            // Assert
            expect($action)->toThrow(RecursiveHydrationException::class);
        });
    });

    describe('Edge Cases', function (): void {
        // Reserved for boundary payload shapes or unusual recursive structures.
    });

    describe('Regressions', function (): void {
        // Reserved for bug-numbered regression scenarios.
    });
});
