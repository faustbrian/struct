<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Cline\CodingStandard\Rector\Factory;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodParameterRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\CodingStyle\Rector\ClassLike\NewlineBetweenClassLikeStmtsRector;
use Rector\CodeQuality\Rector\BooleanOr\RepeatedOrEqualToInArrayRector;
use Rector\Php70\Rector\StmtsAwareInterface\IfIssetToCoalescingRector;
use Rector\Php80\Rector\NotIdentical\StrContainsRector;
use RectorLaravel\Rector\MethodCall\ContainerBindConcreteWithClosureOnlyRector;
use Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayAnyRector;
use Rector\Php84\Rector\Foreach_\ForeachToArrayAllRector;
use Rector\TypeDeclaration\Rector\ArrowFunction\AddArrowFunctionReturnTypeRector;
return Factory::create(
    paths: [__DIR__.'/src', __DIR__.'/tests'],
    skip: [
        RemoveUnreachableStatementRector::class => [__DIR__.'/tests'],
        ContainerBindConcreteWithClosureOnlyRector::class,
        NewlineBetweenClassLikeStmtsRector::class,
        ForeachToArrayAnyRector::class,
        ForeachToArrayAllRector::class => [__DIR__.'/src/Support/DataCollection.php'],
        StrContainsRector::class => [__DIR__.'/src/Serialization/SerializationOptions.php'],
        IfIssetToCoalescingRector::class => [
            __DIR__.'/src/Serialization/SerializationContext.php',
            __DIR__.'/src/Support/CreationContext.php',
        ],
        RepeatedOrEqualToInArrayRector::class => [__DIR__.'/src/Metadata/MetadataFactory.php'],
        RemoveDeadInstanceOfRector::class => [__DIR__.'/src/Support/CreationContext.php'],
        RemoveUnusedPrivatePropertyRector::class => [__DIR__.'/src/Serialization/SerializationOptions.php'],
        RemoveUnusedPublicMethodParameterRector::class => [__DIR__.'/src/Serialization/SerializationContext.php'],
        AddArrowFunctionReturnTypeRector::class => [__DIR__.'/src/Support/DataCollection.php'],
    ],
);
