<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Exceptions\InvalidLivewireDataClassException;
use Cline\Struct\Exceptions\InvalidLivewireDataPayloadException;
use Cline\Struct\Livewire\DataSynth;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Livewire\Mechanisms\HandleComponents\HandleComponents;
use Livewire\Wireable;
use Tests\Fixtures\Data\SongData;
use Tests\Fixtures\Data\WireableSongData;

describe('Livewire support for Struct data objects', function (): void {
    beforeEach(function (): void {
        $this->componentContext = new ComponentContext(
            new stdClass(),
        );
    });

    describe('wireable integration', function (): void {
        test('implements Livewire wireable on explicit wireable DTOs', function (): void {
            // Arrange
            $dto = WireableSongData::create([
                'title' => 'Never Gonna Give You Up',
                'artist' => 'Rick Astley',
            ]);

            // Act
            $isWireable = $dto instanceof Wireable;
            $livewirePayload = $dto->toLivewire();
            $restoredDto = WireableSongData::fromLivewire([
                'title' => 'Giving Up on Love',
                'artist' => 'Rick Astley',
            ]);

            // Assert
            expect($isWireable)->toBeTrue()
                ->and($livewirePayload)->toBe([
                    'title' => 'Never Gonna Give You Up',
                    'artist' => 'Rick Astley',
                ])
                ->and($restoredDto)->toBeInstanceOf(WireableSongData::class);
        });
    });

    describe('synth discovery', function (): void {
        test('registers a Livewire synth for Struct DTO objects', function (): void {
            // Arrange
            $dto = SongData::create([
                'title' => 'Never Gonna Give You Up',
                'artist' => 'Rick Astley',
            ]);

            // Act
            $synth = resolve(HandleComponents::class)->findSynth(
                $dto,
                new stdClass(),
            );

            // Assert
            expect($synth)->toBeInstanceOf(DataSynth::class);
        });

        test('matches only Struct DTO objects', function (): void {
            // Arrange
            $dto = SongData::create([
                'title' => 'Never Gonna Give You Up',
                'artist' => 'Rick Astley',
            ]);

            // Act
            $matchForDto = DataSynth::match($dto);
            $matchForPlainObject = DataSynth::match(
                new stdClass(),
            );

            // Assert
            expect($matchForDto)->toBeTrue()
                ->and($matchForPlainObject)->toBeFalse();
        });
    });

    describe('synth hydration cycle', function (): void {
        beforeEach(function (): void {
            $this->synth = new DataSynth(
                $this->componentContext,
                'dto',
            );
        });

        test('dehydrates child values and exposes DTO class metadata', function (): void {
            // Arrange
            $dto = SongData::create([
                'title' => 'Never Gonna Give You Up',
                'artist' => 'Rick Astley',
            ]);
            $seen = [];

            // Act
            [$data, $meta] = $this->synth->dehydrate(
                $dto,
                function (string $key, mixed $value) use (&$seen): string {
                    $seen[$key] = $value;

                    return mb_strtoupper((string) $value);
                },
            );

            // Assert
            expect($seen)->toBe([
                'title' => 'Never Gonna Give You Up',
                'artist' => 'Rick Astley',
            ])->and($data)->toBe([
                'title' => 'NEVER GONNA GIVE YOU UP',
                'artist' => 'RICK ASTLEY',
            ])->and($meta)->toBe([
                'class' => SongData::class,
            ]);
        });

        test('hydrates child values back into DTO instances', function (): void {
            // Arrange
            $seen = [];

            // Act
            $dto = $this->synth->hydrate(
                [
                    'title' => 'never gonna give you up',
                    'artist' => 'rick astley',
                ],
                ['class' => SongData::class],
                function (string $key, mixed $value) use (&$seen): string {
                    $seen[$key] = $value;

                    return ucwords((string) $value);
                },
            );

            // Assert
            expect($seen)->toBe([
                'title' => 'never gonna give you up',
                'artist' => 'rick astley',
            ])->and($dto)->toBeInstanceOf(SongData::class)
                ->and($dto->title)->toBe('Never Gonna Give You Up')
                ->and($dto->artist)->toBe('Rick Astley');
        });

        describe('invalid hydration metadata', function (): void {
            test('rejects invalid synth metadata classes', function (mixed $meta): void {
                // Act
                $this->synth->hydrate(
                    ['title' => 'Never Gonna Give You Up', 'artist' => 'Rick Astley'],
                    $meta,
                    static fn (string $key, mixed $value): mixed => $value,
                );
            })->with([
                'missing class metadata' => [['type' => SongData::class]],
                'non dto class metadata' => [['class' => stdClass::class]],
                'non string class metadata' => [['class' => 123]],
            ])->throws(InvalidLivewireDataClassException::class, 'Livewire: Invalid Struct data class.');

            test('rejects invalid synth payloads', function (): void {
                // Act
                $this->synth->hydrate(
                    'not-an-array',
                    ['class' => SongData::class],
                    static fn (string $key, mixed $value): mixed => $value,
                );
            })->throws(InvalidLivewireDataPayloadException::class, 'Livewire: Invalid Struct data payload.');
        });
    });

    describe('property access on immutable DTOs', function (): void {
        beforeEach(function (): void {
            $this->synth = new DataSynth(
                $this->componentContext,
                'dto',
            );
        });

        test('gets and sets DTO properties through immutable replacement', function (): void {
            // Arrange
            $dto = SongData::create([
                'title' => 'Never Gonna Give You Up',
                'artist' => 'Rick Astley',
            ]);

            // Act
            $title = $this->synth->get($dto, 'title');
            $this->synth->set($dto, 'artist', 'A-ha');

            // Assert
            expect($title)->toBe('Never Gonna Give You Up')
                ->and($dto)->toBeInstanceOf(SongData::class)
                ->and($dto->title)->toBe('Never Gonna Give You Up')
                ->and($dto->artist)->toBe('A-ha');
        });
    });
});
