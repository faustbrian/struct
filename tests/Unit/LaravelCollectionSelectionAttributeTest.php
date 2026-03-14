<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Support\Collection;
use Tests\Fixtures\Data\LaravelCollectionSelectionData;

describe('selection Laravel collection attributes', function (): void {
    test('hydrates additional Laravel collection transforms', function (): void {
        $first = new stdClass();
        $first->name = 'Alpha';

        $second = new stdClass();
        $second->name = 'Beta';

        $data = LaravelCollectionSelectionData::create([
            'objectsOnly' => [$first, 'ignore', $second],
            'countedByLetter' => ['Alpha', 'Apex', 'Beta'],
            'excepted' => ['keep' => 'Alpha', 'drop' => 'Beta'],
            'flipped' => ['a' => 'Alpha', 'b' => 'Beta'],
            'paged' => ['Alpha', 'Beta', 'Gamma', 'Delta'],
            'keysOnly' => ['a' => 'Alpha', 'b' => 'Beta'],
            'selected' => [
                ['name' => 'Alpha', 'email' => 'alpha@example.com'],
                ['name' => 'Beta', 'email' => 'beta@example.com'],
            ],
            'dotted' => [
                'user' => ['name' => 'Alpha'],
                'meta' => ['active' => true],
            ],
            'undotted' => [
                'user.name' => 'Alpha',
                'meta.active' => true,
            ],
            'multiplied' => ['Alpha', 'Beta'],
            'nthValues' => [1, 2, 3, 4, 5],
            'onlyKeys' => ['keep' => 'Alpha', 'drop' => 'Beta'],
            'padded' => [1, 2],
            'prepended' => ['a' => 'Alpha'],
            'pushed' => ['Alpha', 'Beta'],
            'putValues' => ['alpha' => 'Alpha'],
            'shuffled' => ['Alpha', 'Beta', 'Gamma'],
            'skipped' => [1, 2, 3, 4],
            'sorted' => [3, 1, 2],
            'sortedDescending' => [1, 3, 2],
            'split' => [1, 2, 3, 4, 5],
            'splitIn' => [1, 2, 3, 4, 5],
            'transformed' => ['alpha', 'beta'],
        ]);

        expect($data->objectsOnly)->toBeInstanceOf(Collection::class)
            ->and($data->objectsOnly->values()->all())->toHaveCount(2)
            ->and($data->objectsOnly->values()->all()[0])->toBeInstanceOf(stdClass::class)
            ->and($data->countedByLetter->all())->toBe(['a' => 2, 'b' => 1])
            ->and($data->excepted->all())->toBe(['keep' => 'Alpha'])
            ->and($data->flipped->all())->toBe(['Alpha' => 'a', 'Beta' => 'b'])
            ->and($data->paged->values()->all())->toBe(['Gamma', 'Delta'])
            ->and($data->keysOnly->values()->all())->toBe(['a', 'b'])
            ->and($data->selected->values()->all())->toBe([
                ['name' => 'Alpha'],
                ['name' => 'Beta'],
            ])->and($data->dotted->all())->toBe([
                'user.name' => 'Alpha',
                'meta.active' => true,
            ])->and($data->undotted->all())->toBe([
                'user' => ['name' => 'Alpha'],
                'meta' => ['active' => true],
            ])->and($data->multiplied->values()->all())->toBe([
                'Alpha',
                'Beta',
                'Alpha',
                'Beta',
            ])->and($data->nthValues->values()->all())->toBe([2, 4])
            ->and($data->onlyKeys->all())->toBe(['keep' => 'Alpha'])
            ->and($data->padded->values()->all())->toBe([1, 2, 0, 0])
            ->and($data->prepended->all())->toBe(['z' => 'Zero', 'a' => 'Alpha'])
            ->and($data->pushed->values()->all())->toBe(['Alpha', 'Beta', 'Gamma'])
            ->and($data->putValues->all())->toBe(['alpha' => 'Alpha', 'gamma' => 'Gamma'])
            ->and($data->shuffled->sort()->values()->all())->toBe(['Alpha', 'Beta', 'Gamma'])
            ->and($data->skipped->values()->all())->toBe([3, 4])
            ->and($data->sorted->values()->all())->toBe([1, 2, 3])
            ->and($data->sortedDescending->values()->all())->toBe([3, 2, 1])
            ->and($data->split->map(
                static fn (Collection $chunk): array => $chunk->values()->all(),
            )->values()->all())->toBe([
                [1, 2],
                [3, 4],
                [5],
            ])->and($data->splitIn->map(
                static fn (Collection $chunk): array => $chunk->values()->all(),
            )->values()->all())->toBe([
                [1, 2, 3],
                [4, 5],
            ])->and($data->transformed->values()->all())->toBe([
                '0:ALPHA',
                '1:BETA',
            ]);
    });
});
