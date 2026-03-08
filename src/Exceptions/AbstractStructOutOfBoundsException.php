<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Exceptions;

use OutOfBoundsException;

/**
 * Base exception type for Struct out-of-bounds access errors.
 *
 * @author Brian Faust <brian@cline.sh>
 */
abstract class AbstractStructOutOfBoundsException extends OutOfBoundsException implements StructException {}
