# Usage

This is the consumer guide for Struct. It documents the public package surface
for applications that define data objects, validate payloads, serialize output,
integrate with Laravel, and extend Struct through its contracts.

## Installation

```bash
composer require cline/struct
```

Struct auto-discovers its service provider.

If you use Livewire support, install Livewire 4 in your application.

If you want the `#[Uuid(...)]` generated-value attribute, install
`ramsey/uuid` in your application as well.

## Quick Example

```php
<?php

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsDataList;
use Cline\Struct\Attributes\Computed;
use Cline\Struct\Attributes\Lazy;
use Cline\Struct\Attributes\LazyGroup;
use Cline\Struct\Attributes\MapName;
use Cline\Struct\Attributes\UseFactory;
use Cline\Struct\Attributes\Validate;
use Cline\Struct\Attributes\ValidateItems;
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

## What Struct Is

Struct is an attribute-driven, immutable data mapping package for Laravel.

It is built around a few explicit rules:

- arrays in, data objects out
- validation is opt-in through `createWithValidation()`
- data objects are immutable and clone through `with(...)`
- serialization is separate from hydration
- request/model integration is adapter-based, not embedded in property metadata

## Core API

Every Struct data object extends `AbstractData` and exposes the same core API:

- `create(array $input): static`
- `createWithValidation(array $input): static`
- `createFromRequest(Request $request): static`
- `createFromRequestWithValidation(Request $request): static`
- `createFromModel(array|Arrayable|Model $source): static`
- `createFromModelWithValidation(array|Arrayable|Model $source): static`
- `collect(array|Collection|LengthAwarePaginator|CursorPaginator $items)`
- `collectInto(array|Collection|LengthAwarePaginator|CursorPaginator $items, string $into)`
- `factory(): AbstractFactory`
- `with(...$overrides): static`
- `toArray(...)`
- `toJson(...)`
- `serializer(): DataSerializer`
- `__toString(): string`

## Defining a Data Object

```php
<?php

use Cline\Struct\AbstractData;

final readonly class SongData extends AbstractData
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {}
}
```

Create it from input:

```php
$song = SongData::create([
    'title' => 'Never Gonna Give You Up',
    'artist' => 'Rick Astley',
]);
```

Clone it immutably:

```php
$updated = $song->with(title: 'Together Forever');
```

Serialize it:

```php
$song->toArray();
$song->toJson();
(string) $song;
```

## Creating Data

### `create()`

Use `create()` for trusted, already-shaped payloads or internal application
data.

### `createWithValidation()`

Use `createWithValidation()` when payloads come from an application boundary.

Struct validates first and then hydrates. For normal data/model validation
failures it throws `DataValidationException`.

### Request Helpers

```php
$user = UserData::createFromRequest($request);
$user = UserData::createFromRequestWithValidation($request);
```

`createFromRequestWithValidation()` throws Laravel's `ValidationException`,
which makes it fit controller workflows more naturally.

### Model Helpers

```php
$user = UserData::createFromModel($model);
$user = UserData::createFromModelWithValidation($model);
```

These accept:

- arrays
- `Arrayable`
- Eloquent models

## Validation

Struct supports:

- explicit property rules with `#[Validate(...)]`
- collection item rules with `#[ValidateItems(...)]`
- inferred rules from property types
- class-level validator customization through `#[UseValidator(...)]`
- cascading validation through nested Struct data objects and Struct containers
- Laravel `ValidationRule` rule objects and rule class strings

### `#[Validate(...)]`

```php
<?php

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\Validate;

final readonly class UserData extends AbstractData
{
    public function __construct(
        #[Validate('required|min:3')]
        public string $name,
    ) {}
}
```

Supported forms:

```php
#[Validate('required|min:3')]
#[Validate(['required', 'min:3'])]
#[Validate([new UppercaseValueRule()])]
#[Validate([UppercaseValueRule::class])]
```

### `#[ValidateItems(...)]`

Use `ValidateItems` when a collection-like property needs item-level rules.

```php
<?php

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsDataList;
use Cline\Struct\Attributes\ValidateItems;
use Cline\Struct\Enums\DataListType;
use Cline\Struct\Support\DataList;

final readonly class ScoresData extends AbstractData
{
    public function __construct(
        #[AsDataList(DataListType::Int)]
        #[ValidateItems(['integer', 'min:10'])]
        public DataList $scores,
    ) {}
}
```

This applies to:

- `DataList`
- `DataCollection`
- `Illuminate\Support\Collection` when paired with `#[AsCollection(...)]`
- plain array properties

### Inferred Validation

Struct can infer validation rules from PHP types when you call
`createWithValidation()`.

Common examples:

- `string` -> `string`
- `int` -> `integer`
- `bool` -> `boolean`
- `float` -> `numeric`
- enums -> `Rule::enum(...)`
- date-like types -> `date`
- nested data/list/collection properties -> array-oriented rules

Global config:

```php
'validation' => [
    'infer_rules' => true,
],
```

Disable inference for an entire class:

```php
#[WithoutInferredValidation()]
final readonly class LooseData extends AbstractData
{
    // ...
}
```

Opt in explicitly:

```php
#[WithInferredValidation()]
final readonly class StrictData extends AbstractData
{
    // ...
}
```

Both attributes can also be used per property.

### Validator Mutators

Use `#[UseValidator(...)]` when a data object needs richer validator behavior.

```php
<?php

use App\Validation\UserValidator;
use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\UseValidator;

#[UseValidator(UserValidator::class)]
final readonly class UserData extends AbstractData
{
    public function __construct(
        public string $name,
    ) {}
}
```

Validator mutators are the right place for:

- additional manual rules
- messages
- custom attribute labels
- `stopOnFirstFailure`
- custom error bags
- `withValidator(...)` hooks
- custom `ValidationRule` objects

### Validation Cascade

When the root object is created through `createWithValidation()`, Struct also
cascades validation into:

- nested Struct data objects
- `DataList` items
- `DataCollection` items

## Name Mapping

Struct supports input and output name mapping at class and property level.

### Class-Level Mapping

```php
<?php

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\MapName;
use Cline\Struct\Enums\NameMapper;

#[MapName(NameMapper::SnakeCase)]
final readonly class UserData extends AbstractData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
    ) {}
}
```

Built-in mappers:

- `NameMapper::None`
- `NameMapper::SnakeCase`
- `NameMapper::CamelCase`
- `NameMapper::PascalCase`

### Property-Level Mapping

```php
<?php

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\MapInputName;
use Cline\Struct\Attributes\MapOutputName;

final readonly class UserData extends AbstractData
{
    public function __construct(
        #[MapInputName('first_name')]
        #[MapOutputName('firstName')]
        public string $name,
    ) {}
}
```

Strategy-based variants also exist:

- `#[MapInputNameUsing(...)]`
- `#[MapOutputNameUsing(...)]`

## Casting and Hydration

Struct uses a single cast model for inbound and outbound transformation.

### Built-In Hydration

Struct natively supports:

- scalar coercion for `int`, `float`, `bool`, `string`, and `array`
- enums
- nested Struct data objects
- `BigNumber`, `BigInteger`, `BigDecimal`, and `BigRational` when `cline/math` is installed
- `Money`, `RationalMoney`, and `MoneyBag` when `cline/money` is installed
- `Numerus` when `cline/numerus` is installed
- `PhoneNumber` when `cline/phone-number` is installed
- `PostalCode` when `cline/postal-code` is installed
- `Version` and `Constraint` when `cline/semver` is installed
- `Carbon`
- `CarbonImmutable`
- `CarbonInterface`
- `DateTimeInterface`, defaulting to `CarbonImmutable`

### Built-In Big Number Casts

When `cline/math` is installed, Struct can auto-cast arbitrary-precision
numbers directly from scalar DTO input.

```php
<?php

use Cline\Math\BigDecimal;
use Cline\Math\BigInteger;
use Cline\Math\BigNumber;
use Cline\Math\BigRational;
use Cline\Struct\AbstractData;

final readonly class LedgerData extends AbstractData
{
    public function __construct(
        public BigInteger $count,
        public BigDecimal $rate,
        public BigRational $ratio,
        public BigNumber $dynamic,
    ) {}
}
```

Supported scalar inputs are `int`, `string`, and finite `float` values.
Serialization returns the math type's string representation.

### Built-In Money Casts

When `cline/money` is installed, Struct can auto-cast `Cline\Money\Money`,
`Cline\Money\RationalMoney`, and `Cline\Money\MoneyBag` properties. `Money`
properties can also hydrate scalar amounts when a DTO declares a default
currency with `#[AsMoney(...)]`.

```php
<?php

use Cline\Money\Money;
use Cline\Money\MoneyBag;
use Cline\Money\RationalMoney;
use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\Money\AsMoney;

final readonly class InvoiceData extends AbstractData
{
    public function __construct(
        public Money $total,
        public RationalMoney $exactTotal,
        public MoneyBag $totalsByCurrency,
        #[AsMoney(currency: 'USD')]
        public Money $subtotal,
        #[AsMoney(currency: 'JPY', minor: true)]
        public Money $minorUnits,
    ) {}
}
```

Structured payloads can use Money's serialized shape:

```php
[
    'amount' => '12.345',
    'currency' => 'USD',
    'context' => [
        'type' => 'custom',
        'scale' => 3,
        'step' => 1,
    ],
]
```

`RationalMoney` uses the same `amount` / `currency` shape without a
`context` key, and `MoneyBag` serializes as a list of those exact-money
entries.

### Built-In Numeric Casts

When `cline/numerus` is installed, Struct can auto-cast `Cline\Numerus\Numerus`
properties and can normalize scalar numeric DTO fields through attributes.

```php
<?php

use Cline\Numerus\Numerus;
use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\Numerus\Abs;
use Cline\Struct\Attributes\Numerus\Clamp;
use Cline\Struct\Attributes\Numerus\Round;
use Cline\Struct\Attributes\Numerus\RoundHalfEven;

final readonly class InvoiceLineData extends AbstractData
{
    public function __construct(
        public Numerus $unitPrice,
        #[Round(precision: 2)]
        public float $subtotal,
        #[RoundHalfEven(precision: 2)]
        public float $tax,
        #[Clamp(min: 0, max: 100)]
        public int $discountPercent,
        #[Abs]
        public int $quantityDelta,
    ) {}
}
```

Convenience attributes are also available for common rounding modes:

- `#[RoundUp]`
- `#[RoundDown]`
- `#[RoundHalfUp]`
- `#[RoundHalfDown]`
- `#[RoundHalfEven]`
- `#[RoundCeiling]`
- `#[RoundFloor]`
- `#[Ceil]`
- `#[Floor]`
- `#[Clamp]`
- `#[Abs]`

### Built-In Phone Number Casts

When `cline/phone-number` is installed, Struct can auto-cast
`Cline\PhoneNumber\PhoneNumber` properties. Scalar local-number payloads can
declare a default parsing region with `#[AsPhoneNumber(...)]`.

```php
<?php

use Cline\PhoneNumber\PhoneNumber;
use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\PhoneNumber\AsPhoneNumber;

final readonly class ContactData extends AbstractData
{
    public function __construct(
        public PhoneNumber $international,
        #[AsPhoneNumber(regionCode: 'US')]
        public PhoneNumber $local,
    ) {}
}
```

Structured payloads can use a `phoneNumber` and optional `regionCode` shape:

```php
[
    'phoneNumber' => '202-555-0123',
    'regionCode' => 'US',
]
```

Serialization returns the normalized E.164 string representation.

### Built-In Postal Code Casts

When `cline/postal-code` is installed, Struct can auto-cast
`Cline\PostalCode\PostalCode` properties. Scalar postal code payloads must
declare a country with `#[AsPostalCode(...)]`.

```php
<?php

use Cline\PostalCode\PostalCode;
use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\PostalCode\AsPostalCode;

final readonly class AddressData extends AbstractData
{
    public function __construct(
        public PostalCode $shipping,
        #[AsPostalCode(country: 'CA')]
        public PostalCode $billing,
    ) {}
}
```

Structured payloads can use this shape:

```php
[
    'postalCode' => '12345-6789',
    'country' => 'US',
]
```

Serialization returns the same `postalCode` and `country` shape.

### Built-In SemVer Casts

When `cline/semver` is installed, Struct can auto-cast
`Cline\SemVer\Version` and `Cline\SemVer\Constraint` properties directly from
strings.

```php
<?php

use Cline\SemVer\Constraint;
use Cline\SemVer\Version;
use Cline\Struct\AbstractData;

final readonly class ReleaseData extends AbstractData
{
    public function __construct(
        public Version $version,
        public Constraint $constraint,
    ) {}
}
```

Serialization returns the normalized string form of each semantic version
value object.

### Built-In String Attributes

Struct also supports deterministic string normalization attributes for scalar
string DTO fields.

```php
<?php

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\Strings\After;
use Cline\Struct\Attributes\Strings\Headline;
use Cline\Struct\Attributes\Strings\Limit;
use Cline\Struct\Attributes\Strings\Slug;
use Cline\Struct\Attributes\Strings\Squish;
use Cline\Struct\Attributes\Strings\Trim;

final readonly class ArticleData extends AbstractData
{
    public function __construct(
        #[Trim]
        #[Squish]
        #[Headline]
        public string $title,
        #[Trim]
        #[Slug]
        public string $slug,
        #[Limit(160)]
        public string $excerpt,
        #[After(':')]
        public string $externalId,
    ) {}
}
```

Available built-in string attributes include:

- `#[Trim]`
- `#[LeftTrim]`
- `#[RightTrim]`
- `#[Squish]`
- `#[Lowercase]`
- `#[Uppercase]`
- `#[Titlecase]`
- `#[Headline]`
- `#[Ascii]`
- `#[Transliterate]`
- `#[Slug]`
- `#[SnakeCase]`
- `#[KebabCase]`
- `#[CamelCase]`
- `#[StudlyCase]`
- `#[PascalCase]`
- `#[Limit]`
- `#[Words]`
- `#[Take]`
- `#[Start]`
- `#[Finish]`
- `#[Wrap]`
- `#[Unwrap]`
- `#[ChopStart]`
- `#[ChopEnd]`
- `#[After]`
- `#[AfterLast]`
- `#[Before]`
- `#[BeforeLast]`
- `#[Between]`
- `#[BetweenFirst]`
- `#[Numbers]`
- `#[Deduplicate]`
- `#[Replace]`
- `#[ReplaceFirst]`
- `#[ReplaceLast]`
- `#[ReplaceStart]`
- `#[ReplaceEnd]`
- `#[Mask]`
- `#[PadLeft]`
- `#[PadRight]`
- `#[PadBoth]`
- `#[Reverse]`
- `#[Repeat]`

String attributes are applied in declaration order during hydration. Struct
does not re-run them during serialization, which preserves non-idempotent
transforms such as `#[After]`, `#[Before]`, and `#[Between]`.
Their canonical namespace is `Cline\Struct\Attributes\Strings`, while
compatibility aliases remain available under `Cline\Struct\Attributes`.

### Built-In Generated Values

Struct also supports missing-only string generators for new identifiers,
tokens, and passwords.

```php
<?php

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\Strings\Password;
use Cline\Struct\Attributes\Strings\Ulid;
use Cline\Struct\Attributes\Strings\Uuid;

final readonly class UserData extends AbstractData
{
    public function __construct(
        #[Uuid(version: 7)]
        public string $id,
        #[Ulid(lowerCase: true)]
        public string $publicId,
        #[Password(length: 20, symbols: false, lowerCase: true)]
        public string $temporaryPassword,
    ) {}
}
```

Available built-in generated-value attributes include:

- `#[Uuid]`
- `#[Ulid]`
- `#[Random]`
- `#[Password]`

Generated-value attributes follow these rules:

- they only run when the input key is missing
- explicit input always wins
- explicit `null` is preserved under normal DTO rules
- generated values are visible to `createWithValidation()`
- `with(...)` preserves existing generated values and does not rerun them
- they are only supported on `string` and `?string` properties
- they cannot be combined with `Optional`
- their canonical namespace is `Cline\Struct\Attributes\Strings`

`#[Uuid]` supports all UUID versions from `1` through `7`. Versions `3`
and `5` require `namespace` and `name`, while version `2` may additionally
use `localDomain`, `localIdentifier`, `node`, and `clockSeq`.

### Built-In Collection Attributes

Struct also supports deterministic collection transforms for plain `array`
properties, `DataList`, `DataCollection`, and
`Illuminate\Support\Collection` properties declared with
`#[AsCollection(...)]`.

```php
<?php

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsCollection;
use Cline\Struct\Attributes\AsDataList;
use Cline\Struct\Attributes\Collections\RejectNulls;
use Cline\Struct\Attributes\Collections\Reverse;
use Cline\Struct\Attributes\Collections\Take;
use Cline\Struct\Attributes\Collections\Unique;
use Cline\Struct\Enums\DataListType;
use Cline\Struct\Support\DataList;
use Illuminate\Support\Collection;

final readonly class BatchData extends AbstractData
{
    public function __construct(
        #[Reverse]
        public array $payload,
        #[AsDataList(DataListType::String)]
        #[Unique]
        #[Take(10)]
        public DataList $tags,
        #[AsCollection(DataListType::String)]
        #[RejectNulls]
        public Collection $authors,
        #[RejectNulls]
        public array $metadata,
    ) {}
}
```

Available built-in collection attributes include:

- `#[Collections\Reverse]`
- `#[Collections\RejectNulls]`
- `#[Collections\RejectEmptyStrings]`
- `#[Collections\RejectFalsy]`
- `#[Collections\Unique]`
- `#[Collections\Slice]`
- `#[Collections\Take]`
- `#[Collections\Values]`
- `#[Collections\OnlyKeys]`
- `#[Collections\ExceptKeys]`
- `#[Collections\SortValues]`
- `#[Collections\SortKeys]`

Collection attributes follow these rules:

- they run in declaration order during hydration
- they are not re-run during serialization
- plain `array` properties preserve keys unless an attribute explicitly reindexes
- `DataCollection` preserves keys by default
- `Collection` preserves keys by default
- `DataList` always reindexes because it is a list container
- key-based attributes such as `OnlyKeys`, `ExceptKeys`, and `SortKeys`
  are intentionally rejected on `DataList`

### Callback-Based Laravel Collection Attributes

`Illuminate\Support\Collection` properties declared with
`#[AsCollection(...)]` also support callback-driven transforms that resolve
an invokable helper from the container when bound, or instantiate it
directly when it has a zero-argument constructor.

```php
<?php

use App\Struct\CollectionCallbacks\IsPublished;
use App\Struct\CollectionCallbacks\PostSlugMap;
use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsCollection;
use Cline\Struct\Attributes\Collections\Filter;
use Cline\Struct\Attributes\Collections\Map;
use App\Data\PostData;
use Illuminate\Support\Collection;

final readonly class FeedData extends AbstractData
{
    public function __construct(
        #[AsCollection(PostData::class)]
        #[Filter(IsPublished::class)]
        #[Map(PostSlugMap::class)]
        public Collection $posts,
    ) {}
}
```

Available callback-based Laravel collection attributes:

- `#[Collections\Filter(...)]`
- `#[Collections\Reject(...)]`
- `#[Collections\Map(...)]`
- `#[Collections\MapInto(...)]`
- `#[Collections\MapWithKeys(...)]`
- `#[Collections\FlatMap(...)]`
- `#[Collections\Each(...)]`
- `#[Collections\Where(...)]`
- `#[Collections\WhereStrict(...)]`
- `#[Collections\WhereIn(...)]`
- `#[Collections\WhereInStrict(...)]`
- `#[Collections\WhereNotIn(...)]`
- `#[Collections\WhereNotInStrict(...)]`
- `#[Collections\WhereNull(...)]`
- `#[Collections\WhereNotNull(...)]`
- `#[Collections\WhereBetween(...)]`
- `#[Collections\WhereNotBetween(...)]`
- `#[Collections\Pluck(...)]`
- `#[Collections\Flatten(...)]`
- `#[Collections\Collapse(...)]`
- `#[Collections\CollapseWithKeys(...)]`
- `#[Collections\ChunkWhile(...)]`
- `#[Collections\MapToGroups(...)]`
- `#[Collections\SortBy(...)]`
- `#[Collections\SortByDesc(...)]`
- `#[Collections\SortKeysDesc(...)]`
- `#[Collections\SortKeysUsing(...)]`
- `#[Collections\GroupBy(...)]`
- `#[Collections\KeyBy(...)]`
- `#[Collections\UniqueBy(...)]`
- `#[Collections\UniqueStrict(...)]`
- `#[Collections\Duplicates(...)]`
- `#[Collections\DuplicatesStrict(...)]`
- `#[Collections\SkipUntil(...)]`
- `#[Collections\SkipWhile(...)]`
- `#[Collections\TakeUntil(...)]`
- `#[Collections\TakeWhile(...)]`
- `#[Collections\Partition(...)]`
- `#[Collections\Chunk(...)]`
- `#[Collections\Sliding(...)]`
- `#[Collections\Zip(...)]`
- `#[Collections\Concat(...)]`
- `#[Collections\CountBy(...)]`
- `#[Collections\WhereInstanceOf(...)]`
- `#[Collections\Except(...)]`
- `#[Collections\Flip(...)]`
- `#[Collections\ForPage(...)]`
- `#[Collections\Keys(...)]`
- `#[Collections\Dot(...)]`
- `#[Collections\Undot(...)]`
- `#[Collections\Multiply(...)]`
- `#[Collections\Nth(...)]`
- `#[Collections\Only(...)]`
- `#[Collections\Pad(...)]`
- `#[Collections\Prepend(...)]`
- `#[Collections\Push(...)]`
- `#[Collections\Put(...)]`
- `#[Collections\Random(...)]`
- `#[Collections\Select(...)]`
- `#[Collections\Shuffle(...)]`
- `#[Collections\Skip(...)]`
- `#[Collections\Sort(...)]`
- `#[Collections\SortDesc(...)]`
- `#[Collections\Split(...)]`
- `#[Collections\SplitIn(...)]`
- `#[Collections\Transform(...)]`
- `#[Collections\EachSpread(...)]`
- `#[Collections\MapSpread(...)]`
- `#[Collections\Ensure(...)]`
- `#[Collections\CrossJoin(...)]`
- `#[Collections\Diff(...)]`
- `#[Collections\DiffAssoc(...)]`
- `#[Collections\DiffAssocUsing(...)]`
- `#[Collections\DiffKeys(...)]`
- `#[Collections\Intersect(...)]`
- `#[Collections\IntersectUsing(...)]`
- `#[Collections\IntersectAssoc(...)]`
- `#[Collections\IntersectAssocUsing(...)]`
- `#[Collections\IntersectByKeys(...)]`
- `#[Collections\Merge(...)]`
- `#[Collections\MergeRecursive(...)]`
- `#[Collections\Replace(...)]`
- `#[Collections\ReplaceRecursive(...)]`
- `#[Collections\Splice(...)]`
- `#[Collections\Union(...)]`
- `#[Collections\Tap(...)]`
- `#[Collections\Pipe(...)]`
- `#[Collections\PipeInto(...)]`
- `#[Collections\PipeThrough(...)]`
- `#[Collections\When(...)]`
- `#[Collections\Unless(...)]`
- `#[Collections\WhenEmpty]`
- `#[Collections\WhenNotEmpty]`
- `#[Collections\UnlessEmpty]`
- `#[Collections\UnlessNotEmpty]`

These attributes are only supported on `Collection` properties declared with
`#[AsCollection(...)]`. They are intentionally rejected on `array`,
`DataList`, and `DataCollection`.

Callback contracts:

- `FiltersCollectionItemsInterface` for `Filter`, `Reject`, and `Partition`
- `FiltersCollectionItemsInterface` for `SkipUntil`, `SkipWhile`,
  `TakeUntil`, and `TakeWhile`
- `MapsCollectionItemsInterface` for `Map` and `FlatMap`
- `MapsCollectionItemsWithKeysInterface` for `MapWithKeys`
- `MapsCollectionItemsWithKeysInterface` for `MapToGroups`
- `PerformsCollectionActionInterface` for `Each`
- `SpreadsCollectionItemsInterface` for `EachSpread` and `MapSpread`
- `ComputesCollectionSortValueInterface` for `SortBy`
- `ComputesCollectionSortValueInterface` for `SortByDesc`
- `ComputesCollectionGroupKeyInterface` for `GroupBy`, `KeyBy`, and
  `UniqueBy`, and `CountBy`
- `ChunksCollectionItemsInterface` for `ChunkWhile`
- `ComparesCollectionKeysInterface` for `SortKeysUsing`
- `ComparesCollectionKeysInterface` for `DiffAssocUsing` and
  `IntersectAssocUsing`
- `ComparesCollectionValuesInterface` for `IntersectUsing`
- `DecidesCollectionPipelineConditionInterface` for `When` and `Unless`
- `TapsCollectionValueInterface` for `Tap`
- `PipesCollectionValueInterface` for `Pipe`, `PipeInto`, and
  `PipeThrough`

`MapInto(...)` is type-driven rather than callback-driven. It maps each
item into a target Struct data class and is useful when your collection
starts as scalar or array payloads but should end up as DTO instances.

`When*` and `Unless*` attributes are next-transform wrappers. They only
control the immediately following Laravel collection attribute.

### Lazy Laravel Collection Attributes

`Illuminate\Support\LazyCollection` properties are supported through
`#[AsLazyCollection(...)]`. This lane is intentionally detached from
`#[AsCollection(...)]` and preserves laziness strictly.

```php
<?php

use App\Struct\CollectionCallbacks\IsPublished;
use App\Struct\CollectionCallbacks\PostLabelMap;
use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsLazyCollection;
use Cline\Struct\Attributes\Collections\Filter;
use Cline\Struct\Attributes\Collections\Map;
use Cline\Struct\Enums\DataListType;
use Illuminate\Support\LazyCollection;

final readonly class DeferredFeedData extends AbstractData
{
    public function __construct(
        #[AsLazyCollection(DataListType::String)]
        #[Filter(IsPublished::class)]
        #[Map(PostLabelMap::class)]
        public LazyCollection $labels,
    ) {}
}
```

Currently supported lazy-safe transforms:

- `#[Collections\Map(...)]`
- `#[Collections\Filter(...)]`
- `#[Collections\Skip(...)]`
- `#[Collections\Take(...)]`

Rules for `LazyCollection` properties:

- the property must be declared as `Illuminate\Support\LazyCollection`
- use `#[AsLazyCollection(...)]` when you need typed item hydration
- source attributes like `Wrap`, `Range`, `Times`, and `FromJson` can
  target `LazyCollection` properties
- derived `CollectionResults` may read from lazy sources and evaluate
  them terminally
- eager-only collection transforms are rejected on `LazyCollection`
  properties instead of silently materializing them

### Derived Collection Results

Use `Cline\Struct\Attributes\CollectionResults` when a property should
be derived from another `Collection` or `LazyCollection` property instead
of being hydrated from input directly.

```php
<?php

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsCollection;
use Cline\Struct\Attributes\CollectionResults\Contains;
use Cline\Struct\Attributes\CollectionResults\Sum;
use Cline\Struct\Enums\DataListType;
use Illuminate\Support\Collection;

final readonly class MetricsData extends AbstractData
{
    public function __construct(
        #[AsCollection(DataListType::Int)]
        public Collection $totals,
        #[Contains('totals', 10)]
        public bool $hasTen,
        #[Sum('totals')]
        public int $sum,
    ) {}
}
```

Available derived result attributes:

- `#[CollectionResults\Contains(...)]`
- `#[CollectionResults\ContainsStrict(...)]`
- `#[CollectionResults\DoesntContain(...)]`
- `#[CollectionResults\DoesntContainStrict(...)]`
- `#[CollectionResults\Every(...)]`
- `#[CollectionResults\Some(...)]`
- `#[CollectionResults\After(...)]`
- `#[CollectionResults\Before(...)]`
- `#[CollectionResults\First(...)]`
- `#[CollectionResults\FirstOrFail(...)]`
- `#[CollectionResults\Last(...)]`
- `#[CollectionResults\Sole(...)]`
- `#[CollectionResults\FirstWhere(...)]`
- `#[CollectionResults\Get(...)]`
- `#[CollectionResults\Has(...)]`
- `#[CollectionResults\HasAny(...)]`
- `#[CollectionResults\HasMany(...)]`
- `#[CollectionResults\HasSole(...)]`
- `#[CollectionResults\IsEmpty(...)]`
- `#[CollectionResults\IsNotEmpty(...)]`
- `#[CollectionResults\Search(...)]`
- `#[CollectionResults\Value(...)]`
- `#[CollectionResults\Count(...)]`
- `#[CollectionResults\Sum(...)]`
- `#[CollectionResults\Min(...)]`
- `#[CollectionResults\Max(...)]`
- `#[CollectionResults\Avg(...)]`
- `#[CollectionResults\Average(...)]`
- `#[CollectionResults\Median(...)]`
- `#[CollectionResults\Mode(...)]`
- `#[CollectionResults\Percentage(...)]`
- `#[CollectionResults\Reduce(...)]`
- `#[CollectionResults\ReduceSpread(...)]`
- `#[CollectionResults\Implode(...)]`
- `#[CollectionResults\Join(...)]`
- `#[CollectionResults\Pop(...)]`
- `#[CollectionResults\Shift(...)]`
- `#[CollectionResults\Pull(...)]`
- `#[CollectionResults\All(...)]`
- `#[CollectionResults\ToArray(...)]`
- `#[CollectionResults\ToJson(...)]`
- `#[CollectionResults\ToPrettyJson(...)]`
- `#[CollectionResults\Random(...)]`
- `#[CollectionResults\Unwrap(...)]`

These attributes require an explicit source property name and operate on
the source collection after it has finished hydrating and transforming.
When the source is a `LazyCollection`, the result attribute evaluates it
terminally to compute the derived value.

### Generated Collection Sources

Use `Cline\Struct\Attributes\CollectionSources` when a `Collection` or
`LazyCollection` property should be generated instead of read from
collection payload input.

```php
<?php

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsCollection;
use Cline\Struct\Attributes\CollectionSources\Range;
use Cline\Struct\Attributes\CollectionSources\Wrap;
use Cline\Struct\Enums\DataListType;
use Illuminate\Support\Collection;

final readonly class GeneratedData extends AbstractData
{
    public function __construct(
        public string $name,
        #[AsCollection(DataListType::Mixed)]
        #[Wrap(source: 'name')]
        public Collection $wrappedName,
        #[AsCollection(DataListType::Int)]
        #[Range(1, 3)]
        public Collection $numbers,
    ) {}
}
```

Available source attributes:

- `#[CollectionSources\Wrap(...)]`
- `#[CollectionSources\Range(...)]`
- `#[CollectionSources\Times(...)]`
- `#[CollectionSources\FromJson(...)]`

### Custom Property Casts

Use `#[CastWith(...)]` for explicit custom casting.

```php
<?php

use App\Casts\MoneyCast;
use App\Values\Money;
use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\CastWith;

final readonly class ProductData extends AbstractData
{
    public function __construct(
        #[CastWith(MoneyCast::class)]
        public Money $price,
    ) {}
}
```

Custom casts implement Struct's unified cast contract and are responsible for:

- `get()`
  - external value -> internal value
- `set()`
  - internal value -> external value

This is how you handle cases where input and output formats differ.

### Empty String Handling

By default, empty strings are normalized to `null`.

Global config:

```php
'replace_empty_strings_with_null' => true,
```

Per-class or per-property overrides:

- `#[ReplaceEmptyStringsWithNull]`
- `#[DoNotReplaceEmptyStringWithNull]`

## Optional Values

Use `Optional` when you need to distinguish:

- key not provided
- key provided with `null`
- key provided with a value

```php
<?php

use Cline\Struct\AbstractData;
use Cline\Struct\Support\Optional;

final readonly class UserPatchData extends AbstractData
{
    public function __construct(
        public Optional|int $age,
        public Optional|string|null $email = null,
    ) {}
}
```

This is especially useful for patch and partial-update style data objects.

## Enums

Enums are first-class types in Struct.

- backed enums hydrate from backing values
- unit enums hydrate from case names
- backed enums serialize to backing values
- unit enums serialize to case names
- enum rules are inferred during validation

## Lists and Collections

Struct ships three first-class collection options.

### `DataList`

`DataList` is a strict, sequential, immutable list type.

Use it when you want:

- sequential keys only
- transport-safe list semantics
- predictable serialization
- explicit item typing

```php
<?php

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsDataList;
use Cline\Struct\Enums\DataListType;
use Cline\Struct\Support\DataList;

final readonly class UsernamesData extends AbstractData
{
    public function __construct(
        #[AsDataList(DataListType::String)]
        public DataList $usernames,
    ) {}
}
```

Supported descriptors for `AsDataList(...)`:

- `DataListType::*`
- primitive strings like `'int'` or `'string'`
- Struct data class strings
- enum class strings
- cast classes implementing `CastInterface`

Examples:

```php
#[AsDataList(DataListType::Bool)]
public DataList $flags;

#[AsDataList(IntegerStringCast::class)]
public DataList $numbers;
```

### `DataCollection`

`DataCollection` is Struct's immutable keyed collection wrapper.

Use it when you want:

- Struct-owned immutable collection semantics
- predictable keyed serialization without Laravel collection mutability
- transport-oriented wrappers that stay detached from Laravel collection APIs

```php
<?php

use App\Data\PostData;
use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsDataCollection;
use Cline\Struct\Support\DataCollection;

final readonly class UserData extends AbstractData
{
    public function __construct(
        #[AsDataCollection(PostData::class)]
        public DataCollection $posts,
    ) {}
}
```

### `LazyDataList` and `LazyDataCollection`

Use the lazy wrappers when you want Struct-owned collection containers that
defer item hydration and source traversal until the property is iterated or
materialized.

```php
<?php

use App\Data\PostData;
use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsLazyDataCollection;
use Cline\Struct\Attributes\AsLazyDataList;
use Cline\Struct\Enums\DataListType;
use Cline\Struct\Support\LazyDataCollection;
use Cline\Struct\Support\LazyDataList;

final readonly class FeedData extends AbstractData
{
    public function __construct(
        #[AsLazyDataList(DataListType::Int)]
        public LazyDataList $scores,
        #[AsLazyDataCollection(PostData::class)]
        public LazyDataCollection $posts,
    ) {}
}
```

Use them when you want:

- deferred source consumption
- deferred typed item hydration
- Struct-owned immutable wrappers without Laravel collection mutability

In v1, lazy Struct collection wrappers are transport-focused:

- `first()` only consumes enough items to resolve the first value
- `all()`, `count()`, `toArray()`, and `jsonSerialize()` materialize the
  remaining source once and cache the result
- `Attributes\\Collections\\*` transforms are intentionally rejected on
  `LazyDataList` and `LazyDataCollection`

### `Collection`

`Collection` support is a separate first-class feature for applications that
want real Laravel collections on DTO properties without routing through
`DataCollection`.

Use it when you want:

- downstream Laravel collection APIs after hydration
- first-class typed item hydration for `Collection` properties
- the shared `Attributes\Collections\*` transforms on real collections

```php
<?php

use App\Data\PostData;
use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\AsCollection;
use Illuminate\Support\Collection;

final readonly class UserData extends AbstractData
{
    public function __construct(
        #[AsCollection(PostData::class)]
        public Collection $posts,
    ) {}
}
```

Supported descriptors for `AsCollection(...)`:

- `DataListType::*`
- primitive strings like `'int'` or `'string'`
- Struct data class strings
- enum class strings
- cast classes implementing `CastInterface`

### Choosing Between Them

Use:

- `DataList` for strict schema boundaries and predictable list transport
- `DataCollection` for immutable Struct-owned keyed collections
- `LazyDataList` and `LazyDataCollection` for deferred Struct-owned
  transport wrappers
- `Collection` for first-class Laravel collection workflows on DTO properties

## Collecting Many Records

Use `collect()` to preserve the incoming container shape.

Supported inputs:

- arrays
- `Illuminate\Support\Collection`
- `Illuminate\Database\Eloquent\Collection`
- `LengthAwarePaginator`
- `CursorPaginator`

Examples:

```php
$songs = SongData::collect([
    ['title' => 'Never Gonna Give You Up', 'artist' => 'Rick Astley'],
    ['title' => 'Giving Up on Love', 'artist' => 'Rick Astley'],
]);

$songs = SongData::collect(Song::all());
$songs = SongData::collect(Song::paginate());
$songs = SongData::collect(Song::cursorPaginate());
```

Use `collectInto()` when you want a specific target container:

```php
use Illuminate\Support\Collection;

$songs = SongData::collectInto($input, Collection::class);
```

Use:

- `collect()` when preserving source shape matters
- `collectInto()` when the consumer expects a specific container

## Serialization

Struct data objects implement:

- `Arrayable`
- `Castable`
- `Jsonable`
- `JsonSerializable`
- `Stringable`

### Basic Output

```php
$data->toArray();
$data->toJson();
(string) $data;
```

### Sensitive Properties

If a promoted constructor property uses PHP's `SensitiveParameter`, Struct
excludes it from:

- `toArray()`
- `toJson()`
- `jsonSerialize()`
- `__toString()`

### Stringifiers

Use `#[StringifyUsing(...)]` to control `(string) $data`.

Built-in stringifiers:

- `JsonStringifier`
- `XmlStringifier`
- `YamlStringifier`

### Output Mapping

All output methods respect:

- output name mapping
- lazy includes
- lazy groups
- conditional serialization rules
- nested include paths
- sensitive-property omission

## Lazy Serialization

Lazy properties are an output concern, not an input-binding concern.

Use them for:

- omitted-by-default output
- explicit includes
- group-based output
- conditional output
- lazy value resolution

### `#[Lazy]`

```php
<?php

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\Lazy;

final readonly class UserData extends AbstractData
{
    public function __construct(
        public int $id,
        public string $name,
        #[Lazy()]
        public array $analytics = [],
    ) {}
}
```

```php
$user->toArray();
$user->toArray(include: ['analytics']);
```

### `#[LazyGroup(...)]`

```php
#[LazyGroup('details')]
public string $bio = '';
```

```php
$user->toArray(groups: ['details']);
```

### Conditional Inclusion

Struct supports:

- `#[IncludeWhen(...)]`
- `#[ExcludeWhen(...)]`

These use serialization context:

```php
$user->toArray(context: ['is_admin' => true]);
```

### Fluent Serializer

Use `serializer()` for an immutable fluent API:

```php
$user->serializer()
    ->include('analytics', 'posts.author.profile')
    ->groups('details')
    ->exclude('posts.author.email')
    ->context(['is_admin' => true])
    ->toArray();
```

The serializer also supports `toJson()`.

## Computed Properties

Use `#[Computed]` for derived values.

```php
<?php

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\Computed;

final readonly class UserData extends AbstractData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        #[Computed]
        public string $displayName = '',
    ) {}
}
```

Or point it at a computer class:

```php
#[Computed(DisplayNameComputer::class)]
public string $displayName = '';
```

Computed properties can also be lazy, so they are only derived when included
in serialization output.

## Eloquent Integration

Struct supports single data-object casts and collection data-object casts.

### Single Data-Object Cast

```php
<?php

use App\Data\AddressData;
use Illuminate\Database\Eloquent\Model;

final class User extends Model
{
    protected function casts(): array
    {
        return [
            'address' => AddressData::class,
        ];
    }
}
```

### Collection Data-Object Cast

```php
<?php

use App\Data\PostData;
use Cline\Struct\Eloquent\AsDataCollection;
use Illuminate\Database\Eloquent\Model;

final class User extends Model
{
    protected function casts(): array
    {
        return [
            'posts' => AsDataCollection::of(PostData::class),
        ];
    }
}
```

Or from the data object itself:

```php
PostData::castAsCollection();
```

## Factories

Use `#[UseFactory(...)]` to attach a factory.

```php
<?php

use App\Factories\UserDataFactory;
use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\UseFactory;

#[UseFactory(UserDataFactory::class)]
final readonly class UserData extends AbstractData
{
    public function __construct(
        public string $name,
    ) {}
}
```

Factory helpers include:

- `make()`
- `makeOne()`
- `create()`
- `createOne()`
- `count(...)`
- `times(...)`
- `state(...)`
- named states
- `sequence(...)`
- `raw()`
- `lazy()`
- `afterMaking(...)`
- `afterCreating(...)`

## Request and Model Payload Resolvers

Struct ships Laravel-oriented helpers for requests and model-like sources, but
keeps transport and persistence shaping outside the property system.

### Global Resolver Defaults

Configured in [`config/struct.php`](./config/struct.php):

```php
'payload_resolvers' => [
    'request' => DefaultRequestPayloadResolver::class,
    'model' => DefaultModelPayloadResolver::class,
],
```

### Per-Data-Object Resolver Overrides

```php
<?php

use App\Resolvers\UserModelPayloadResolver;
use App\Resolvers\UserRequestPayloadResolver;
use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\UseModelPayloadResolver;
use Cline\Struct\Attributes\UseRequestPayloadResolver;

#[UseRequestPayloadResolver(UserRequestPayloadResolver::class)]
#[UseModelPayloadResolver(UserModelPayloadResolver::class)]
final readonly class UserData extends AbstractData
{
    public function __construct(
        public string $name,
    ) {}
}
```

Resolver precedence:

1. data-object-specific attribute resolver
2. globally bound resolver interface
3. Struct's configured default resolver

Use resolvers when you need:

- custom request payload extraction
- model relation flattening
- authenticated-user-aware request shaping
- app-specific transport or persistence defaults

## Livewire

Struct supports Livewire in two ways.

### `AbstractWireableData`

If you want the data object itself to implement Livewire's `Wireable`, extend
`AbstractWireableData`:

```php
<?php

use Cline\Struct\AbstractWireableData;

final readonly class AddressData extends AbstractWireableData
{
    public function __construct(
        public string $street,
        public string $city,
    ) {}
}
```

### Synth Support

Struct also registers a Livewire property synthesizer so Struct data objects can
dehydrate and hydrate more naturally as Livewire properties.

Use Livewire 4 when relying on this integration.

## Strictness and Policies

Struct lets you control how strict payload handling should be.

Global config:

```php
'undefined_values' => UndefinedValues::Allow,
'superfluous_keys' => SuperfluousKeys::Allow,
```

Per-class or per-property overrides:

- `#[AllowUndefinedValues]`
- `#[ForbidUndefinedValues]`
- `#[AllowSuperfluousKeys]`
- `#[ForbidSuperfluousKeys]`

These control how Struct handles unexpected and extraneous input.

## Configuration

Struct's published config exposes:

- `replace_empty_strings_with_null`
- `undefined_values`
- `superfluous_keys`
- `validation.infer_rules`
- `payload_resolvers.request`
- `payload_resolvers.model`

Policy-style config uses enums rather than raw strings.

## Recursion Protection

Struct detects recursive structures during:

- hydration
- array serialization
- JSON serialization
- string serialization

Instead of looping forever, Struct throws.

## Extension Points

Struct is intentionally extensible through explicit contracts. Consumers can
implement:

- `CastInterface`
  - custom property or list item casts
- `StringifierInterface`
  - custom string serialization
- `ValidatorMutatorInterface`
  - custom validator configuration
- `RequestPayloadResolverInterface`
  - custom request extraction
- `ModelPayloadResolverInterface`
  - custom model-like extraction
- `ComputesValueInterface`
  - custom computed values
- `ResolvesLazyValueInterface`
  - custom lazy value resolution
- `SerializationConditionInterface`
  - custom include/exclude rules

Use these contracts for app- or package-specific behavior rather than pushing
framework or transport concerns into your property definitions.

## Recommended Conventions

Struct works best when consumers follow a few conventions:

- use `create()` for trusted internal payloads
- use `createWithValidation()` at application boundaries
- keep request/model shaping in resolvers
- keep validation customization in validator mutators
- use `DataList` for strict transport lists
- use `DataCollection` for richer collection workflows
- use lazy properties for output shaping, not input binding

## Related Files

- project overview: [README.md](./README.md)
- package config: [config/struct.php](./config/struct.php)
- tests as executable examples: [tests](./tests)
