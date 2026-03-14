<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Exceptions\AbstractStructInvalidArgumentException;
use Illuminate\Support\Collection;
use Tests\Fixtures\Data\InvalidLaravelCollectionCallbackData;
use Tests\Fixtures\Data\LaravelCollectionCallbackData;
use Tests\Fixtures\Data\LaravelCollectionCallbackReuseData;
use Tests\Fixtures\Data\ValueData;
use Tests\Fixtures\Support\CollectionCallbacks\BoundPrefixMapper;
use Tests\Fixtures\Support\CollectionCallbacks\CountingPassthroughMapper;
use Tests\Fixtures\Support\CollectionCallbacks\RecordValueAction;

describe('Laravel collection callback attributes', function (): void {
    beforeEach(function (): void {
        CountingPassthroughMapper::$instances = 0;
        RecordValueAction::$calls = [];
    });

    test('hydrates laravel collections through callback-based attributes', function (): void {
        $data = LaravelCollectionCallbackData::create([
            'filtered' => ['first' => '1', 'second' => 2, 'third' => '3', 'fourth' => 4],
            'rejected' => [1, '2', 3, '4'],
            'mapped' => [1, '2', 3],
            'flatMapped' => ['first' => 'Hello World', 'second' => 'Struct'],
            'sorted' => ['tiny', 'alphabet', 'mid'],
            'grouped' => ['first' => 'Alpha', 'second' => 'Apex', 'third' => 'Beta'],
            'keyed' => ['Alpha', 'Beta'],
            'partitioned' => [1, '2', 3, '4'],
            'ordered' => ['first' => 'a', 'second' => 'b'],
            'recorded' => ['alpha', 'beta'],
            'sortedDescending' => ['tiny', 'alphabet', 'mid'],
            'uniqueBy' => ['Alpha', 'Apex', 'Beta', 'Bravo'],
            'skipUntil' => [1, 3, 4, 6],
            'skipWhile' => [2, 4, 3, 6],
            'takeUntil' => [1, 3, 4, 6],
            'takeWhile' => [2, 4, 3, 6],
            'mappedWithKeys' => ['Alpha', 'Beta'],
            'chunked' => ['Alpha', 'Beta', 'Gamma'],
            'sliding' => ['Alpha', 'Beta', 'Gamma'],
            'mappedInto' => ['alpha', 'beta'],
        ]);

        expect($data->filtered)->toBeInstanceOf(Collection::class)
            ->and($data->filtered->all())->toBe([
                'second' => 2,
                'fourth' => 4,
            ])->and($data->rejected->all())->toBe([
                0 => 1,
                2 => 3,
            ])->and($data->mapped->all())->toBe([2, 4, 6])
            ->and($data->flatMapped->all())->toBe(['hello', 'world', 'struct'])
            ->and($data->sorted->values()->all())->toBe(['alphabet', 'tiny', 'mid'])
            ->and($data->grouped->map(
                static fn (Collection $group): array => $group->all(),
            )->all())->toBe([
                'a' => [
                    'first' => 'Alpha',
                    'second' => 'Apex',
                ],
                'b' => [
                    'third' => 'Beta',
                ],
            ])->and($data->keyed->all())->toBe([
                'a' => 'Alpha',
                'b' => 'Beta',
            ])->and($data->partitioned->map(
                static fn (Collection $group): array => $group->all(),
            )->all())->toBe([
                'even' => [
                    1 => 2,
                    3 => 4,
                ],
                'odd' => [
                    0 => 1,
                    2 => 3,
                ],
            ])->and($data->ordered->all())->toBe([
                0 => '0:A',
                1 => '1:B',
            ])->and($data->recorded->all())->toBe([
                0 => 'alpha',
                1 => 'beta',
            ])->and($data->sortedDescending->values()->all())->toBe(['alphabet', 'tiny', 'mid'])
            ->and($data->uniqueBy->values()->all())->toBe(['Alpha', 'Beta'])
            ->and($data->skipUntil->values()->all())->toBe([4, 6])
            ->and($data->skipWhile->values()->all())->toBe([3, 6])
            ->and($data->takeUntil->values()->all())->toBe([1, 3])
            ->and($data->takeWhile->values()->all())->toBe([2, 4])
            ->and($data->mappedWithKeys->all())->toBe([
                'a' => 'Alpha',
                'b' => 'Beta',
            ])->and($data->chunked->map(
                static fn (Collection $group): array => $group->all(),
            )->all())->toBe([
                0 => ['Alpha', 'Beta'],
                1 => [2 => 'Gamma'],
            ])->and($data->sliding->map(
                static fn (Collection $group): array => $group->all(),
            )->values()->all())->toBe([
                ['Alpha', 'Beta'],
                [1 => 'Beta', 2 => 'Gamma'],
            ])->and($data->mappedInto->all())->each->toBeInstanceOf(ValueData::class)
            ->and($data->mappedInto->map(
                static fn (ValueData $value): string|int|null => $value->value,
            )->all())->toBe([
                0 => 'alpha',
                1 => 'beta',
            ])->and(RecordValueAction::$calls)->toBe([
                '0:alpha',
                '1:beta',
            ])->and($data->toArray())->toBe([
                'filtered' => [
                    'second' => 2,
                    'fourth' => 4,
                ],
                'rejected' => [
                    0 => 1,
                    2 => 3,
                ],
                'mapped' => [2, 4, 6],
                'flatMapped' => ['hello', 'world', 'struct'],
                'sorted' => [
                    1 => 'alphabet',
                    0 => 'tiny',
                    2 => 'mid',
                ],
                'grouped' => [
                    'a' => [
                        'first' => 'Alpha',
                        'second' => 'Apex',
                    ],
                    'b' => [
                        'third' => 'Beta',
                    ],
                ],
                'keyed' => [
                    'a' => 'Alpha',
                    'b' => 'Beta',
                ],
                'partitioned' => [
                    'even' => [
                        1 => 2,
                        3 => 4,
                    ],
                    'odd' => [
                        0 => 1,
                        2 => 3,
                    ],
                ],
                'ordered' => ['0:A', '1:B'],
                'recorded' => ['alpha', 'beta'],
                'sortedDescending' => [
                    1 => 'alphabet',
                    0 => 'tiny',
                    2 => 'mid',
                ],
                'uniqueBy' => [
                    0 => 'Alpha',
                    2 => 'Beta',
                ],
                'skipUntil' => [2 => 4, 3 => 6],
                'skipWhile' => [2 => 3, 3 => 6],
                'takeUntil' => [0 => 1, 1 => 3],
                'takeWhile' => [0 => 2, 1 => 4],
                'mappedWithKeys' => [
                    'a' => 'Alpha',
                    'b' => 'Beta',
                ],
                'chunked' => [
                    ['Alpha', 'Beta'],
                    [2 => 'Gamma'],
                ],
                'sliding' => [
                    ['Alpha', 'Beta'],
                    [1 => 'Beta', 2 => 'Gamma'],
                ],
                'mappedInto' => [
                    ['value' => 'alpha'],
                    ['value' => 'beta'],
                ],
            ]);
    });

    test('reuses direct and container-resolved callback instances within one hydration operation', function (): void {
        $resolves = 0;

        app()->bind(BoundPrefixMapper::class, function () use (&$resolves): BoundPrefixMapper {
            ++$resolves;

            return new BoundPrefixMapper('bound:');
        });

        $data = LaravelCollectionCallbackReuseData::create([
            'directFirst' => ['one', 'two'],
            'directSecond' => ['three'],
            'boundFirst' => ['one', 'two'],
            'boundSecond' => ['three'],
        ]);

        expect(CountingPassthroughMapper::$instances)->toBe(1)
            ->and($resolves)->toBe(1)
            ->and($data->boundFirst->all())->toBe(['bound:one', 'bound:two'])
            ->and($data->boundSecond->all())->toBe(['bound:three']);
    });

    test('rejects callback-based collection attributes on non-laravel collection properties', function (): void {
        expect(fn (): InvalidLaravelCollectionCallbackData => InvalidLaravelCollectionCallbackData::create([
            'arrayValues' => [1, 2, 3],
            'invalidMapper' => ['a'],
        ]))->toThrow(AbstractStructInvalidArgumentException::class);
    });

    test('rejects callback classes that do not implement the expected contract', function (): void {
        expect(fn (): InvalidLaravelCollectionCallbackData => InvalidLaravelCollectionCallbackData::create([
            'arrayValues' => [],
            'invalidMapper' => ['a'],
        ]))->toThrow(AbstractStructInvalidArgumentException::class);
    });
});
