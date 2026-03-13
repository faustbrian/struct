<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Casts;

use Cline\Money\Monetary;
use Cline\Money\MoneyBag;
use Cline\Money\RationalMoney;
use Cline\Struct\Contracts\CastInterface;
use Cline\Struct\Metadata\PropertyMetadata;
use InvalidArgumentException;

use function array_is_list;
use function array_key_exists;
use function array_map;
use function count;
use function is_array;
use function is_float;
use function is_int;
use function is_string;
use function preg_split;
use function throw_unless;
use function trim;

/**
 * Casts values to and from exact money types.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class MonetaryCast implements CastInterface
{
    public function get(PropertyMetadata $property, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->targetClass($property) === RationalMoney::class
            ? $this->toRationalMoney($value)
            : $this->toMoneyBag($value);
    }

    public function set(PropertyMetadata $property, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $money = $this->get($property, $value);

        if ($money instanceof RationalMoney) {
            return $money->jsonSerialize();
        }

        return $money instanceof MoneyBag
            ? array_map(
                static fn (RationalMoney $containedMoney): array => $containedMoney->jsonSerialize(),
                $money->getMonies(),
            )
            : $value;
    }

    /**
     * @return class-string<MoneyBag|RationalMoney>
     */
    private function targetClass(PropertyMetadata $property): string
    {
        foreach ($property->types as $type) {
            if ($type === RationalMoney::class) {
                return RationalMoney::class;
            }

            if ($type === MoneyBag::class) {
                return MoneyBag::class;
            }
        }

        return MoneyBag::class;
    }

    private function toMoneyBag(mixed $value): MoneyBag
    {
        if ($value instanceof MoneyBag) {
            return $value;
        }

        if ($value instanceof Monetary) {
            return new MoneyBag()->add($value);
        }

        throw_unless(is_array($value), InvalidArgumentException::class, 'MonetaryCast only supports MoneyBag, RationalMoney, Monetary, or array values for money bags.');

        $bag = new MoneyBag();

        foreach ($this->entriesFromArray($value) as $money) {
            $bag->add($money);
        }

        return $bag;
    }

    private function toRationalMoney(mixed $value): RationalMoney
    {
        if ($value instanceof RationalMoney) {
            return $value;
        }

        if ($value instanceof Monetary) {
            $monies = $value->getMonies();
            $money = $monies[0] ?? null;

            throw_unless(count($monies) === 1 && $money instanceof RationalMoney, InvalidArgumentException::class, 'MonetaryCast can only hydrate RationalMoney from a single monetary value.');

            return $money;
        }

        if (is_string($value)) {
            return $this->entryFromString($value);
        }

        throw_unless(is_array($value), InvalidArgumentException::class, 'MonetaryCast only supports RationalMoney, Monetary, string, or array values for rational money.');

        /** @var array<string, mixed> $value */
        return $this->entryFromArray($value);
    }

    /**
     * @param  array<mixed>        $value
     * @return list<RationalMoney>
     */
    private function entriesFromArray(array $value): array
    {
        if ($this->isMoneyEntry($value)) {
            /** @var array<string, mixed> $value */
            return [$this->entryFromArray($value)];
        }

        if (array_is_list($value)) {
            $entries = [];

            foreach ($value as $entry) {
                if ($entry instanceof Monetary) {
                    $entries = [...$entries, ...$entry->getMonies()];

                    continue;
                }

                if (is_string($entry)) {
                    $entries[] = $this->entryFromString($entry);

                    continue;
                }

                throw_unless(is_array($entry), InvalidArgumentException::class, 'MoneyBag arrays must contain monetary arrays or strings.');

                /** @var array<string, mixed> $entry */
                $entries[] = $this->entryFromArray($entry);
            }

            return $entries;
        }

        $entries = [];

        foreach ($value as $currency => $amount) {
            throw_unless(is_string($currency), InvalidArgumentException::class, 'MoneyBag maps must use currency codes as string keys.');
            throw_unless(is_int($amount) || is_float($amount) || is_string($amount), InvalidArgumentException::class, 'MoneyBag maps must contain scalar amounts.');

            $entries[] = RationalMoney::of($this->normalizeNumber($amount), $currency);
        }

        return $entries;
    }

    /**
     * @param array<string, mixed> $value
     */
    private function entryFromArray(array $value): RationalMoney
    {
        $amount = $value['amount'] ?? null;
        $currency = $value['currency'] ?? null;

        throw_unless(is_int($amount) || is_float($amount) || is_string($amount), InvalidArgumentException::class, 'RationalMoney arrays must include a numeric amount.');
        throw_unless(is_string($currency) || is_int($currency), InvalidArgumentException::class, 'RationalMoney arrays must include a currency code.');

        /** @var float|int|string $amount */
        /** @var int|string $currency */
        return RationalMoney::of($this->normalizeNumber($amount), $currency);
    }

    private function entryFromString(string $value): RationalMoney
    {
        $parts = preg_split('/\s+/', trim($value), 2);

        $currency = $parts[0] ?? null;
        $amount = $parts[1] ?? null;

        throw_unless(is_array($parts) && count($parts) === 2 && is_string($currency) && $currency !== '' && is_string($amount), InvalidArgumentException::class, 'RationalMoney strings must be in the format "CUR amount".');

        return RationalMoney::of($this->normalizeNumber($amount), $currency);
    }

    /**
     * @param array<mixed> $value
     */
    private function isMoneyEntry(array $value): bool
    {
        return array_key_exists('amount', $value) && array_key_exists('currency', $value);
    }

    private function normalizeNumber(int|float|string $value): int|string
    {
        return is_float($value) ? (string) $value : $value;
    }
}
