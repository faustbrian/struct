<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Contracts\ModelPayloadResolverInterface;
use Cline\Struct\Contracts\RequestPayloadResolverInterface;
use Cline\Struct\Exceptions\DataValidationException;
use Cline\Struct\Exceptions\RequestDataValidationException;
use Illuminate\Support\Facades\Schema;
use Tests\Fixtures\Data\ResolvedSongData;
use Tests\Fixtures\Data\SongData;
use Tests\Fixtures\Models\Song;
use Tests\Fixtures\Support\GlobalModelPayloadResolver;
use Tests\Fixtures\Support\GlobalRequestPayloadResolver;

describe('LaravelIntegration', function (): void {
    beforeEach(function (): void {
        Schema::create('songs', function ($table): void {
            $table->id();
            $table->string('title');
            $table->string('artist');
            $table->timestamps();
        });

        app()->forgetInstance(RequestPayloadResolverInterface::class);
        app()->forgetInstance(ModelPayloadResolverInterface::class);
    });

    describe('Happy Paths', function (): void {
        test('creates a SongData DTO from an eloquent model', function (): void {
            // Arrange
            $song = Song::query()->create([
                'title' => 'Never Gonna Give You Up',
                'artist' => 'Rick Astley',
            ]);

            // Act
            $data = SongData::createFromModel($song);

            // Assert
            expect($data)->toBeInstanceOf(SongData::class)
                ->and($data->title)->toBe('Never Gonna Give You Up')
                ->and($data->artist)->toBe('Rick Astley');
        });

        test('validates while creating a SongData DTO from an eloquent model', function (): void {
            // Arrange
            $song = Song::query()->create([
                'title' => 'Whenever You Need Somebody',
                'artist' => 'Rick Astley',
            ]);

            // Act
            $data = SongData::createFromModelWithValidation($song);

            // Assert
            expect($data)->toBeInstanceOf(SongData::class)
                ->and($data->title)->toBe('Whenever You Need Somebody')
                ->and($data->artist)->toBe('Rick Astley');
        });

        test('creates a SongData DTO from an HTTP request payload', function (): void {
            // Arrange
            $request = request()->create('/struct/songs', 'POST', [
                'title' => 'Together Forever',
                'artist' => 'Rick Astley',
            ]);

            // Act
            $data = SongData::createFromRequest($request);

            // Assert
            expect($data)->toBeInstanceOf(SongData::class)
                ->and($data->title)->toBe('Together Forever')
                ->and($data->artist)->toBe('Rick Astley');
        });

        test('uses the global request payload resolver when one is bound', function (): void {
            // Arrange
            app()->bind(RequestPayloadResolverInterface::class, GlobalRequestPayloadResolver::class);
            $request = request()->create('/struct/songs', 'POST', [
                'title' => 'Together Forever',
                'artist' => 'Rick Astley',
            ]);

            // Act
            $data = SongData::createFromRequest($request);

            // Assert
            expect($data->title)->toBe('global-request:Together Forever')
                ->and($data->artist)->toBe('Rick Astley');
        });

        test('lets a DTO override the global request payload resolver', function (): void {
            // Arrange
            app()->bind(RequestPayloadResolverInterface::class, GlobalRequestPayloadResolver::class);
            $request = request()->create('/struct/songs', 'POST', [
                'title' => 'Together Forever',
                'artist' => 'Rick Astley',
            ]);

            // Act
            $data = ResolvedSongData::createFromRequest($request);

            // Assert
            expect($data->title)->toBe('local-request:Together Forever')
                ->and($data->artist)->toBe('Rick Astley');
        });

        test('builds request-aware SongData DTOs using request validation', function (): void {
            // Arrange
            $request = request()->create('/struct/songs', 'POST', [
                'title' => 'Together Forever',
                'artist' => 'Rick Astley',
            ]);

            // Act
            $data = SongData::createFromRequestWithValidation($request);

            // Assert
            expect($data->title)->toBe('Together Forever')
                ->and($data->artist)->toBe('Rick Astley');
        });

        test('uses the global model payload resolver when one is bound', function (): void {
            // Arrange
            app()->bind(ModelPayloadResolverInterface::class, GlobalModelPayloadResolver::class);
            $song = Song::query()->create([
                'title' => 'Never Gonna Give You Up',
                'artist' => 'Rick Astley',
            ]);

            // Act
            $data = SongData::createFromModel($song);

            // Assert
            expect($data->title)->toBe('global-model:Never Gonna Give You Up')
                ->and($data->artist)->toBe('Rick Astley');
        });

        test('lets a DTO override the global model payload resolver', function (): void {
            // Arrange
            app()->bind(ModelPayloadResolverInterface::class, GlobalModelPayloadResolver::class);
            $song = Song::query()->create([
                'title' => 'Never Gonna Give You Up',
                'artist' => 'Rick Astley',
            ]);

            // Act
            $data = ResolvedSongData::createFromModel($song);

            // Assert
            expect($data->title)->toBe('local-model:Never Gonna Give You Up')
                ->and($data->artist)->toBe('Rick Astley');
        });
    });

    describe('Sad Paths', function (): void {
        test('throws a DataValidationException when model payload is invalid', function (): void {
            // Arrange
            $action = fn (): SongData => SongData::createFromModelWithValidation([
                'title' => 'Together Forever',
            ]);

            // Act & Assert
            expect($action)->toThrow(DataValidationException::class);
        });

        test('throws a RequestDataValidationException when request payload is invalid', function (): void {
            // Arrange
            $request = request()->create('/struct/songs', 'POST', [
                'title' => 'Together Forever',
            ]);

            // Act & Assert
            expect(fn (): SongData => SongData::createFromRequestWithValidation($request))
                ->toThrow(RequestDataValidationException::class);
        });
    });

    describe('Edge Cases', function (): void {});
});
