# Benchmarking

Struct benchmarks run through `cline/bench`.

The benchmark suite lives in `benchmark/`.

```bash
composer bench
composer bench:save
composer bench:compare
```

`bench:save` stores the current results as the baseline snapshot, and
`bench:compare` compares the latest run against that saved snapshot.
