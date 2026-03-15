<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Exceptions\InvalidCollectionAttributeException;
use Illuminate\Support\LazyCollection;
use Tests\Fixtures\Data\InvalidLazyLaravelCollectionAttributeData;
use Tests\Fixtures\Data\LazyLaravelCollectionData;

describe('lazy laravel collection support', function (): void {
    test('hydrates typed lazy laravel collections without eager consumption', function (): void {
        $numberItems = 0;
        $nameItems = 0;

        $dto = LazyLaravelCollectionData::create([
            'numbers' => (function () use (&$numberItems): Generator {
                ++$numberItems;
                yield '1';
                ++$numberItems;
                yield '2';
                ++$numberItems;
                yield '3';
            })(),
            'mappedNames' => (function () use (&$nameItems): Generator {
                ++$nameItems;
                yield 'alpha';
                ++$nameItems;
                yield 'beta';
                ++$nameItems;
                yield 'gamma';
            })(),
            'derivedNumbers' => ['1', '2', '3'],
            'evenNumbers' => ['1', '2', '3'],
        ]);

        expect($dto->numbers)->toBeInstanceOf(LazyCollection::class)
            ->and($dto->mappedNames)->toBeInstanceOf(LazyCollection::class)
            ->and($dto->evenNumbers)->toBeInstanceOf(LazyCollection::class)
            ->and($numberItems)->toBe(0)
            ->and($nameItems)->toBe(0)
            ->and($dto->numbers->first())->toBe(1)
            ->and($numberItems)->toBe(1)
            ->and($dto->mappedNames->first())->toBe('1:BETA')
            ->and($nameItems)->toBe(2)
            ->and($dto->numbers->all())->toBe([1, 2, 3])
            ->and($numberItems)->toBe(3)
            ->and($dto->mappedNames->values()->all())->toBe(['1:BETA'])
            ->and($nameItems)->toBe(2)
            ->and($dto->evenNumbers->values()->all())->toBe([2, 4, 6]);
    });

    test('supports lazy collection sources and derived results', function (): void {
        $dto = LazyLaravelCollectionData::create([
            'numbers' => ['1', '2', '3'],
            'derivedNumbers' => ['1', '2', '3'],
            'mappedNames' => ['alpha', 'beta', 'gamma'],
            'evenNumbers' => ['1', '2', '3'],
        ]);

        expect($dto->wrapped)->toBeInstanceOf(LazyCollection::class)
            ->and($dto->wrapped->all())->toBe(['Only'])
            ->and($dto->ranged->values()->all())->toBe([2, 3, 4])
            ->and($dto->times->values()->all())->toBe([1, 2, 3])
            ->and($dto->decoded->values()->all())->toBe(['Alpha', 'Beta'])
            ->and($dto->firstNumber)->toBe(1)
            ->and($dto->numberCount)->toBe(3)
            ->and($dto->numberSum)->toBe(6)
            ->and($dto->decodedContainsBeta)->toBeTrue();
    });

    test('serializes lazy laravel collections with hydrated items', function (): void {
        $dto = LazyLaravelCollectionData::create([
            'numbers' => ['1', '2', '3'],
            'derivedNumbers' => ['1', '2', '3'],
            'mappedNames' => ['alpha', 'beta', 'gamma'],
            'evenNumbers' => ['1', '2', '3'],
        ]);

        expect($dto->toArray())->toBe([
            'numbers' => ['1', '2', '3'],
            'derivedNumbers' => ['1', '2', '3'],
            'mappedNames' => [1 => '1:BETA'],
            'evenNumbers' => ['2', '4', '6'],
            'wrapped' => ['Only'],
            'ranged' => [2, 3, 4],
            'times' => [1, 2, 3],
            'decoded' => ['Alpha', 'Beta'],
            'firstNumber' => 1,
            'numberCount' => 3,
            'numberSum' => 6,
            'decodedContainsBeta' => true,
        ]);
    });

    test('rejects eager-only collection transforms on lazy laravel collections', function (): void {
        expect(fn (): InvalidLazyLaravelCollectionAttributeData => InvalidLazyLaravelCollectionAttributeData::create([
            'numbers' => [1, 2, 3],
        ]))->toThrow(InvalidCollectionAttributeException::class);
    });
});
