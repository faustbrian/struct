<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Exceptions\RecursiveSerializationException;
use Illuminate\Contracts\Config\Repository;
use Tests\Fixtures\Data\CastedListData;
use Tests\Fixtures\Data\ComputedUserData;
use Tests\Fixtures\Data\MappedUserData;
use Tests\Fixtures\Data\RecursiveObjectData;
use Tests\Fixtures\Data\XmlStringifiedData;

describe('Serialization', function (): void {
    beforeEach(function (): void {
        $this->configRepository = resolve(Repository::class);
        $this->previousDateFormat = $this->configRepository->get('struct.date_format', \DATE_ATOM);
        $this->previousDateTimezone = $this->configRepository->get('struct.date_timezone');
    });

    afterEach(function (): void {
        $this->configRepository->set('struct.date_format', $this->previousDateFormat);
        $this->configRepository->set('struct.date_timezone', $this->previousDateTimezone);
    });

    describe('mapping and sensitive field handling', function (): void {
        beforeEach(function (): void {
            // Arrange
            $this->dto = ComputedUserData::create([
                'first_name' => 'Brian',
                'last_name' => 'Faust',
                'password' => 'secret',
            ]);
        });

        test('serializes mapped output while omitting sensitive values', function (): void {
            // Arrange
            $dto = $this->dto;

            // Act
            $serialized = $dto->toArray();
            $asString = (string) $dto;

            // Assert
            expect($serialized)->toBe([
                'first_name' => 'Brian',
                'last_name' => 'Faust',
                'display_name' => 'Brian Faust',
            ])->and($asString)->toContain('display_name');
        });
    });

    describe('recursive structures', function (): void {
        beforeEach(function (): void {
            // Arrange
            $payload = new stdClass();
            $payload->name = 'loop';

            $this->dto = RecursiveObjectData::create([
                'payload' => $payload,
            ]);
        });

        test('throws when a payload contains a self-referencing loop', function (): void {
            // Arrange
            $dto = $this->dto;

            // Act
            $dto->payload->self = $dto->payload;
            $dto->toArray();
        })->throws(RecursiveSerializationException::class);
    });

    describe('stringification behavior', function (): void {
        beforeEach(function (): void {
            // Arrange
            $this->dto = XmlStringifiedData::create([
                'title' => 'Struct',
            ]);
        });

        test('casts DTO data into custom XML output when stringified', function (): void {
            // Act
            $asString = (string) $this->dto;

            // Assert
            expect($asString)->toContain('<dto>')
                ->and($asString)->toContain('<title>Struct</title>');
        });
    });

    describe('collection casting', function (): void {
        beforeEach(function (): void {
            // Arrange
            $this->dto = CastedListData::create([
                'numbers' => ['1', '2', 3],
            ]);
        });

        test('casts list values through the configured data cast contract', function (): void {
            // Act
            $serialized = $this->dto->toArray();

            // Assert
            expect($serialized)->toBe([
                'numbers' => ['1', '2', '3'],
            ]);
        });

        test('serializes dates using the first configured date format', function (): void {
            // Arrange
            $this->configRepository->set('struct.date_format', ['Y-m-d H:i:s', \DATE_ATOM]);
            $dto = MappedUserData::create([
                'id' => 1,
                'full_name' => 'Brian Faust',
                'created_at' => '2026-03-07T10:00:00+00:00',
                'status' => 'active',
            ]);

            // Act
            $serialized = $dto->toArray();

            // Assert
            expect($serialized['created_at'])->toBe('2026-03-07 10:00:00');
        });

        test('serializes dates using the configured timezone', function (): void {
            // Arrange
            $this->configRepository->set('struct.date_format', 'Y-m-d H:i:s');
            $this->configRepository->set('struct.date_timezone', 'America/New_York');

            $dto = MappedUserData::create([
                'id' => 1,
                'full_name' => 'Brian Faust',
                'created_at' => '2026-03-07T10:00:00+00:00',
                'status' => 'active',
            ]);

            // Act
            $serialized = $dto->toArray();

            // Assert
            expect($serialized['created_at'])->toBe('2026-03-07 05:00:00');
        });
    });
});
