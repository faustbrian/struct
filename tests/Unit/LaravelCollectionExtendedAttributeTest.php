<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Support\Collection;
use Tests\Fixtures\Data\LaravelCollectionExtendedData;

describe('extended Laravel collection attributes', function (): void {
    test('hydrates laravel collections through extended collection attributes', function (): void {
        $data = LaravelCollectionExtendedData::create([
            'where' => [
                ['id' => 1, 'type' => 'post'],
                ['id' => 2, 'type' => 'page'],
            ],
            'whereStrict' => [
                ['id' => '1', 'name' => 'string'],
                ['id' => 1, 'name' => 'int'],
            ],
            'whereIn' => [
                ['id' => 1, 'type' => 'post'],
                ['id' => 2, 'type' => 'note'],
                ['id' => 3, 'type' => 'page'],
            ],
            'whereInStrict' => [
                ['id' => '1'],
                ['id' => 1],
                ['id' => 3],
            ],
            'whereNotIn' => [
                ['id' => 1, 'type' => 'post'],
                ['id' => 2, 'type' => 'page'],
            ],
            'whereNotInStrict' => [
                ['id' => 1],
                ['id' => 2],
                ['id' => '2'],
            ],
            'whereNull' => [
                ['id' => 1, 'deleted_at' => null],
                ['id' => 2, 'deleted_at' => '2024-01-01'],
            ],
            'whereNotNull' => [
                ['id' => 1, 'deleted_at' => null],
                ['id' => 2, 'deleted_at' => '2024-01-01'],
            ],
            'whereBetween' => [
                ['id' => 1, 'score' => 9],
                ['id' => 2, 'score' => 15],
                ['id' => 3, 'score' => 20],
            ],
            'whereNotBetween' => [
                ['id' => 1, 'score' => 9],
                ['id' => 2, 'score' => 15],
                ['id' => 3, 'score' => 20],
            ],
            'plucked' => [
                ['id' => 10, 'name' => 'Alpha'],
                ['id' => 20, 'name' => 'Beta'],
            ],
            'flattened' => [['Alpha', ['Beta']], [['Gamma']]],
            'collapsed' => [['Alpha', 'Beta'], ['Gamma']],
            'collapsedWithKeys' => [
                ['a' => 'Alpha'],
                ['b' => 'Beta'],
            ],
            'chunkedWhile' => ['Alpha', 'Apex', 'Beta', 'Bravo', 'Charlie'],
            'mappedToGroups' => ['Alpha', 'Apex', 'Beta'],
            'sortKeysDescending' => ['b' => 'Beta', 'a' => 'Alpha', 'c' => 'Charlie'],
            'sortKeysUsing' => ['b' => 'Beta', 'A' => 'Alpha', 'c' => 'Charlie'],
            'uniqueStrict' => [1, '1', 1, '1'],
            'duplicates' => [1, '1', 1, '1'],
            'duplicatesStrict' => [1, '1', 1, '1'],
            'zipped' => ['Alpha', 'Beta', 'Gamma'],
            'whenMapped' => ['alpha', 'beta'],
            'unlessMapped' => ['alpha'],
            'whenEmpty' => [],
            'whenNotEmpty' => ['alpha'],
            'unlessEmpty' => [],
            'unlessNotEmpty' => ['alpha'],
        ]);

        expect($data->where)->toBeInstanceOf(Collection::class)
            ->and($data->where->values()->all())->toBe([
                ['id' => 1, 'type' => 'post'],
            ])->and($data->whereStrict->values()->all())->toBe([
                ['id' => 1, 'name' => 'int'],
            ])->and($data->whereIn->values()->all())->toBe([
                ['id' => 1, 'type' => 'post'],
                ['id' => 3, 'type' => 'page'],
            ])->and($data->whereInStrict->values()->all())->toBe([
                ['id' => 1],
                ['id' => 3],
            ])->and($data->whereNotIn->values()->all())->toBe([
                ['id' => 1, 'type' => 'post'],
            ])->and($data->whereNotInStrict->values()->all())->toBe([
                ['id' => 1],
                ['id' => '2'],
            ])->and($data->whereNull->values()->all())->toBe([
                ['id' => 1, 'deleted_at' => null],
            ])->and($data->whereNotNull->values()->all())->toBe([
                ['id' => 2, 'deleted_at' => '2024-01-01'],
            ])->and($data->whereBetween->pluck('id')->all())->toBe([2, 3])
            ->and($data->whereNotBetween->pluck('id')->all())->toBe([1])
            ->and($data->plucked->all())->toBe([
                10 => 'Alpha',
                20 => 'Beta',
            ])->and($data->flattened->values()->all())->toBe(['Alpha', 'Beta', 'Gamma'])
            ->and($data->collapsed->values()->all())->toBe(['Alpha', 'Beta', 'Gamma'])
            ->and($data->collapsedWithKeys->all())->toBe(['a' => 'Alpha', 'b' => 'Beta'])
            ->and($data->chunkedWhile->map(
                static fn (Collection $group): array => $group->values()->all(),
            )->values()->all())->toBe([
                ['Alpha', 'Apex'],
                ['Beta', 'Bravo'],
                ['Charlie'],
            ])->and($data->mappedToGroups->map(
                static fn (Collection $group): array => $group->values()->all(),
            )->all())->toBe([
                'a' => ['Alpha', 'Apex'],
                'b' => ['Beta'],
            ])->and($data->sortKeysDescending->keys()->all())->toBe(['c', 'b', 'a'])
            ->and($data->sortKeysUsing->keys()->all())->toBe(['A', 'b', 'c'])
            ->and($data->uniqueStrict->values()->all())->toBe([1, '1'])
            ->and($data->duplicates->values()->all())->toBe(['1', 1, '1'])
            ->and($data->duplicatesStrict->values()->all())->toBe([1, '1'])
            ->and($data->zipped->map(
                static fn (Collection $pair): array => $pair->all(),
            )->values()->all())->toBe([
                ['Alpha', 1],
                ['Beta', 2],
                ['Gamma', 3],
            ])->and($data->whenMapped->values()->all())->toBe(['0:ALPHA', '1:BETA'])
            ->and($data->unlessMapped->values()->all())->toBe(['0:ALPHA'])
            ->and($data->whenEmpty->values()->all())->toBe(['fallback'])
            ->and($data->whenNotEmpty->values()->all())->toBe(['alpha', 'fallback'])
            ->and($data->unlessEmpty->values()->all())->toBe([])
            ->and($data->unlessNotEmpty->values()->all())->toBe(['alpha']);
    });
});
