<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Cline\Struct\Support\DateFormat;

describe('DateFormat', function (): void {
    test('captures configured formats and timezone consistently', function (): void {
        config([
            'struct.date_format' => ['Y-m-d H:i:s', \DATE_ATOM],
            'struct.date_timezone' => 'America/New_York',
        ]);

        $format = DateFormat::fromConfig();

        expect(DateFormat::all())->toBe(['Y-m-d H:i:s', \DATE_ATOM])
            ->and(DateFormat::primary())->toBe('Y-m-d H:i:s')
            ->and(DateFormat::timezone())->toBe('America/New_York')
            ->and($format->format)->toBe('Y-m-d H:i:s')
            ->and($format->timezone)->toBe('America/New_York');
    });

    test('parses and formats carbon values with configured timezone', function (): void {
        config([
            'struct.date_format' => ['Y-m-d H:i:s', \DATE_ATOM],
            'struct.date_timezone' => 'America/New_York',
        ]);

        $immutable = DateFormat::parseCarbonImmutable('2024-05-16 12:00:00');
        $mutable = DateFormat::parseCarbon('2024-05-16 12:00:00');
        $formatted = DateFormat::format(
            CarbonImmutable::create(2_024, 5, 16, 16, 0, 0, 'UTC'),
        );

        expect($immutable)->toBeInstanceOf(CarbonImmutable::class)
            ->and($immutable->timezone->getName())->toBe('America/New_York')
            ->and($mutable)->toBeInstanceOf(Carbon::class)
            ->and($mutable->timezone->getName())->toBe('America/New_York')
            ->and($formatted)->toBe('2024-05-16 12:00:00');
    });

    test('falls back across multiple configured formats', function (): void {
        $format = DateFormat::withConfig(
            \DATE_ATOM,
            'UTC',
            ['Y-m-d H:i:s', \DATE_ATOM],
        );

        expect($format->parseCarbonImmutableValue('2024-05-16T12:00:00+00:00'))
            ->toBeInstanceOf(CarbonImmutable::class)
            ->and($format->parseCarbonValue('2024-05-16T12:00:00+00:00'))
            ->toBeInstanceOf(Carbon::class);
    });

    test('parses single configured formats without fallback formats', function (): void {
        $format = DateFormat::withConfig('Y-m-d H:i:s', 'UTC');

        expect($format->parseCarbonImmutableValue('2024-05-16 12:00:00'))
            ->toBeInstanceOf(CarbonImmutable::class)
            ->and($format->parseCarbonValue('2024-05-16 12:00:00'))
            ->toBeInstanceOf(Carbon::class);
    });
});
