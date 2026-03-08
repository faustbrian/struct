<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Enums;

/**
 * Configures whether unknown input keys should be allowed or rejected.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum SuperfluousKeys: string
{
    case Allow = 'allow';
    case Forbid = 'forbid';
}
