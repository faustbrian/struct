<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct;

use Cline\Struct\Commands\CacheStructuresCommand;
use Cline\Struct\Commands\ClearStructuresCommand;
use Cline\Struct\Contracts\ModelPayloadResolverInterface;
use Cline\Struct\Contracts\RequestPayloadResolverInterface;
use Cline\Struct\Livewire\DataSynth;
use Cline\Struct\Metadata\MetadataFactory;
use Cline\Struct\Resolvers\DefaultModelPayloadResolver;
use Cline\Struct\Resolvers\DefaultRequestPayloadResolver;
use Cline\Struct\Serialization\SerializationDefaults;
use Cline\Struct\Serialization\SerializationOptions;
use Cline\Struct\Support\DateFormat;
use Cline\Struct\Support\StringifierResolver;
use Cline\Struct\Validation\ValidationFactory;
use Livewire\Livewire;
use Override;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

use const DATE_ATOM;

use function array_filter;
use function array_values;
use function class_exists;
use function config;
use function is_array;
use function is_string;
use function resolve;

/**
 * Registers Struct services, configuration, and optional Livewire integration.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class StructServiceProvider extends PackageServiceProvider
{
    /**
     * Declare the package name and published config file.
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('struct')
            ->hasConfigFile()
            ->hasCommands([
                CacheStructuresCommand::class,
                ClearStructuresCommand::class,
            ]);
    }

    /**
     * Register the metadata factory and payload resolver bindings.
     */
    #[Override()]
    public function registeringPackage(): void
    {
        /** @var class-string<RequestPayloadResolverInterface> $requestPayloadResolver */
        $requestPayloadResolver = config('struct.payload_resolvers.request', DefaultRequestPayloadResolver::class);

        /** @var class-string<ModelPayloadResolverInterface> $modelPayloadResolver */
        $modelPayloadResolver = config('struct.payload_resolvers.model', DefaultModelPayloadResolver::class);

        $this->app->singleton(MetadataFactory::class);
        $this->app->singleton(StringifierResolver::class);
        $this->app->singleton(ValidationFactory::class);
        $this->app->singleton(DateFormat::class, function (): DateFormat {
            $formats = config('struct.date_format', DATE_ATOM);

            if (is_array($formats)) {
                $formats = array_values(array_filter(
                    $formats,
                    static fn (mixed $format): bool => is_string($format) && $format !== '',
                ));
            } else {
                $formats = is_string($formats) && $formats !== '' ? [$formats] : [DATE_ATOM];
            }

            $timezone = config('struct.date_timezone');

            return DateFormat::withConfig(
                $formats[0] ?? DATE_ATOM,
                is_string($timezone) && $timezone !== '' ? $timezone : null,
                $formats,
            );
        });
        $this->app->singleton(
            SerializationOptions::class,
            fn (): SerializationOptions => new SerializationOptions(date: resolve(DateFormat::class)),
        );
        $this->app->singleton(
            SerializationDefaults::class,
            fn (): SerializationDefaults => new SerializationDefaults(
                options: resolve(SerializationOptions::class),
                metadataFactory: resolve(MetadataFactory::class),
            ),
        );
        $this->app->singleton(
            RequestPayloadResolverInterface::class,
            $requestPayloadResolver,
        );
        $this->app->singleton(
            ModelPayloadResolverInterface::class,
            $modelPayloadResolver,
        );
    }

    /**
     * Register the Livewire synthesizer when Livewire is available.
     */
    #[Override()]
    public function packageBooted(): void
    {
        if (!class_exists(Livewire::class) || !$this->app->bound('livewire')) {
            return;
        }

        /** @phpstan-ignore-next-line */
        Livewire::propertySynthesizer(DataSynth::class);
    }
}
