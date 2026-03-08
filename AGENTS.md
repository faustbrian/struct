- You MUST run `make refactor` before pushing.
- You MUST run `make lint` before pushing.
- You MUST run `make test` before pushing.
- You MUST NOT introduce mutable runtime static properties for caches,
  helper instances, or request-scoped state.
- Laravel Octane keeps workers alive across requests, so mutable static
  properties can leak data between requests, hold stale container
  dependencies, and produce cross-request bugs that do not appear under
  the normal PHP request lifecycle.
- Prefer per-operation context objects, container singletons, or
  instance-owned caches that are explicitly scoped to the right
  lifetime.
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
