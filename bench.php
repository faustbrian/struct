<?php declare(strict_types=1);

use Cline\Bench\Configuration\BenchConfig;
use Cline\Bench\Enums\Metric;

return BenchConfig::default()
    ->withBenchmarkPath('benchmark')
    ->withBootstrapPath('vendor/autoload.php')
    ->withProcessIsolation(true)
    ->withPreferredCompetitors(['struct', 'bag', 'spatie'])
    ->withDefaultRegression(metric: Metric::Median, tolerance: '5%');
