[![GitHub Workflow Status][ico-tests]][link-tests]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

------

# struct

## Benchmarking

This package currently keeps the existing `phpbench` suite and now also
ships a `cline/bench` mirror of the same DTO comparison scenarios.

Existing `phpbench` workflow:

```bash
composer bench
composer bench:compare
```

Docker profiling workflow with Xdebug:

```bash
make profile-phpinfo
XDEBUG_MODE=trace docker-compose run --rm profile php vendor/bin/phpbench run benchmarks/DataProfileBench.php
XDEBUG_MODE=profile docker-compose run --rm profile php vendor/bin/phpbench run benchmarks/BagDataProfileBench.php
```

Trace and profiler output is written to `build/xdebug/` on the host so it
can be inspected with tools such as QCacheGrind or KCachegrind.

New `cline/bench` workflow:

```bash
composer bench:cline
composer bench:cline:save
composer bench:cline:compare
```

The `cline/bench` suite lives in `benchmarks-cline/` and reuses the same
support graph as the `phpbench` benchmarks. This gives us a real migration
path and lets us compare `phpbench` output against the new runner on the
same benchmark subjects.

Lean, attribute-driven Laravel data objects for applications that want the useful parts
of `spatie/laravel-data` without the magic-heavy surface area.

## Requirements

> **Requires [PHP 8.4+](https://php.net/releases/)**

## Installation

```bash
composer require cline/struct
```

For the consumer-facing guide, see [USAGE.md](USAGE.md).

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

## Feature Set

Struct is being implemented to support:

- unified inbound and outbound casts
- built-in Carbon, CarbonImmutable, CarbonInterface, DateTimeInterface casts
- scalar value casting for primitive property types
- replace-empty-strings-with-null by default, with class and property opt-out
- `#[Validate('required|min:3')]`, `#[Validate([...])]`,
  `#[ValidateItems(...)]`, and custom `ValidationRule` support
- explicit validation only on `createWithValidation()`
- inferred validation rules with class and property-level opt-out or opt-in
- class and property level input/output name mapping
- JSON, XML, and YAML string serialization strategies
- lazy serialization controls with explicit includes, groups, and conditions
- Eloquent casts for `MyValue::class`, `castAsCollection()`, and
  `AsDataCollection::of(...)`
- `SensitiveParameter` aware serialization
- computed properties with inline or computer-class strategies
- Laravel-style factories via `#[UseFactory(...)]`
- undefined and superfluous key policies
- `DataList` and `DataCollection` containers for typed list and collection
  properties
- list item descriptors can be primitive types, data-object/enum class strings, or
  cast classes implementing Struct's `CastInterface` contract
- native enum hydration and serialization
- `Optional` sentinel support

## Usage

```php
use Cline\Struct\Attributes\Computed;
use Cline\Struct\Attributes\AsDataList;
use Cline\Struct\Attributes\Lazy;
use Cline\Struct\Attributes\LazyGroup;
use Cline\Struct\Attributes\MapName;
use Cline\Struct\Attributes\UseFactory;
use Cline\Struct\Attributes\UseValidator;
use Cline\Struct\Attributes\Validate;
use Cline\Struct\Attributes\ValidateItems;
use Cline\Struct\Attributes\WithoutInferredValidation;
use Cline\Struct\AbstractData;
use Cline\Struct\Factories\AbstractFactory;
use Cline\Struct\Enums\DataListType;
use Cline\Struct\Enums\NameMapper;
use Cline\Struct\Support\DataList;
use Cline\Struct\Support\Optional;
use Carbon\CarbonImmutable;

#[UseFactory(UserDataFactory::class)]
#[MapName(NameMapper::SnakeCase)]
final readonly class UserData extends AbstractData
{
    public function __construct(
        public int $id,
        #[Validate('required|min:3')]
        public string $name,
        public Optional|int $age,
        public Optional|string|null $email = null,
        public CarbonImmutable $createdAt,
        #[AsDataList(DataListType::String)]
        #[ValidateItems('string|min:2')]
        public DataList $tags = new DataList(),
        #[Lazy()]
        public array $analytics = [],
        #[LazyGroup('details')]
        public string $bio = '',
        #[Computed]
        public string $displayName = '',
    ) {}
}

$dto = UserData::create([
    'id' => '1',
    'name' => 'Brian',
    'created_at' => '2026-03-07T10:00:00+00:00',
    'tags' => [1, 2, 3],
]);

$validated = UserData::createWithValidation([
    'id' => 1,
    'name' => 'Brian',
    'created_at' => now()->toIso8601String(),
]);

$clone = $validated->with(name: 'Struct');

$validated->toArray();
(string) $validated;
UserData::factory()->count(3)->make();
UserData::collect(User::query()->paginate());
UserData::collectInto($users, Collection::class);
$validated->toArray(include: ['analytics'], groups: ['details']);
$validated->serializer()->include('analytics')->groups('details')->toJson();
```

List items can also use a cast class instead of a primitive descriptor:

```php
#[AsDataList(IntegerStringCast::class)]
public DataList $numbers;
```

That lets inbound values be normalized one way and serialized back out with
the cast's `set()` logic.

Lazy properties are serialization-only. They are omitted from output by
default and included explicitly through `include`, `groups`, or conditions:

```php
#[Lazy()]
public array $analytics = [];

#[LazyGroup('details')]
public string $bio = '';
```

```php
$user->toArray(include: ['analytics']);
$user->toArray(groups: ['details']);
$user->serializer()
    ->include('posts.author.profile')
    ->groups('details')
    ->toArray();
```

`ValidateItems` applies wildcard item rules for collection-like properties.
Use it when each element inside a `DataList`, `DataCollection`, or plain array
property needs its own validation rules:

```php
#[ValidateItems(['integer', 'min:10'])]
public DataList $scores;
```

Validation inference is enabled by default when calling
`createWithValidation()`, but you can control it explicitly:

```php
#[WithoutInferredValidation()]
final readonly class LooseData extends AbstractData
{
    // ...
}

#[UseValidator(UserValidator::class)]
final readonly class StrictData extends AbstractData
{
    public function __construct(
        #[Validate(['required', 'min:3', UppercaseValueRule::class])]
        public string $name,
        #[ValidateItems(['integer', 'min:10'])]
        public DataList $scores,
    ) {}
}
```

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

## Inspiration

- [spatie/laravel-data](https://github.com/spatie/laravel-data)
- [beacon-hq/bag](https://github.com/beacon-hq/bag)

[ico-tests]: https://github.com/faustbrian/:package_name/actions/workflows/quality-assurance.yaml/badge.svg
[ico-version]: https://img.shields.io/packagist/v/cline/:package_name.svg
[ico-license]: https://img.shields.io/badge/License-MIT-green.svg
[ico-downloads]: https://img.shields.io/packagist/dt/cline/:package_name.svg

[link-tests]: https://github.com/faustbrian/:package_name/actions
[link-packagist]: https://packagist.org/packages/cline/:package_name
[link-downloads]: https://packagist.org/packages/cline/:package_name
[link-security]: https://github.com/faustbrian/:package_name/security
[link-maintainer]: https://github.com/faustbrian
[link-contributors]: ../../contributors
