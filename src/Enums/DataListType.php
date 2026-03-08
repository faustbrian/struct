<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Enums;

/**
 * Describes primitive item types supported by Struct data lists.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum DataListType: string
{
    case Array = 'array';
    case Bool = 'bool';
    case Float = 'float';
    case Int = 'int';
    case Mixed = 'mixed';
    case String = 'string';
}
