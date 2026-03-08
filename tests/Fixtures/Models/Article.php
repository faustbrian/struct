<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;
use Tests\Fixtures\Data\FactoryUserData;

/**
 * @author Brian Faust <brian@cline.sh>
 */
final class Article extends Model
{
    use HasFactory;

    #[Override()]
    protected $guarded = [];

    #[Override()]
    protected function casts(): array
    {
        return [
            'author' => FactoryUserData::class,
            'contributors' => FactoryUserData::castAsCollection(),
        ];
    }
}
