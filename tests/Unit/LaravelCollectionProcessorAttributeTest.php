<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tests\Fixtures\Data\LaravelCollectionProcessorData;
use Tests\Fixtures\Support\CollectionCallbacks\CollectionTapRecorder;
use Tests\Fixtures\Support\CollectionCallbacks\SpreadRecorder;

describe('processor Laravel collection attributes', function (): void {
    test('hydrates processor and source based Laravel collection attributes', function (): void {
        $spreadRecorder = new SpreadRecorder();
        $tapRecorder = new CollectionTapRecorder();

        app()->instance(SpreadRecorder::class, $spreadRecorder);
        app()->instance(CollectionTapRecorder::class, $tapRecorder);

        $data = LaravelCollectionProcessorData::create([
            'spreadEach' => [[1, 2], [3, 4]],
            'spreadMapped' => [[1, 2], [3, 4]],
            'ensuredStrings' => ['Alpha', 'Beta'],
            'tapped' => ['Alpha', 'Beta'],
            'jsonPayload' => '{"alpha":"Alpha","beta":"Beta"}',
        ]);

        expect($spreadRecorder->calls)->toBe([
            [1, 2, 0],
            [3, 4, 1],
            [1, 2, 0],
            [3, 4, 1],
        ])->and($data->spreadMapped->values()->all())->toBe([
            '1:2:0',
            '3:4:1',
        ])->and($data->ensuredStrings->values()->all())->toBe([
            'Alpha',
            'Beta',
        ])->and($tapRecorder->snapshots)->toBe([
            ['Alpha', 'Beta'],
        ])->and($data->tapped->values()->all())->toBe([
            'Alpha',
            'Beta',
        ])->and($data->fromJson->all())->toBe([
            'alpha' => 'Alpha',
            'beta' => 'Beta',
        ]);
    });
});
