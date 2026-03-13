<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Exceptions\AbstractStructInvalidArgumentException;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Symfony\Component\Uid\Ulid as SymfonyUlid;
use Tests\Fixtures\Data\GeneratedValueData;
use Tests\Fixtures\Data\InvalidOptionalGeneratedValueData;
use Tests\Fixtures\Data\InvalidTypedGeneratedValueData;

describe('Generated value attributes', function (): void {
    test('generates missing values before validation and hydration', function (): void {
        Str::createRandomStringsUsingSequence(['AbCdEfGhIjKl']);
        Str::createUlidsUsingSequence([new SymfonyUlid('01ARZ3NDEKTSV4RRFFQ69G5FAV')]);

        try {
            $data = GeneratedValueData::createWithValidation([]);
        } finally {
            Str::createRandomStringsNormally();
            Str::createUlidsNormally();
        }

        expect(uuidVersion($data->uuidV1))->toBe(1)
            ->and(uuidVersion($data->uuidV2))->toBe(2)
            ->and($data->uuidV2)->toBe(strtolower($data->uuidV2))
            ->and($data->uuidV3)->toBe('9073926b-929f-31c2-abc9-fad77ae3e8eb')
            ->and(uuidVersion($data->uuidV4))->toBe(4)
            ->and($data->uuidV5)->toBe('79a0a9bc-db8c-5356-89ea-366a11ac0a5b')
            ->and(uuidVersion($data->uuidV6))->toBe(6)
            ->and(uuidVersion($data->uuidV7))->toBe(7)
            ->and($data->ulid)->toBe('01arz3ndektsv4rrffq69g5fav')
            ->and($data->random)->toBe('abcdefghijkl')
            ->and($data->password)->toHaveLength(12)
            ->and($data->password)->toBe(strtolower($data->password))
            ->and(preg_match('/^[a-z]+$/', $data->password))->toBe(1)
            ->and($data->defaulted)->not->toBe('php-default')
            ->and(uuidVersion($data->defaulted))->toBe(4)
            ->and($data->note)->toBeNull();
    });

    test('keeps explicit input and explicit null values instead of generating', function (): void {
        Str::createRandomStringsUsingSequence(['AbCdEfGhIjKl']);
        Str::createUlidsUsingSequence([new SymfonyUlid('01ARZ3NDEKTSV4RRFFQ69G5FAV')]);

        try {
            $data = GeneratedValueData::create([
                'uuidV4' => '11111111-1111-4444-8111-111111111111',
                'ulid' => '01ARZ3NDEKTSV4RRFFQ69G5FAV',
                'random' => 'provided-rnd',
                'password' => 'provided-pass',
                'note' => null,
            ]);
        } finally {
            Str::createRandomStringsNormally();
            Str::createUlidsNormally();
        }

        expect($data->uuidV4)->toBe('11111111-1111-4444-8111-111111111111')
            ->and($data->ulid)->toBe('01ARZ3NDEKTSV4RRFFQ69G5FAV')
            ->and($data->random)->toBe('provided-rnd')
            ->and($data->password)->toBe('provided-pass')
            ->and($data->note)->toBeNull();
    });

    test('does not regenerate values when cloning with with()', function (): void {
        Str::createRandomStringsUsingSequence(['AbCdEfGhIjKl']);
        Str::createUlidsUsingSequence([new SymfonyUlid('01ARZ3NDEKTSV4RRFFQ69G5FAV')]);

        try {
            $data = GeneratedValueData::create([]);
        } finally {
            Str::createRandomStringsNormally();
            Str::createUlidsNormally();
        }

        $clone = $data->with(note: 'updated');

        expect($clone->uuidV4)->toBe($data->uuidV4)
            ->and($clone->ulid)->toBe($data->ulid)
            ->and($clone->random)->toBe($data->random)
            ->and($clone->password)->toBe($data->password)
            ->and($clone->note)->toBe('updated');
    });

    test('rejects generator attributes on optional properties', function (): void {
        expect(fn (): InvalidOptionalGeneratedValueData => InvalidOptionalGeneratedValueData::create([]))
            ->toThrow(AbstractStructInvalidArgumentException::class);
    });

    test('rejects generator attributes on non-string properties', function (): void {
        expect(fn (): InvalidTypedGeneratedValueData => InvalidTypedGeneratedValueData::create([]))
            ->toThrow(AbstractStructInvalidArgumentException::class);
    });
});

function uuidVersion(string $value): int
{
    return RamseyUuid::fromString($value)->getFields()->getVersion();
}
