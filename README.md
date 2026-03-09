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

## Documentation

- Consumer guide: [USAGE.md](USAGE.md)
- Benchmarking workflows: [USAGE.md#benchmarking](USAGE.md#benchmarking)

## Goals

Struct is designed around a few hard constraints:

- arrays in, data objects out
- explicit validation through `createWithValidation()`
- attribute-driven metadata where possible
- immutable data objects with `with(...)` cloning instead of mutation
- recursion detection during hydration and serialization
- first-class Laravel ergonomics without hidden global behavior

## Design

Struct is built from a small kernel and a set of focused subsystems:

- metadata via `cline/attribute-reader`
- normalization for scalar casting and empty-string handling
- hydration through a unified cast contract with `get` and `set`
- validation via Laravel's validator, activated explicitly
- serialization with output mapping, computed properties, and
  `SensitiveParameter` support
- Eloquent cast integration for single data objects and `DataCollection` values
- factory support through `#[UseFactory(...)]`

The public model intentionally starts narrow:

- `AbstractData::create(array $input): static`
- `AbstractData::createWithValidation(array $input): static`
- `AbstractData::collect(array|Collection|LengthAwarePaginator|CursorPaginator $items)`
- `AbstractData::collectInto(array|Collection|LengthAwarePaginator|CursorPaginator $items, string $into)`
- `AbstractData::with(...$overrides): static`
- `AbstractData::factory(): AbstractFactory`
- `AbstractData::toArray(): array`
- `AbstractData::toJson(): string`
- `AbstractData::__toString(): string`

## Feature Highlights

- explicit `create()` and `createWithValidation()` entry points
- immutable data objects with `with(...)` cloning
- attribute-driven validation, casting, name mapping, and serialization
- typed `DataList` and `DataCollection` containers
- Laravel request, model, factory, and Eloquent cast integration
- lazy and conditional serialization with computed properties
- enum, date/time, and scalar coercion support
- `SensitiveParameter`-aware output handling

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
