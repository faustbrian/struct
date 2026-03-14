<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Carbon\CarbonImmutable;
use Cline\Struct\Exceptions\SuperfluousInputKeyException;
use Cline\Struct\Support\DataList;
use Cline\Struct\Support\Optional;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Collection;
use Tests\Fixtures\Data\CastedListData;
use Tests\Fixtures\Data\LaravelCollectionAttributeData;
use Tests\Fixtures\Data\MappedUserData;
use Tests\Fixtures\Data\SongData;
use Tests\Fixtures\Data\StrictUserData;
use Tests\Fixtures\Enums\UserStatus;

describe('Dto hydration', function (): void {
    beforeEach(function (): void {
        $this->configRepository = resolve(Repository::class);
        $this->previousDateFormat = $this->configRepository->get('struct.date_format', \DATE_ATOM);
        $this->previousDateTimezone = $this->configRepository->get('struct.date_timezone');
        $this->basePayload = [
            'id' => 1,
            'full_name' => 'Brian Faust',
            'created_at' => '2026-03-07T10:00:00+00:00',
            'status' => 'active',
        ];
    });

    afterEach(function (): void {
        $this->configRepository->set('struct.date_format', $this->previousDateFormat);
        $this->configRepository->set('struct.date_timezone', $this->previousDateTimezone);
    });

    describe('Happy Paths', function (): void {
        test('hydrates dto values from arrays with scalar casting and mappings', function (): void {
            // Arrange
            $payload = $this->basePayload;
            $payload['id'] = '1';
            $payload['tags'] = [1, '2', 3.4];
            $payload['email'] = '';

            // Act
            $dto = MappedUserData::create($payload);

            // Assert
            expect($dto->id)->toBe(1)
                ->and($dto->fullName)->toBe('Brian Faust')
                ->and($dto->createdAt)->toBeInstanceOf(CarbonImmutable::class)
                ->and($dto->status)->toBe(UserStatus::Active)
                ->and($dto->tags)->toBeInstanceOf(DataList::class)
                ->and($dto->tags->toArray())->toBe([1, 2, 3])
                ->and($dto->email)->toBeNull()
                ->and($dto->age)->toBeInstanceOf(Optional::class);
        });

        test('supports wither-style immutable cloning for dto field updates', function (): void {
            // Arrange
            $payload = $this->basePayload;
            $payload['full_name'] = 'Original';

            // Act
            $dto = MappedUserData::create($payload);
            $clone = $dto->with(fullName: 'Changed');

            // Assert
            expect($dto->fullName)->toBe('Original')
                ->and($clone->fullName)->toBe('Changed')
                ->and($clone)->not->toBe($dto);
        });

        test('casts data list items through the existing cast contract', function (): void {
            // Arrange
            $payload = [
                'numbers' => ['1', 2, '3'],
            ];

            // Act
            $dto = CastedListData::create($payload);

            // Assert
            expect($dto->numbers)->toBeInstanceOf(DataList::class)
                ->and($dto->numbers->toArray())->toBe([1, 2, 3]);
        });

        test('hydrates nested collection wrappers without serializing them first', function (): void {
            // Arrange
            $payload = [
                'numbers' => new DataList(['1', 2, '3']),
            ];

            // Act
            $dto = CastedListData::create($payload);

            // Assert
            expect($dto->numbers)->toBeInstanceOf(DataList::class)
                ->and($dto->numbers->toArray())->toBe([1, 2, 3]);
        });

        test('hydrates laravel collections from existing collection inputs', function (): void {
            // Arrange
            $payload = [
                'reversed' => collect(['first' => 'A', 'second' => 'B', 'third' => 'C']),
                'numbers' => collect(['first' => '1', 'second' => 2, 'third' => '3']),
                'onlyKeys' => collect(['drop' => 'x', 'keep' => 'a', 'also' => 'b']),
                'cleaned' => collect(['first' => 'A', 'second' => null, 'third' => 'B']),
                'casted' => collect(['1', 2, '3']),
                'songs' => collect([
                    ['title' => 'A', 'artist' => 'Artist A'],
                    ['title' => 'B', 'artist' => 'Artist B'],
                ]),
            ];

            // Act
            $dto = LaravelCollectionAttributeData::create($payload);

            // Assert
            expect($dto->reversed)->toBeInstanceOf(Collection::class)
                ->and($dto->reversed->all())->toBe([
                    'third' => 'C',
                    'second' => 'B',
                    'first' => 'A',
                ])
                ->and($dto->songs->first())->toBeInstanceOf(SongData::class);
        });

        test('hydrates dates using configured date formats', function (): void {
            // Arrange
            $this->configRepository->set('struct.date_format', ['Y-m-d H:i:s', 'Y*m*d H_i_s']);
            $payload = $this->basePayload;
            $payload['created_at'] = '2026*03*07 10_00_00';

            // Act
            $dto = MappedUserData::create($payload);

            // Assert
            expect($dto->createdAt)->toBeInstanceOf(CarbonImmutable::class)
                ->and($dto->createdAt->format('Y-m-d H:i:s'))->toBe('2026-03-07 10:00:00');
        });

        test('hydrates dates using the configured timezone', function (): void {
            // Arrange
            $this->configRepository->set('struct.date_format', 'Y-m-d H:i:s');
            $this->configRepository->set('struct.date_timezone', 'Europe/Helsinki');

            $payload = $this->basePayload;
            $payload['created_at'] = '2026-03-07 10:00:00';

            // Act
            $dto = MappedUserData::create($payload);

            // Assert
            expect($dto->createdAt->getTimezone()->getName())->toBe('Europe/Helsinki')
                ->and($dto->createdAt->format('Y-m-d H:i:s'))->toBe('2026-03-07 10:00:00');
        });
    });

    describe('Sad Paths', function (): void {
        test('throws superfluous key exception when unknown keys are supplied', function (): void {
            // Arrange
            $payload = [
                'id' => 1,
                'name' => 'Brian',
                'status' => 'active',
                'extra' => 'nope',
            ];

            // Act
            $action = fn (): StrictUserData => StrictUserData::create($payload);

            // Assert
            expect($action)->toThrow(SuperfluousInputKeyException::class);
        });
    });

    describe('Edge Cases', function (): void {
        // Reserved for boundary payloads.
    });

    describe('Regressions', function (): void {
        // Reserved for bug-linked cases.
    });
});
