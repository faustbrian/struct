<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Exceptions;

/**
 * Raised when Livewire synthesizer metadata does not contain a valid data object class.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class InvalidLivewireDataClassException extends AbstractLivewireDataSynthException
{
    public static function fromMetadata(): self
    {
        return new self('Livewire: Invalid Struct data class.');
    }
}
