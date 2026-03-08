<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Exceptions;

use RuntimeException;

/**
 * Base exception type for all Struct-specific runtime errors.
 *
 * @author Brian Faust <brian@cline.sh>
 */
abstract class AbstractStructException extends RuntimeException implements StructException {}
