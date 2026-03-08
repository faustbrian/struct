<?php declare(strict_types=1);

use Cline\Bench\Configuration\BenchConfig;

return BenchConfig::default()
    ->withBenchmarkPath('benchmarks-cline')
    ->withBootstrapPath('vendor/autoload.php')
    ->withProcessIsolation(true)
    ->withPreferredCompetitors(['struct', 'bag', 'spatie'])
    ->withDefaultRegression(metric: 'median', tolerance: '5%');
