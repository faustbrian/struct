# Benchmarking

Struct keeps the existing `phpbench` suite and also ships a `cline/bench`
mirror of the same DTO comparison scenarios.

## `phpbench`

Run the existing benchmark suite:

```bash
composer bench
composer bench:compare
```

## Xdebug Profiling

Use the Docker profiling container when you need traces or profiler output:

```bash
make profile-phpinfo
XDEBUG_MODE=trace docker-compose run --rm profile php vendor/bin/phpbench run benchmarks/DataProfileBench.php
XDEBUG_MODE=profile docker-compose run --rm profile php vendor/bin/phpbench run benchmarks/BagDataProfileBench.php
```

Trace and profiler output is written to `build/xdebug/` on the host so it can
be inspected with tools such as QCacheGrind or KCachegrind.

## `cline/bench`

Run the mirrored `cline/bench` suite:

```bash
composer bench:cline
composer bench:cline:save
composer bench:cline:compare
```

The `cline/bench` suite lives in `benchmarks-cline/` and reuses the same
support graph as the `phpbench` benchmarks. That keeps the benchmark subjects
aligned while comparing the existing runner to the new one.
