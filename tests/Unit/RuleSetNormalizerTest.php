<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Validation\RuleSetNormalizer;
use Tests\Fixtures\Rules\UppercaseValueRule;

describe('RuleSetNormalizer', function (): void {
    test('preserves normalized rule order for mixed rule definitions', function (): void {
        $rules = new RuleSetNormalizer()->normalize([
            'required|string',
            UppercaseValueRule::class,
            'min:5|alpha',
        ]);

        expect($rules[0])->toBe('required')
            ->and($rules[1])->toBe('string')
            ->and($rules[2])->toBeInstanceOf(UppercaseValueRule::class)
            ->and($rules[3])->toBe('min:5')
            ->and($rules[4])->toBe('alpha');
    });
});
