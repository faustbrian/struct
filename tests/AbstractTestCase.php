<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests;

use Cline\Struct\Enums\SuperfluousKeys;
use Cline\Struct\Enums\UndefinedValues;
use Cline\Struct\StructServiceProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * @author Brian Faust <brian@cline.sh>
 * @internal
 */
abstract class AbstractTestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            StructServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app->make(Repository::class)->set('database.default', 'sqlite');
        $app->make(Repository::class)->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app->make(Repository::class)->set('struct.replace_empty_strings_with_null', true);
        $app->make(Repository::class)->set('struct.undefined_values', UndefinedValues::Allow);
        $app->make(Repository::class)->set('struct.superfluous_keys', SuperfluousKeys::Allow);
    }
}
