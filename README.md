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
```

Install `cline/math` when you want first-class arbitrary-precision
`BigNumber`, `BigInteger`, `BigDecimal`, or `BigRational` DTO properties.

```bash
composer require cline/money
```

Install `cline/money` when you want first-class `Money` DTO properties or
the `#[AsMoney(...)]` attribute for scalar currency amounts.

```bash
composer require cline/numerus
```

Install `cline/numerus` when you want first-class `Numerus` DTO properties or
numeric normalization attributes such as `#[Round]`, `#[Clamp]`, and `#[Abs]`.

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
