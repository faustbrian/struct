<?php declare(strict_types=1);

$root = dirname(__DIR__);
$dumpFile = tempnam(sys_get_temp_dir(), 'struct-bench-');

if ($dumpFile === false) {
    fwrite(STDERR, "Unable to create a temporary dump file.\n");

    exit(1);
}

$arguments = array_slice($argv, 1);
$escapedArguments = array_map(static fn (string $argument): string => escapeshellarg($argument), $arguments);
$command = [
    'GIT_CONFIG_COUNT=1',
    'GIT_CONFIG_KEY_0='.escapeshellarg('safe.directory'),
    'GIT_CONFIG_VALUE_0='.escapeshellarg($root),
    escapeshellarg(PHP_BINARY),
    escapeshellarg($root.'/vendor/bin/phpbench'),
    'run',
    'benchmarks',
    '--dump-file='.escapeshellarg($dumpFile),
    ...$escapedArguments,
];

$exitCode = 0;

echo 'Running benchmarks and building comparison report...'.PHP_EOL.PHP_EOL;

passthru(implode(' ', $command), $exitCode);

if ($exitCode !== 0) {
    @unlink($dumpFile);

    exit($exitCode);
}

$xml = simplexml_load_file($dumpFile);

@unlink($dumpFile);

if (!$xml instanceof SimpleXMLElement) {
    fwrite(STDERR, "Unable to parse phpbench XML output.\n");

    exit(1);
}

/**
 * @return array<string, float>
 */
function benchmarkTimes(SimpleXMLElement $xml, string $class): array
{
    $times = [];

    foreach ($xml->suite->benchmark as $benchmark) {
        if ((string) $benchmark['class'] !== $class) {
            continue;
        }

        foreach ($benchmark->subject as $subject) {
            $stats = $subject->variant->stats;

            if (!$stats instanceof SimpleXMLElement) {
                continue;
            }

            $times[(string) $subject['name']] = (float) $stats['mode'];
        }
    }

    return $times;
}

/**
 * @param array<string, array<string, float>> $competitorResults
 * @return list<array{label: string, values: array<string, float>}>
 */
function comparisonRows(array $competitorResults): array
{
    $labels = [
        'benchProfileCollectionTransformation' => 'Profile Collection Transform',
        'benchProfileObjectTransformation' => 'Profile Object Transform',
        'benchProfileCollectionCreation' => 'Profile Collection Create',
        'benchProfileObjectCreation' => 'Profile Object Create',
        'benchCollectionTransformation' => 'Collection Transform',
        'benchObjectTransformation' => 'Object Transform',
        'benchCollectionCreation' => 'Collection Create',
        'benchObjectCreation' => 'Object Create',
        'benchCollectionTransformationWithoutCache' => 'Collection Transform No Cache',
        'benchObjectTransformationWithoutCache' => 'Object Transform No Cache',
        'benchCollectionCreationWithoutCache' => 'Collection Create No Cache',
        'benchObjectCreationWithoutCache' => 'Object Create No Cache',
    ];

    $rows = [];

    foreach ($labels as $subject => $label) {
        $values = [];

        foreach ($competitorResults as $competitor => $times) {
            if (!isset($times[$subject])) {
                continue 2;
            }

            $values[$competitor] = $times[$subject];
        }

        $rows[] = [
            'label' => $label,
            'values' => $values,
        ];
    }

    return $rows;
}

function formatMicros(float $micros): string
{
    if ($micros >= 1000) {
        return number_format($micros / 1000, 3).'ms';
    }

    return number_format($micros, 3).'μs';
}

function formatOps(float $micros): string
{
    if ($micros <= 0.0) {
        return 'n/a';
    }

    return number_format(1_000_000 / $micros, 0).'/s';
}

function displayWidth(string $value): int
{
    return mb_strwidth($value, 'UTF-8');
}

function padCell(string $value, int $width, bool $leftAlign = true): string
{
    $padding = max(0, $width - displayWidth($value));

    return $leftAlign
        ? $value.str_repeat(' ', $padding)
        : str_repeat(' ', $padding).$value;
}

$competitors = [
    'Struct' => ['\Benchmarks\DataProfileBench', '\Benchmarks\DataBench'],
    'Spatie' => ['\Benchmarks\SpatieDataProfileBench', '\Benchmarks\SpatieDataBench'],
    'Bag' => ['\Benchmarks\BagDataProfileBench', '\Benchmarks\BagDataBench'],
];

$competitorResults = [];

foreach ($competitors as $competitor => $classes) {
    $competitorResults[$competitor] = [];

    foreach ($classes as $class) {
        $competitorResults[$competitor] = [
            ...$competitorResults[$competitor],
            ...benchmarkTimes($xml, $class),
        ];
    }
}

$rows = comparisonRows($competitorResults);
$headers = ['Scenario'];
$alignments = [true];
$widths = [displayWidth('Scenario')];

foreach (array_keys($competitors) as $competitor) {
    $headers[] = $competitor;
    $alignments[] = false;
    $widths[] = displayWidth($competitor);
}

$headers[] = 'Winner';
$headers[] = 'Ratio';
$headers[] = '% Faster';
$alignments[] = true;
$alignments[] = false;
$alignments[] = false;
$widths[] = displayWidth('Winner');
$widths[] = displayWidth('Ratio');
$widths[] = displayWidth('% Faster');

foreach (array_keys($competitors) as $competitor) {
    $header = $competitor.' Ops/s';
    $headers[] = $header;
    $alignments[] = false;
    $widths[] = displayWidth($header);
}

$table = [];
$wins = array_fill_keys(array_keys($competitors), 0);
$ratioProduct = 1.0;

foreach ($rows as $row) {
    $values = $row['values'];
    $fastest = min($values);
    $slowest = max($values);
    $winner = array_search($fastest, $values, true);
    $ratio = $slowest / $fastest;
    $percentFaster = (($slowest - $fastest) / $slowest) * 100;

    if (is_string($winner)) {
        ++$wins[$winner];
    }

    $ratioProduct *= $ratio;

    $tableRow = [$row['label']];

    foreach (array_keys($competitors) as $competitor) {
        $tableRow[] = formatMicros($values[$competitor]);
    }

    $tableRow[] = is_string($winner) ? $winner : 'n/a';
    $tableRow[] = number_format($ratio, 2).'x';
    $tableRow[] = number_format($percentFaster, 1).'%';

    foreach (array_keys($competitors) as $competitor) {
        $tableRow[] = formatOps($values[$competitor]);
    }

    $table[] = $tableRow;

    foreach ($tableRow as $index => $value) {
        $widths[$index] = max($widths[$index], displayWidth($value));
    }
}

$divider = implode('  ', array_map(static fn (int $width): string => str_repeat('-', $width), $widths));
$headerRow = [];

foreach ($headers as $index => $header) {
    $headerRow[] = padCell($header, $widths[$index], $alignments[$index]);
}

echo PHP_EOL.'Comparison'.PHP_EOL;
echo implode('  ', $headerRow).PHP_EOL;
echo $divider.PHP_EOL;

foreach ($table as $row) {
    $formatted = [];

    foreach ($row as $index => $value) {
        $formatted[] = padCell($value, $widths[$index], $alignments[$index]);
    }

    echo implode('  ', $formatted).PHP_EOL;
}

$overallRatio = $table === []
    ? 1.0
    : $ratioProduct ** (1 / count($table));

echo PHP_EOL.'Overall'.PHP_EOL;

foreach ($wins as $competitor => $count) {
    echo sprintf('%s wins %d/%d scenarios.', $competitor, $count, count($table)).PHP_EOL;
}

echo sprintf('Geometric mean spread: %s.', number_format($overallRatio, 2).'x').PHP_EOL;
