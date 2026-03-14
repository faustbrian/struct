<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Exceptions\AbstractStructInvalidArgumentException;
use Cline\Struct\Support\DataCollection;
use Cline\Struct\Support\DataList;
use Illuminate\Support\Collection;
use Tests\Fixtures\Data\CollectionAttributeData;
use Tests\Fixtures\Data\InvalidCollectionAttributeData;
use Tests\Fixtures\Data\LaravelCollectionAttributeData;
use Tests\Fixtures\Data\SongData;

describe('Collection transforms', function (): void {
    test('hydrates arrays, lists, and collections through collection attributes', function (): void {
        $data = CollectionAttributeData::create([
            'reversedArray' => ['first' => 'A', 'second' => 'B', 'third' => 'C'],
            'reversedList' => [1, 2, 3],
            'reversedCollection' => ['first' => 'A', 'second' => 'B', 'third' => 'C'],
            'cleaned' => ['first' => 'A', 'second' => null, 'third' => '', 'fourth' => 'B'],
            'truthyOnly' => ['zero' => 0, 'name' => 'Taylor', 'blank' => '', 'enabled' => true, 'missing' => null],
            'uniqueStrict' => ['first' => 1, 'second' => '1', 'third' => 1, 'fourth' => '1'],
            'sliced' => ['zero' => 'A', 'one' => 'B', 'two' => 'C', 'three' => 'D'],
            'taken' => ['zero' => 'A', 'one' => 'B', 'two' => 'C'],
            'reindexed' => ['first' => 'A', 'second' => 'B'],
            'onlyKeys' => ['drop' => 'x', 'keep' => 'a', 'also' => 'b'],
            'exceptKeys' => ['keep' => 'a', 'drop' => 'x', 'also' => 'b'],
            'sortedValues' => ['first' => 2, 'second' => 3, 'third' => 1],
            'sortedKeys' => ['beta' => 'b', 'alpha' => 'a', 'gamma' => 'g'],
            'stacked' => ['first' => null, 'second' => 'A', 'third' => 'B', 'fourth' => 'C'],
        ]);

        expect($data->reversedArray)->toBe([
            'third' => 'C',
            'second' => 'B',
            'first' => 'A',
        ])->and($data->reversedList)->toBeInstanceOf(DataList::class)
            ->and($data->reversedList->all())->toBe([3, 2, 1])
            ->and($data->reversedCollection)->toBeInstanceOf(DataCollection::class)
            ->and($data->reversedCollection->all())->toBe([
                'third' => 'C',
                'second' => 'B',
                'first' => 'A',
            ])->and($data->cleaned)->toBe([
                'first' => 'A',
                'fourth' => 'B',
            ])->and($data->truthyOnly)->toBe([
                'name' => 'Taylor',
                'enabled' => true,
            ])->and($data->uniqueStrict)->toBe([
                'first' => 1,
                'second' => '1',
            ])->and($data->sliced)->toBe([
                'one' => 'B',
                'two' => 'C',
            ])->and($data->taken)->toBe([
                'zero' => 'A',
                'one' => 'B',
            ])->and($data->reindexed)->toBe([
                0 => 'A',
                1 => 'B',
            ])->and($data->onlyKeys)->toBe([
                'keep' => 'a',
                'also' => 'b',
            ])->and($data->exceptKeys)->toBe([
                'keep' => 'a',
                'also' => 'b',
            ])->and($data->sortedValues)->toBe([
                'second' => 3,
                'first' => 2,
                'third' => 1,
            ])->and($data->sortedKeys)->toBe([
                'alpha' => 'a',
                'beta' => 'b',
                'gamma' => 'g',
            ])->and($data->stacked)->toBe([
                0 => 'A',
                1 => 'B',
            ])->and($data->toArray())->toBe([
                'reversedArray' => [
                    'third' => 'C',
                    'second' => 'B',
                    'first' => 'A',
                ],
                'reversedList' => [3, 2, 1],
                'reversedCollection' => [
                    'third' => 'C',
                    'second' => 'B',
                    'first' => 'A',
                ],
                'cleaned' => [
                    'first' => 'A',
                    'fourth' => 'B',
                ],
                'truthyOnly' => [
                    'name' => 'Taylor',
                    'enabled' => true,
                ],
                'uniqueStrict' => [
                    'first' => 1,
                    'second' => '1',
                ],
                'sliced' => [
                    'one' => 'B',
                    'two' => 'C',
                ],
                'taken' => [
                    'zero' => 'A',
                    'one' => 'B',
                ],
                'reindexed' => ['A', 'B'],
                'onlyKeys' => [
                    'keep' => 'a',
                    'also' => 'b',
                ],
                'exceptKeys' => [
                    'keep' => 'a',
                    'also' => 'b',
                ],
                'sortedValues' => [
                    'second' => 3,
                    'first' => 2,
                    'third' => 1,
                ],
                'sortedKeys' => [
                    'alpha' => 'a',
                    'beta' => 'b',
                    'gamma' => 'g',
                ],
                'stacked' => ['A', 'B'],
            ]);
    });

    test('rejects key-based collection attributes on DataList properties', function (): void {
        expect(fn (): InvalidCollectionAttributeData => InvalidCollectionAttributeData::create([
            'list' => ['keep', 'drop'],
        ]))->toThrow(AbstractStructInvalidArgumentException::class);
    });

    test('hydrates laravel collections through shared collection attributes', function (): void {
        $data = LaravelCollectionAttributeData::create([
            'reversed' => ['first' => 'A', 'second' => 'B', 'third' => 'C'],
            'numbers' => ['first' => '1', 'second' => 2, 'third' => '3'],
            'onlyKeys' => ['drop' => 'x', 'keep' => 'a', 'also' => 'b'],
            'cleaned' => ['first' => 'A', 'second' => null, 'third' => 'B'],
            'casted' => ['1', 2, '3'],
            'songs' => [
                ['title' => 'A', 'artist' => 'Artist A'],
                ['title' => 'B', 'artist' => 'Artist B'],
            ],
        ]);

        expect($data->reversed)->toBeInstanceOf(Collection::class)
            ->and($data->reversed->all())->toBe([
                'third' => 'C',
                'second' => 'B',
                'first' => 'A',
            ])->and($data->numbers)->toBeInstanceOf(Collection::class)
            ->and($data->numbers->all())->toBe([1, 2, 3])
            ->and($data->onlyKeys->all())->toBe([
                'keep' => 'a',
                'also' => 'b',
            ])->and($data->cleaned->all())->toBe([
                'first' => 'A',
                'third' => 'B',
            ])->and($data->casted->all())->toBe([1, 2, 3])
            ->and($data->songs->all()[0])->toBeInstanceOf(SongData::class)
            ->and($data->toArray())->toBe([
                'reversed' => [
                    'third' => 'C',
                    'second' => 'B',
                    'first' => 'A',
                ],
                'numbers' => [1, 2, 3],
                'onlyKeys' => [
                    'keep' => 'a',
                    'also' => 'b',
                ],
                'cleaned' => [
                    'first' => 'A',
                    'third' => 'B',
                ],
                'casted' => ['1', '2', '3'],
                'songs' => [
                    ['title' => 'A', 'artist' => 'Artist A'],
                    ['title' => 'B', 'artist' => 'Artist B'],
                ],
            ]);
    });
});
