<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Exceptions\InvalidCollectionAttributeException;
use Cline\Struct\Support\LazyDataCollection;
use Cline\Struct\Support\LazyDataList;
use Tests\Fixtures\Data\InvalidLazyCollectionAttributeData;
use Tests\Fixtures\Data\LazyListData;
use Tests\Fixtures\Data\LazySongCollectionData;
use Tests\Fixtures\Data\SongData;

describe('Lazy collection hydration', function (): void {
    test('hydrates lazy data lists from traversable input without eager consumption', function (): void {
        $consumed = 0;
        $input = [
            'numbers' => (function () use (&$consumed): Generator {
                ++$consumed;

                yield '1';
                ++$consumed;

                yield 2;
                ++$consumed;

                yield '3';
            })(),
        ];

        $dto = LazyListData::create($input);

        expect($dto->numbers)->toBeInstanceOf(LazyDataList::class)
            ->and($consumed)->toBe(0)
            ->and($dto->numbers->first())->toBe(1)
            ->and($consumed)->toBe(1)
            ->and($dto->numbers->toArray())->toBe([1, 2, 3])
            ->and($consumed)->toBe(3);
    });

    test('hydrates lazy data collections from traversable input while preserving keys', function (): void {
        $consumed = 0;
        $input = [
            'songs' => (function () use (&$consumed): Generator {
                ++$consumed;

                yield 'first' => ['title' => 'A', 'artist' => 'Artist A'];
                ++$consumed;

                yield 'second' => ['title' => 'B', 'artist' => 'Artist B'];
            })(),
        ];

        $dto = LazySongCollectionData::create($input);

        expect($dto->songs)->toBeInstanceOf(LazyDataCollection::class)
            ->and($consumed)->toBe(0)
            ->and($dto->songs->first())->toBeInstanceOf(SongData::class)
            ->and($dto->songs->first()?->title)->toBe('A')
            ->and($consumed)->toBe(1)
            ->and($dto->songs->toArray())->toBe([
                'first' => ['title' => 'A', 'artist' => 'Artist A'],
                'second' => ['title' => 'B', 'artist' => 'Artist B'],
            ])
            ->and($consumed)->toBe(2);
    });

    test('rejects collection transform attributes on lazy data wrappers', function (): void {
        expect(fn (): InvalidLazyCollectionAttributeData => InvalidLazyCollectionAttributeData::create([
            'list' => [1, 2, 3],
        ]))->toThrow(InvalidCollectionAttributeException::class);
    });
});
