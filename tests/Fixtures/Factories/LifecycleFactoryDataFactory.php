<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Factories;

use Cline\Struct\Contracts\DataObjectInterface;
use Cline\Struct\Factories\AbstractFactory;
use Override;
use Tests\Fixtures\Data\LifecycleFactoryData;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class LifecycleFactoryDataFactory extends AbstractFactory
{
    #[Override()]
    protected string $model = LifecycleFactoryData::class;

    public function definition(): array
    {
        return [
            'name' => 'Default',
        ];
    }

    #[Override()]
    public function configure(): static
    {
        return $this
            ->afterMaking(static fn (DataObjectInterface $dto): DataObjectInterface => $dto->with(name: $dto->name.'-made'))
            ->afterCreating(static fn (DataObjectInterface $dto): DataObjectInterface => $dto->with(name: $dto->name.'-created'));
    }
}
