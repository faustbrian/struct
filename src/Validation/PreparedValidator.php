<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Struct\Validation;

use Illuminate\Validation\Validator;

/**
 * Stores the configured Laravel validator and optional error bag metadata.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class PreparedValidator
{
    /**
     * @param ?string $errorBag The error bag name to use when redirecting validation errors.
     */
    public function __construct(
        public Validator $validator,
        public ?string $errorBag,
    ) {}
}
