[![GitHub Workflow Status][ico-tests]][link-tests]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

------

# struct

Lean, attribute-driven Laravel data objects for applications that want the useful parts
of `spatie/laravel-data` without the magic-heavy surface area.

## Requirements

> **Requires [PHP 8.4+](https://php.net/releases/)**

## Installation

```bash
composer require cline/struct
```

Optional features:

```bash
composer require cline/math
composer require cline/money
composer require cline/numerus
composer require cline/phone-number
composer require cline/postal-code
composer require ramsey/uuid
composer require cline/semver
```

Install `cline/math` when you want first-class arbitrary-precision
`BigNumber`, `BigInteger`, `BigDecimal`, or `BigRational` DTO properties.

```bash
composer require cline/money
```

Install `cline/money` when you want first-class `Money`, `RationalMoney`,
or `MoneyBag` DTO properties, or the `#[AsMoney(...)]` attribute for
scalar currency amounts.

```bash
composer require cline/numerus
```

Install `cline/numerus` when you want first-class `Numerus` DTO properties or
numeric normalization attributes such as `#[Round]`, `#[Clamp]`, and `#[Abs]`.

```bash
composer require cline/phone-number
```

Install `cline/phone-number` when you want first-class `PhoneNumber` DTO
properties, including scalar local-number hydration with
`#[AsPhoneNumber(regionCode: ...)]`.

```bash
composer require cline/postal-code
```

Install `cline/postal-code` when you want first-class `PostalCode` DTO
properties, including scalar postal-code hydration with
`#[AsPostalCode(country: ...)]`.

```bash
composer require cline/semver
```

Install `cline/semver` when you want first-class `Version` and `Constraint`
DTO properties for semantic version payloads.

Struct also ships built-in deterministic string transformation attributes such
as `#[Trim]`, `#[SnakeCase]`, `#[Slug]`, `#[Limit]`, `#[Replace]`, and
extraction attributes like `#[After]`, `#[Before]`, and `#[Between]`.
It also ships missing-value generators such as `#[Uuid]`, `#[Ulid]`,
`#[Random]`, and `#[Password]` for DTO fields that should be created only
when the input key is absent. Install `ramsey/uuid` if you want to use
`#[Uuid(...)]`. String transforms and generators now live under
`Cline\Struct\Attributes\Strings`, while compatibility aliases remain
available at `Cline\Struct\Attributes`. Collection transforms now live under
`Cline\Struct\Attributes\Collections`, including helpers such as
`#[Collections\\Reverse]`, `#[Collections\\Unique]`, `#[Collections\\Slice]`,
and `#[Collections\\OnlyKeys]`. Struct also ships first-class detached
`Illuminate\\Support\\Collection` property support through
`#[AsCollection(...)]`, separate from `DataList` and `DataCollection`,
including callback-based collection attributes such as
`#[Collections\\Filter(...)]`, `#[Collections\\Map(...)]`,
`#[Collections\\GroupBy(...)]`, `#[Collections\\UniqueBy(...)]`, and
`#[Collections\\MapInto(...)]`. Collection-returning transforms also
include attributes such as `#[Collections\\Where(...)]`,
`#[Collections\\Pluck(...)]`, `#[Collections\\Flatten(...)]`,
`#[Collections\\ChunkWhile(...)]`, `#[Collections\\MapToGroups(...)]`,
`#[Collections\\CountBy(...)]`, `#[Collections\\Diff(...)]`,
`#[Collections\\Merge(...)]`, `#[Collections\\Pipe(...)]`,
`#[Collections\\Tap(...)]`, and conditional wrappers like
`#[Collections\\When(...)]`. Derived collection results and generated
collection sources live under
`Cline\\Struct\\Attributes\\CollectionResults` and
`Cline\\Struct\\Attributes\\CollectionSources`, for methods such as
`#[Contains('posts', ...)]`, `#[After('posts', ...)]`,
`#[ToJson('posts')]`, `#[Reduce('totals', ...)]`,
`#[Wrap(source: 'name')]`, and `#[FromJson(source: 'payload')]`.
For deferred traversal, Struct also ships `LazyDataList` and
`LazyDataCollection` with explicit `#[AsLazyDataList(...)]` and
`#[AsLazyDataCollection(...)]` attributes. These lazy wrappers keep
Struct-owned collection semantics and typed item hydration, but they are
transport-focused and intentionally reject `Attributes\\Collections\\*`
transforms in v1.

## Documentation

- Consumer guide: [USAGE.md](USAGE.md)
- Benchmarking workflows: [BENCHMARK.md](BENCHMARK.md)

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please use the [GitHub security reporting form][link-security] rather than the issue queue.

## Credits

- [Brian Faust][link-maintainer]
- [All Contributors][link-contributors]

## License

The MIT License. Please see [License File](LICENSE.md) for more information.

[ico-tests]: https://github.com/faustbrian/struct/actions/workflows/quality-assurance.yaml/badge.svg
[ico-version]: https://img.shields.io/packagist/v/cline/struct.svg
[ico-license]: https://img.shields.io/badge/License-MIT-green.svg
[ico-downloads]: https://img.shields.io/packagist/dt/cline/struct.svg

[link-tests]: https://github.com/faustbrian/struct/actions
[link-packagist]: https://packagist.org/packages/cline/struct
[link-downloads]: https://packagist.org/packages/cline/struct
[link-security]: https://github.com/faustbrian/struct/security
[link-maintainer]: https://github.com/faustbrian
[link-contributors]: ../../contributors
