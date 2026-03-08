<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Contracts\ModelPayloadResolverInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Tests\Fixtures\Data\ResolvedSongData;
use Tests\Fixtures\Data\SongData;
use Tests\Fixtures\Models\Song;
use Tests\Fixtures\Support\GlobalModelPayloadResolver;

describe('SongData::collect', function (): void {
    beforeEach(function (): void {
        Schema::create('songs', function ($table): void {
            $table->id();
            $table->string('title');
            $table->string('artist');
            $table->timestamps();
        });

        Song::query()->create([
            'title' => 'Never Gonna Give You Up',
            'artist' => 'Rick Astley',
        ]);

        Song::query()->create([
            'title' => 'Giving Up on Love',
            'artist' => 'Rick Astley',
        ]);
    });

    describe('Happy Paths', function (): void {
        test('creates arrays of dto objects from plain arrays', function (): void {
            // Arrange
            $payload = [
                ['title' => 'Never Gonna Give You Up', 'artist' => 'Rick Astley'],
                ['title' => 'Giving Up on Love', 'artist' => 'Rick Astley'],
            ];

            // Act
            $songs = SongData::collect($payload);

            // Assert
            expect($songs)->toBeArray()
                ->and($songs)->toHaveCount(2)
                ->and($songs[0])->toBeInstanceOf(SongData::class);
        });

        test('preserves eloquent collections when collecting models', function (): void {
            // Arrange
            $models = Song::query()->get();

            // Act
            $songs = SongData::collect($models);

            // Assert
            expect($songs)->toBeInstanceOf(EloquentCollection::class)
                ->and($songs)->toHaveCount(2)
                ->and($songs->first())->toBeInstanceOf(SongData::class);
        });

        test('uses the dto model payload resolver when collecting eloquent models', function (): void {
            // Arrange
            app()->bind(ModelPayloadResolverInterface::class, GlobalModelPayloadResolver::class);
            $models = Song::query()->get();

            // Act
            $songs = ResolvedSongData::collect($models);

            // Assert
            expect($songs)->toBeInstanceOf(EloquentCollection::class)
                ->and($songs)->toHaveCount(2)
                ->and($songs->first()->title)->toBe('local-model:Never Gonna Give You Up')
                ->and($songs->last()->title)->toBe('local-model:Giving Up on Love');
        });
    });

    describe('Sad Paths', function (): void {});

    describe('Edge Cases', function (): void {
        test('preserves length aware paginators when collecting models', function (): void {
            // Arrange
            $paginator = Song::query()->paginate(perPage: 1);

            // Act
            $songs = SongData::collect($paginator);

            // Assert
            expect($songs)->toBeInstanceOf(LengthAwarePaginator::class)
                ->and($songs->items())->toHaveCount(1)
                ->and($songs->items()[0])->toBeInstanceOf(SongData::class)
                ->and($songs->total())->toBe(2);
        });

        test('preserves cursor paginators when collecting models', function (): void {
            // Arrange
            $paginator = Song::query()->orderBy('id')->cursorPaginate(perPage: 1);

            // Act
            $songs = SongData::collect($paginator);

            // Assert
            expect($songs)->toBeInstanceOf(CursorPaginator::class)
                ->and($songs->items())->toHaveCount(1)
                ->and($songs->items()[0])->toBeInstanceOf(SongData::class);
        });

        test('allows explicit collection overrides', function (): void {
            // Arrange
            $payload = [
                ['title' => 'Never Gonna Give You Up', 'artist' => 'Rick Astley'],
                ['title' => 'Giving Up on Love', 'artist' => 'Rick Astley'],
            ];

            // Act
            $songs = SongData::collectInto($payload, Collection::class);

            // Assert
            expect($songs)->toBeInstanceOf(Collection::class)
                ->and($songs)->toHaveCount(2)
                ->and($songs->first())->toBeInstanceOf(SongData::class);
        });
    });
});
