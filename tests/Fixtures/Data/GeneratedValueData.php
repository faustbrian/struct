<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Fixtures\Data;

use Cline\Struct\AbstractData;
use Cline\Struct\Attributes\Strings\Password;
use Cline\Struct\Attributes\Strings\Random;
use Cline\Struct\Attributes\Strings\Ulid;
use Cline\Struct\Attributes\Strings\Uuid;
use Cline\Struct\Attributes\Validate;

/**
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class GeneratedValueData extends AbstractData
{
    public function __construct(
        #[Validate('uuid')]
        #[Uuid(version: 1)]
        public string $uuidV1,
        #[Validate('uuid')]
        #[Uuid(version: 2, localDomain: 0, localIdentifier: 42, node: '0x123456789abc', clockSeq: 21, lowerCase: true)]
        public string $uuidV2,
        #[Validate('uuid')]
        #[Uuid(version: 3, namespace: 'dns', name: 'example.com')]
        public string $uuidV3,
        #[Validate('uuid')]
        #[Uuid(version: 4)]
        public string $uuidV4,
        #[Validate('uuid')]
        #[Uuid(version: 5, namespace: 'url', name: 'https://cline.sh')]
        public string $uuidV5,
        #[Validate('uuid')]
        #[Uuid(version: 6)]
        public string $uuidV6,
        #[Validate('uuid')]
        #[Uuid(version: 7)]
        public string $uuidV7,
        #[Validate('ulid')]
        #[Ulid(lowerCase: true)]
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
