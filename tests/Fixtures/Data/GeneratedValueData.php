<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\Password;
use Cline\Struct\Attributes\Random;
use Cline\Struct\Attributes\Ulid;
use Cline\Struct\Attributes\Uuid;
use Cline\Struct\Attributes\Validate;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class GeneratedValueData extends AbstractData
{
    public function __construct(
        #[Uuid(version: 1)]
        #[Validate('uuid')]
        public string $uuidV1,
        #[Uuid(version: 2, localDomain: 0, localIdentifier: 42, node: '0x123456789abc', clockSeq: 21, lowerCase: true)]
        #[Validate('uuid')]
        public string $uuidV2,
        #[Uuid(version: 3, namespace: 'dns', name: 'example.com')]
        #[Validate('uuid')]
        public string $uuidV3,
        #[Uuid(version: 4)]
        #[Validate('uuid')]
        public string $uuidV4,
        #[Uuid(version: 5, namespace: 'url', name: 'https://cline.sh')]
        #[Validate('uuid')]
        public string $uuidV5,
        #[Uuid(version: 6)]
        #[Validate('uuid')]
        public string $uuidV6,
        #[Uuid(version: 7)]
        #[Validate('uuid')]
        public string $uuidV7,
        #[Ulid(lowerCase: true)]
        #[Validate('ulid')]
        public string $ulid,
        #[Random(length: 12, lowerCase: true)]
        public string $random,
        #[Password(length: 12, numbers: false, symbols: false, spaces: false, lowerCase: true)]
        public string $password,
        #[Uuid(version: 4)]
        public string $defaulted = 'php-default',
        public ?string $note = null,
    ) {}
}
