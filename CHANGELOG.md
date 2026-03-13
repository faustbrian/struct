# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Added repository-level maintainer guidance in `AGENTS.md`.
- Added built-in arbitrary-precision number casting for `cline/math`.
- Added built-in `Money`, `RationalMoney`, and `MoneyBag` casting plus the `#[AsMoney(...)]` attribute.
- Added built-in numeric normalization attributes backed by Numerus.
- Added built-in `PhoneNumber` casting plus the
  `#[AsPhoneNumber(...)]` attribute for region-aware scalar phone
  input.
- Added built-in string normalization and extraction attributes backed by
  `StringCast`.
- Expanded built-in string attributes to cover deterministic naming,
  prefix/suffix, replacement, padding, masking, and repetition helpers.
- Added built-in `Numerus` property casting when `cline/numerus` is installed.
- Initial release
