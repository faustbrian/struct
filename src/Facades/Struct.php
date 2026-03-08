<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Facades;

use Cline\Struct\Metadata\ClassMetadata;
use Cline\Struct\Metadata\MetadataFactory;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ClassMetadata for(string $class)
 * @author Brian Faust <brian@cline.sh>
 */
final class Struct extends Facade
{
    /**
     * @return class-string
     */
    protected static function getFacadeAccessor(): string
    {
        return MetadataFactory::class;
    }
}
