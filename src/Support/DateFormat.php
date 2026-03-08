<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Support;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Support\Facades\Date;
use Stringable;

use const DATE_ATOM;
use const JSON_THROW_ON_ERROR;

use function count;
use function is_array;
use function is_scalar;
use function json_encode;
use function resolve;

/**
 * Resolves configured date formats for Struct hydration and serialization.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class DateFormat
{
    private ?DateTimeZone $timezoneObject;

    /**
     * @param list<string> $formats
     */
    public function __construct(
        public string $format,
        public ?string $timezone,
        public array $formats = [DATE_ATOM],
    ) {
        $this->timezoneObject = $timezone !== null ? new DateTimeZone($timezone) : null;
    }

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return self::fromConfig()->formats;
    }

    public static function primary(): string
    {
        return self::fromConfig()->format;
    }

    public static function parseCarbonImmutable(mixed $value): CarbonImmutable
    {
        return self::fromConfig()->parseCarbonImmutableValue($value);
    }

    public static function parseCarbon(mixed $value): Carbon
    {
        return self::fromConfig()->parseCarbonValue($value);
    }

    public static function format(
        DateTimeInterface $value,
        ?string $format = null,
        ?string $timezone = null,
    ): string {
        return self::fromConfig()->formatValue($value, $format, $timezone);
    }

    public static function formatWithTimezone(
        DateTimeInterface $value,
        string $format,
        ?string $timezone = null,
    ): string {
        return self::applyTimezoneToDateTimeWithTimezone($value, $timezone)->format($format);
    }

    /**
     * @param list<string> $formats
     */
    public static function withConfig(
        string $format,
        ?string $timezone,
        array $formats = [],
    ): self {
        if ($formats === []) {
            $formats = [$format];
        }

        return new self($format, $timezone, $formats);
    }

    public static function fromConfig(): self
    {
        return resolve(self::class);
    }

    public static function timezone(): ?string
    {
        return self::fromConfig()->timezone;
    }

    public function parseCarbonImmutableValue(mixed $value): CarbonImmutable
    {
        if ($value instanceof CarbonImmutable) {
            return self::applyTimezoneToCarbonImmutable($value, $this->timezone);
        }

        if ($value instanceof DateTimeInterface) {
            return self::applyTimezoneToCarbonImmutable(
                CarbonImmutable::instance($value),
                $this->timezone,
            );
        }

        $string = $this->stringify($value);

        if (count($this->formats) === 1) {
            return $this->parseSingleCarbonImmutableFormat($string, $this->formats[0]);
        }

        foreach ($this->formats as $format) {
            $date = $this->parseNativeFormat($string, $format);

            if ($date instanceof DateTimeImmutable) {
                return CarbonImmutable::instance($date);
            }
        }

        return new CarbonImmutable($string, $this->timezone);
    }

    public function parseCarbonValue(mixed $value): Carbon
    {
        if ($value instanceof Carbon) {
            return self::applyTimezoneToCarbon($value, $this->timezone);
        }

        if ($value instanceof DateTimeInterface) {
            return self::applyTimezoneToCarbon(Date::instance($value), $this->timezone);
        }

        $string = $this->stringify($value);

        if (count($this->formats) === 1) {
            return $this->parseSingleCarbonFormat($string, $this->formats[0]);
        }

        foreach ($this->formats as $format) {
            $date = $this->parseNativeFormat($string, $format);

            if ($date instanceof DateTimeImmutable) {
                return Date::instance($date);
            }
        }

        return new Carbon($string, $this->timezone);
    }

    public function formatValue(
        DateTimeInterface $value,
        ?string $format = null,
        ?string $timezone = null,
    ): string {
        return self::formatWithTimezone(
            $value,
            $format ?? $this->format,
            $timezone ?? $this->timezone,
        );
    }

    private static function applyTimezoneToCarbonImmutable(CarbonImmutable $value, ?string $timezone = null): CarbonImmutable
    {
        if ($timezone === null) {
            return $value;
        }

        return $value->setTimezone($timezone);
    }

    private static function applyTimezoneToCarbon(Carbon $value, ?string $timezone = null): Carbon
    {
        if ($timezone === null) {
            return $value;
        }

        return $value->copy()->setTimezone($timezone);
    }

    private static function applyTimezoneToDateTimeWithTimezone(
        DateTimeInterface $value,
        ?string $timezone,
    ): DateTimeInterface {
        if ($timezone === null) {
            return $value;
        }

        return match (true) {
            $value instanceof CarbonImmutable => self::applyTimezoneToCarbonImmutable(
                $value->setTimezone($timezone),
                $timezone,
            ),
            $value instanceof Carbon => self::applyTimezoneToCarbon(
                $value->copy()->setTimezone($timezone),
                $timezone,
            ),
            default => self::applyTimezoneToCarbonImmutable(
                CarbonImmutable::instance($value)->setTimezone($timezone),
                $timezone,
            ),
        };
    }

    private function parseSingleCarbonImmutableFormat(string $value, string $format): CarbonImmutable
    {
        $date = $this->parseNativeFormat($value, $format);

        if ($date instanceof DateTimeImmutable) {
            return CarbonImmutable::instance($date);
        }

        return new CarbonImmutable($value, $this->timezone);
    }

    private function parseSingleCarbonFormat(string $value, string $format): Carbon
    {
        $date = $this->parseNativeFormat($value, $format);

        if ($date instanceof DateTimeImmutable) {
            return Date::instance($date);
        }

        return new Carbon($value, $this->timezone);
    }

    private function parseNativeFormat(string $value, string $format): ?DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat($format, $value, $this->timezoneObject);

        if (!$date instanceof DateTimeImmutable) {
            return null;
        }

        $errors = DateTimeImmutable::getLastErrors();

        if (is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
            return null;
        }

        return $date;
    }

    private function stringify(mixed $value): string
    {
        return is_scalar($value) || $value instanceof Stringable
            ? (string) $value
            : json_encode($value, JSON_THROW_ON_ERROR);
    }
}
