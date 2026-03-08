<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Exceptions\ImmutableDataListException;
use Cline\Struct\Exceptions\MissingDataListIndexException;
use Cline\Struct\Support\DataList;

describe('DataList', function (): void {
    test('returns items by index', function (): void {
        // Arrange
        $list = new DataList(['first', 'second']);

        // Act
        $value = $list->get(1);

        // Assert
        expect($value)->toBe('second');
    });

    test('throws when reading an index that does not exist', function (): void {
        // Arrange
        $list = new DataList(['first']);

        // Act & Assert
        expect(fn (): mixed => $list->get(2))->toThrow(MissingDataListIndexException::class);
    });

    test('throws when mutating the immutable list', function (): void {
        // Arrange
        $list = new DataList(['first']);

        // Act & Assert
        expect(function () use ($list): void {
            $list[] = 'second';
        })->toThrow(ImmutableDataListException::class);

        expect(function () use ($list): void {
            unset($list[0]);
        })->toThrow(ImmutableDataListException::class);
    });
});
