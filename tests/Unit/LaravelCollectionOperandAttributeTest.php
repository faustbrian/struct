<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Support\Collection;
use Tests\Fixtures\Data\LaravelCollectionOperandData;

describe('operand Laravel collection attributes', function (): void {
    test('hydrates operand aware Laravel collection transforms', function (): void {
        $data = LaravelCollectionOperandData::create([
            'otherNames' => ['Beta'],
            'diffed' => ['Alpha', 'Beta'],
            'otherAssoc' => ['drop' => 'Beta'],
            'diffAssoced' => ['keep' => 'Alpha', 'drop' => 'Beta'],
            'otherAssocUsing' => ['alpha' => 'Alpha'],
            'diffAssocUsing' => ['Alpha' => 'Alpha', 'beta' => 'Beta'],
            'otherKeys' => ['drop' => 'X'],
            'diffKeys' => ['keep' => 'Alpha', 'drop' => 'Beta'],
            'crossJoined' => ['Alpha', 'Beta'],
            'intersected' => ['Alpha', 'Beta', 'Gamma'],
            'intersectUsing' => ['1', '2', '3'],
            'intersectAssoc' => ['keep' => 'Alpha', 'drop' => 'Beta'],
            'otherAssocIntersectUsing' => ['alpha' => 'Alpha'],
            'intersectAssocUsing' => ['Alpha' => 'Alpha', 'beta' => 'Beta'],
            'intersectByKeys' => ['keep' => 'Alpha', 'drop' => 'Beta'],
            'merged' => ['Alpha', 'Beta'],
            'mergedRecursive' => ['meta' => ['name' => 'Alpha']],
            'replaced' => ['keep' => 'Alpha', 'drop' => 'Beta'],
            'replacedRecursive' => ['meta' => ['name' => 'Alpha']],
            'unioned' => ['keep' => 'Alpha'],
            'randomized' => ['Alpha', 'Beta', 'Gamma'],
            'spliced' => [1, 2, 3, 4],
            'piped' => ['Alpha', 'Beta'],
            'pipedInto' => ['Alpha', 'Beta'],
            'pipedThrough' => ['Alpha', 'Beta'],
        ]);

        expect($data->diffed->values()->all())->toBe(['Alpha'])
            ->and($data->diffAssoced->all())->toBe(['keep' => 'Alpha'])
            ->and($data->diffAssocUsing->all())->toBe(['beta' => 'Beta'])
            ->and($data->diffKeys->all())->toBe(['keep' => 'Alpha'])
            ->and($data->crossJoined->values()->all())->toBe([
                ['Alpha', 1],
                ['Alpha', 2],
                ['Beta', 1],
                ['Beta', 2],
            ])->and($data->intersected->values()->all())->toBe(['Beta', 'Gamma'])
            ->and($data->intersectUsing->values()->all())->toBe(['2'])
            ->and($data->intersectAssoc->all())->toBe(['keep' => 'Alpha'])
            ->and($data->intersectAssocUsing->all())->toBe(['Alpha' => 'Alpha'])
            ->and($data->intersectByKeys->all())->toBe(['keep' => 'Alpha'])
            ->and($data->merged->values()->all())->toBe(['Alpha', 'Beta', 'Gamma'])
            ->and($data->mergedRecursive->all())->toBe([
                'meta' => ['name' => 'Alpha', 'active' => true],
            ])->and($data->replaced->all())->toBe([
                'keep' => 'Replaced',
                'drop' => 'Beta',
            ])->and($data->replacedRecursive->all())->toBe([
                'meta' => ['name' => 'Alpha', 'active' => true],
            ])->and($data->unioned->all())->toBe([
                'keep' => 'Alpha',
                'new' => 'Gamma',
            ])->and($data->randomized)->toBeInstanceOf(Collection::class)
            ->and($data->randomized->count())->toBe(2)
            ->and($data->randomized->every(
                static fn (string $value): bool => in_array($value, ['Alpha', 'Beta', 'Gamma'], true),
            ))->toBeTrue()
            ->and($data->spliced->values()->all())->toBe([2, 3])
            ->and($data->piped->values()->all())->toBe(['Alpha', 'Beta', 'Gamma'])
            ->and($data->pipedInto->values()->all())->toBe(['Alpha', 'Beta', 'Gamma'])
            ->and($data->pipedThrough->values()->all())->toBe(['Gamma', 'Beta', 'Alpha']);
    });
});
