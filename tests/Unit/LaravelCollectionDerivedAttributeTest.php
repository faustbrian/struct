<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Support\Collection;
use Tests\Fixtures\Data\LaravelCollectionDerivedData;

describe('derived Laravel collection attributes', function (): void {
    test('hydrates generated collections and derived collection results', function (): void {
        $data = LaravelCollectionDerivedData::create([
            'names' => ['Alpha', 'Beta'],
            'records' => [
                ['id' => 1, 'type' => 'post'],
                ['id' => 2, 'type' => 'page'],
            ],
            'modalNumbers' => [2, 2, 3],
            'keyedValues' => ['only' => 'Only'],
            'combined' => ['first', 'second'],
            'forgotten' => ['keep' => 'Alpha', 'drop' => 'Beta'],
            'whenEmptyCollection' => [],
            'scalarValue' => 'Only',
        ]);

        expect($data->combined)->toBeInstanceOf(Collection::class)
            ->and($data->combined->all())->toBe([
                'first' => 'Alpha',
                'second' => 'Beta',
            ])->and($data->forgotten->all())->toBe([
                'keep' => 'Alpha',
            ])->and($data->wrapped->all())->toBe(['Only'])
            ->and($data->ranged->values()->all())->toBe([2, 3, 4])
            ->and($data->times->values()->all())->toBe([1, 2, 3])
            ->and($data->containsAlpha)->toBeTrue()
            ->and($data->containsType)->toBeTrue()
            ->and($data->containsStrictAlpha)->toBeTrue()
            ->and($data->doesntContainGamma)->toBeTrue()
            ->and($data->doesntContainStrictGamma)->toBeTrue()
            ->and($data->afterAlpha)->toBe('Beta')
            ->and($data->beforeBeta)->toBe('Alpha')
            ->and($data->everyEven)->toBeFalse()
            ->and($data->someEven)->toBeTrue()
            ->and($data->firstName)->toBe('Alpha')
            ->and($data->firstNameOrFail)->toBe('Alpha')
            ->and($data->lastName)->toBe('Beta')
            ->and($data->soleWrappedValue)->toBe('Only')
            ->and($data->firstPageRecord)->toBe(['id' => 2, 'type' => 'page'])
            ->and($data->gotOnlyValue)->toBe('Only')
            ->and($data->hasOnlyKey)->toBeTrue()
            ->and($data->hasAnyKnownKey)->toBeTrue()
            ->and($data->hasManyRecords)->toBeTrue()
            ->and($data->hasSoleWrappedValue)->toBeTrue()
            ->and($data->emptyCollectionIsEmpty)->toBeTrue()
            ->and($data->namesAreNotEmpty)->toBeTrue()
            ->and($data->searchBeta)->toBe(1)
            ->and($data->firstRecordType)->toBe('post')
            ->and($data->nameCount)->toBe(2)
            ->and($data->allNames)->toBe(['Alpha', 'Beta'])
            ->and($data->arrayNames)->toBe(['Alpha', 'Beta'])
            ->and($data->jsonNames)->toBe('["Alpha","Beta"]')
            ->and($data->prettyJsonNames)->toContain("\n")
            ->and($data->randomName)->toBeString()
            ->and($data->randomName)->toBeIn(['Alpha', 'Beta'])
            ->and($data->sumRange)->toBe(9)
            ->and($data->minRange)->toBe(2)
            ->and($data->maxRange)->toBe(4)
            ->and($data->avgRange)->toBe(3.0)
            ->and($data->averageRange)->toBe(3.0)
            ->and($data->medianRange)->toBe(3.0)
            ->and($data->modeValues)->toBe([2])
            ->and($data->evenPercentage)->toBe(66.67)
            ->and($data->reducedSum)->toBe(9)
            ->and($data->reducedSpread)->toBe([6, 3])
            ->and($data->implodedNames)->toBe('Alpha, Beta')
            ->and($data->joinedNames)->toBe('Alpha and Beta')
            ->and($data->poppedName)->toBe('Beta')
            ->and($data->shiftedName)->toBe('Alpha')
            ->and($data->pulledValue)->toBe('Only')
            ->and($data->unwrappedNames)->toBe(['Alpha', 'Beta'])
            ->and($data->toArray())->toBe([
                'names' => ['Alpha', 'Beta'],
                'records' => [
                    ['id' => 1, 'type' => 'post'],
                    ['id' => 2, 'type' => 'page'],
                ],
                'modalNumbers' => [2, 2, 3],
                'keyedValues' => ['only' => 'Only'],
                'combined' => [
                    'first' => 'Alpha',
                    'second' => 'Beta',
                ],
                'forgotten' => [
                    'keep' => 'Alpha',
                ],
                'wrapped' => ['Only'],
                'ranged' => [2, 3, 4],
                'times' => [1, 2, 3],
                'containsAlpha' => true,
                'containsType' => true,
                'containsStrictAlpha' => true,
                'doesntContainGamma' => true,
                'doesntContainStrictGamma' => true,
                'afterAlpha' => 'Beta',
                'beforeBeta' => 'Alpha',
                'everyEven' => false,
                'someEven' => true,
                'firstName' => 'Alpha',
                'firstNameOrFail' => 'Alpha',
                'lastName' => 'Beta',
                'soleWrappedValue' => 'Only',
                'firstPageRecord' => ['id' => 2, 'type' => 'page'],
                'gotOnlyValue' => 'Only',
                'hasOnlyKey' => true,
                'hasAnyKnownKey' => true,
                'hasManyRecords' => true,
                'hasSoleWrappedValue' => true,
                'emptyCollectionIsEmpty' => true,
                'namesAreNotEmpty' => true,
                'searchBeta' => 1,
                'firstRecordType' => 'post',
                'nameCount' => 2,
                'allNames' => ['Alpha', 'Beta'],
                'arrayNames' => ['Alpha', 'Beta'],
                'jsonNames' => '["Alpha","Beta"]',
                'prettyJsonNames' => json_encode(['Alpha', 'Beta'], \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR),
                'randomName' => $data->randomName,
                'unwrappedNames' => ['Alpha', 'Beta'],
                'sumRange' => 9,
                'minRange' => 2,
                'maxRange' => 4,
                'avgRange' => 3.0,
                'averageRange' => 3.0,
                'medianRange' => 3.0,
                'modeValues' => [2],
                'evenPercentage' => 66.67,
                'reducedSum' => 9,
                'reducedSpread' => [6, 3],
                'implodedNames' => 'Alpha, Beta',
                'joinedNames' => 'Alpha and Beta',
                'poppedName' => 'Beta',
                'shiftedName' => 'Alpha',
                'pulledValue' => 'Only',
                'whenEmptyCollection' => [],
                'scalarValue' => 'Only',
            ]);
    });
});
