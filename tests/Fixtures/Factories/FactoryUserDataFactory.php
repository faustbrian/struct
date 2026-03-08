<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Factories;

use Cline\Struct\Factories\AbstractFactory;
use Override;
use Tests\Fixtures\Data\FactoryUserData;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class FactoryUserDataFactory extends AbstractFactory
{
    #[Override()]
    protected string $model = FactoryUserData::class;

    public function definition(): array
    {
        return [
            'name' => 'Default',
            'verified' => false,
        ];
    }

    public function verified(): static
    {
        return $this->state([
            'verified' => true,
        ]);
    }
}
