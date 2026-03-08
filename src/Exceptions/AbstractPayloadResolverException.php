<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Exceptions;

/**
 * Base exception type for invalid payload resolver bindings.
 *
 * @author Brian Faust <brian@cline.sh>
 */
abstract class AbstractPayloadResolverException extends AbstractStructInvalidArgumentException {}
