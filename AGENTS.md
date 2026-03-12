# Package Maintenance Rules

These rules apply to all package work in this repository. RFC 2119
keywords (MUST, MUST NOT, SHOULD, SHOULD NOT, MAY) are used
intentionally.

## Release And Documentation Hygiene

- You MUST update `CHANGELOG.md` for every implementation task that
  creates, modifies, or deletes files before claiming completion.
- You MUST update `README.md`, examples, and other package
  documentation when public behavior, configuration, installation, or
  usage changes.
- You MUST document breaking changes, removals, and migration steps in
  `CHANGELOG.md` before the work is considered complete.
- You SHOULD prefer a documented deprecation path before removing or
  renaming public APIs.

## Public API And Compatibility

- You MUST treat public PHP APIs, configuration keys, environment
  variables, command signatures, events, and exception contracts as
  SemVer-governed surface area.
- You MUST NOT introduce backward-incompatible behavior without a clear,
  documented architectural reason and explicit release-note coverage.
- You MUST NOT alternate between conflicting style patterns without a
  documented architectural reason.
- You MUST keep commits and feature changes focused. Unrelated refactors
  MUST be split into separate work.

## Testing And Verification

- You MUST add or update automated tests for every bug fix and every
  user-visible behavior change.
- You MUST prefer regression coverage before changing existing behavior.
- You MUST run `make refactor` before pushing.
- You MUST run `make lint` before pushing.
- You MUST run `make test` before pushing.
- You MUST report the exact verification commands you ran when handing
  work off for review.

## Dependency Discipline

- You MUST keep the dependency surface minimal and MUST NOT add,
  upgrade, or remove dependencies without a clear maintenance benefit.
- You MUST verify new dependency constraints against the package's
  supported platform and framework matrix before merging.
- You SHOULD prefer existing project utilities and framework features
  over adding new libraries for small conveniences.

## Runtime Safety

- You MUST NOT introduce mutable runtime static properties for caches,
  helper instances, or request-scoped state.
- Laravel Octane keeps workers alive across requests, so mutable static
  properties can leak data between requests, hold stale container
  dependencies, and produce cross-request bugs that do not appear under
  the normal PHP request lifecycle.
- Prefer per-operation context objects, container singletons, or
  instance-owned caches that are explicitly scoped to the right
  lifetime.

## Struct Performance Rules

- When the user asks for performance improvements or a performance
  review, you MUST start by identifying the hot paths from the current
  implementation before proposing changes.
- You SHOULD prefer benchmark-driven work: add or reuse focused tests,
  run the smallest relevant benchmark loop first, and isolate changes so
  regressions can be attributed to one commit.
- You MUST preserve benchmark-sensitive implementations when tools try to
  rewrite them into slower equivalents. Review formatter and refactor
  output carefully instead of assuming the rewritten code is acceptable.
- You MUST NOT trade correctness or configurability for micro-optimizing
  shortcuts. Safe wins in this package have come from reducing repeated
  work, precomputing immutable metadata, lazily materializing expensive
  state, and adding fast paths for clearly proven common cases.
- You MUST be especially skeptical of collection "fast paths" that add
  a detection pass before the real work or that create fresh per-item
  contexts, options, or helper objects inside a hot loop. In this
  package those patterns have caused real regressions even when they
  looked cheaper on paper.
- When optimizing collection serialization or hydration, you SHOULD
  prefer reusing one shared per-operation context over calling
  item-level APIs that allocate their own contexts or re-enter the full
  top-level pipeline for each item.
- For performance reviews, you SHOULD prioritize real costs over style:
  repeated reflection, repeated container resolution, repeated path
  filtering, avoidable array copies, avoidable object creation, and
  generic recursion in hot serialization and hydration paths.
- You SHOULD call out when a potential optimization is too invasive,
  would expand public API surface, or would require mutable global state.
- After performance changes, you SHOULD run the relevant benchmark
  comparison and report both relative wins and any commands blocked by
  environment issues.
