<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Enums\UndefinedValues;
use Illuminate\Contracts\Config\Repository;
use Tests\Fixtures\Data\LenientUndefinedData;

describe('Policy', function (): void {
    beforeEach(function (): void {
        resolve(Repository::class)->set('struct.undefined_values', UndefinedValues::Forbid);
    });

    describe('Happy Paths', function (): void {
        test('allows undefined nullable values when dto explicitly opts in', function (): void {
            // Arrange
            $dto = LenientUndefinedData::create([
                'name' => 'Brian',
            ]);

            // Act
            $email = $dto->email;

            // Assert
            expect($email)->toBeNull();
        });
    });
});
