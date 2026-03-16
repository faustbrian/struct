# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Added `PropertyHydrationContext` plus contextual hydration companion
  contracts for casts and attribute-backed string and collection
  transforms, allowing whole-DTO decisions from raw input and already
  resolved sibling values without mutating shared cast instances.
- Added detached `Illuminate\\Support\\LazyCollection` DTO property
  support through `#[AsLazyCollection(...)]`, including lazy-safe
  transform attributes such as `Map`, `Filter`, `Skip`, and `Take`,
  plus `CollectionResults` and `CollectionSources` support for lazy
  sources and targets.

### Changed
- Reduced collection hydration overhead by caching lazy derived-result
  source materializations, reusing hydrated attribute/context state per
  operation, and narrowing post-hydration collection passes to only the
  properties that actually require them.
- Moved the newer value-object and numeric attributes into dedicated
  subnamespaces under `Cline\Struct\Attributes\Money`,
  `Cline\Struct\Attributes\PhoneNumber`,
  `Cline\Struct\Attributes\PostalCode`, and
  `Cline\Struct\Attributes\Numerus`.

### Breaking Changes
- Updated attribute imports are required after the namespace move. For
  example:
  - `Cline\Struct\Attributes\AsMoney` becomes
    `Cline\Struct\Attributes\Money\AsMoney`
  - `Cline\Struct\Attributes\AsPhoneNumber` becomes
    `Cline\Struct\Attributes\PhoneNumber\AsPhoneNumber`
  - `Cline\Struct\Attributes\AsPostalCode` becomes
    `Cline\Struct\Attributes\PostalCode\AsPostalCode`
  - Numeric attributes such as `Round`, `Clamp`, and `Abs` now live
    under `Cline\Struct\Attributes\Numerus\*`

### Added
- Added repository-level maintainer guidance in `AGENTS.md`.
- Added `LazyDataList` and `LazyDataCollection` plus
  `#[AsLazyDataList(...)]` and `#[AsLazyDataCollection(...)]` for
  deferred Struct-owned collection hydration and serialization.
- Added built-in arbitrary-precision number casting for `cline/math`.
- Added built-in `Money`, `RationalMoney`, and `MoneyBag` casting plus the `#[AsMoney(...)]` attribute.
- Added built-in numeric normalization attributes backed by Numerus.
- Added built-in `PhoneNumber` casting plus the
  `#[AsPhoneNumber(...)]` attribute for region-aware scalar phone
  input.
- Added built-in `PostalCode` casting plus the
  `#[AsPostalCode(...)]` attribute for country-aware scalar postal
  input.
- Added built-in `Version` and `Constraint` casting for semantic
  version DTO fields.
- Added built-in string normalization and extraction attributes backed by
  `StringCast`.
- Expanded built-in string attributes to cover deterministic naming,
  prefix/suffix, replacement, padding, masking, and repetition helpers.
- Added missing-only generated-value attributes for `UUID`, `ULID`,
  random strings, and passwords, including validation-aware hydration.
- Added built-in collection transformation attributes for arrays,
  `DataList`, and `DataCollection` under `Attributes\\Collections`.
- Added detached first-class `Illuminate\\Support\\Collection` DTO
  property support through `#[AsCollection(...)]`, including shared
  `Attributes\\Collections` transforms and inferred item validation.
- Added callback-based `Illuminate\\Support\\Collection` attributes for
  `#[AsCollection(...)]` properties, including `Filter`, `Reject`, `Map`,
  `FlatMap`, `Each`, `SortBy`, `GroupBy`, `KeyBy`, and `Partition`.
- Expanded callback-based `Illuminate\\Support\\Collection` attributes
  with `SortByDesc`, `UniqueBy`, `SkipUntil`, `SkipWhile`, `TakeUntil`,
  `TakeWhile`, `MapWithKeys`, `Chunk`, `Sliding`, and `MapInto`.
- Expanded detached `Illuminate\\Support\\Collection` attributes with
  query, shape, and conditional transforms including `Where*`, `Pluck`,
  `Flatten`, `Collapse`, `ChunkWhile`, `MapToGroups`, `SortKeysDesc`,
  `SortKeysUsing`, `UniqueStrict`, `Duplicates*`, `Zip`, `Concat`, and
  `When*` / `Unless*`.
- Added `Attributes\\CollectionResults` for source-based derived values
  such as `Contains`, `Every`, `FirstWhere`, `Count`, `Reduce`,
  `Sum`, `Join`, `Pop`, `Pull`, and `Unwrap`.
- Added `Attributes\\CollectionSources` for generated collection
  properties through `Wrap`, `Range`, and `Times`, and added detached
  collection transforms for `Combine` and `Forget`.
- Expanded detached `Illuminate\\Support\\Collection` support with
  terminal result attributes such as `After`, `All`, `FirstOrFail`,
  `Has*`, `IsEmpty`, `ToJson`, and result `Random`; with transform
  attributes such as `CountBy`, `WhereInstanceOf`, `Dot`, `Undot`,
  `ForPage`, `Select`, `Transform`, `CrossJoin`, `Diff*`, `Intersect*`,
  `Merge*`, `Replace*`, `Union`, collection `Random`, and `Splice`; and
  with processor/source support through `EachSpread`, `MapSpread`,
  `Ensure`, `Tap`, `Pipe*`, and `FromJson`.
- Moved built-in string transforms and generators under
  `Attributes\\Strings` with backward-compatible aliases at
  `Attributes`.
- Added built-in `Numerus` property casting when `cline/numerus` is installed.
- Initial release
