<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\CodingStandard\PhpCsFixer\Preset;

use Cline\Struct\Attributes\AllowSuperfluousKeys;
use Cline\Struct\Attributes\AllowUndefinedValues;
use Cline\Struct\Attributes\AsCollection;
use Cline\Struct\Attributes\AsDataCollection;
use Cline\Struct\Attributes\AsDataList;
use Cline\Struct\Attributes\AsLazyCollection;
use Cline\Struct\Attributes\AsLazyDataCollection;
use Cline\Struct\Attributes\AsLazyDataList;
use Cline\Struct\Attributes\CastWith;
use Cline\Struct\Attributes\Computed;
use Cline\Struct\Attributes\DoNotReplaceEmptyStringWithNull;
use Cline\Struct\Attributes\Encrypted;
use Cline\Struct\Attributes\ExcludeWhen;
use Cline\Struct\Attributes\ForbidSuperfluousKeys;
use Cline\Struct\Attributes\ForbidUndefinedValues;
use Cline\Struct\Attributes\IncludeWhen;
use Cline\Struct\Attributes\Lazy;
use Cline\Struct\Attributes\LazyGroup;
use Cline\Struct\Attributes\MapInputName;
use Cline\Struct\Attributes\MapInputNameUsing;
use Cline\Struct\Attributes\MapName;
use Cline\Struct\Attributes\MapOutputName;
use Cline\Struct\Attributes\MapOutputNameUsing;
use Cline\Struct\Attributes\ReplaceEmptyStringsWithNull;
use Cline\Struct\Attributes\StringifyUsing;
use Cline\Struct\Attributes\UseFactory;
use Cline\Struct\Attributes\UseModelPayloadResolver;
use Cline\Struct\Attributes\UseRequestPayloadResolver;
use Cline\Struct\Attributes\UseValidator;
use Cline\Struct\Attributes\Validate;
use Cline\Struct\Attributes\ValidateItems;
use Cline\Struct\Attributes\WithInferredValidation;
use Cline\Struct\Attributes\WithoutInferredValidation;
use Override;

/**
 * @version 1.0.2
 */
final class Ordered implements PresetInterface
{
    #[Override()]
    public function name(): string
    {
        return 'Ordered';
    }

    #[Override()]
    public function rules(): array
    {
        return [
            'ordered_attributes' => [
                'order' => [
                    // Validation...
                    Validate::class,
                    ValidateItems::class,
                    WithInferredValidation::class,
                    WithoutInferredValidation::class,
                    UseValidator::class,
                    AllowSuperfluousKeys::class,
                    AllowUndefinedValues::class,
                    ForbidSuperfluousKeys::class,
                    ForbidUndefinedValues::class,
                    DoNotReplaceEmptyStringWithNull::class,
                    ReplaceEmptyStringsWithNull::class,
                    // Behavior...
                    AsCollection::class,
                    AsDataCollection::class,
                    AsDataList::class,
                    AsLazyCollection::class,
                    AsLazyDataCollection::class,
                    AsLazyDataList::class,
                    CastWith::class,
                    Computed::class,
                    Encrypted::class,
                    ExcludeWhen::class,
                    IncludeWhen::class,
                    Lazy::class,
                    LazyGroup::class,
                    MapInputName::class,
                    MapInputNameUsing::class,
                    MapName::class,
                    MapOutputName::class,
                    MapOutputNameUsing::class,
                    StringifyUsing::class,
                    UseFactory::class,
                    UseModelPayloadResolver::class,
                    UseRequestPayloadResolver::class,
                ],
                'sort_algorithm' => 'custom',
            ],
            'ordered_class_elements' => [
                'order' => [
                    'use_trait',
                    'case',
                    'constant_public',
                    'constant_protected',
                    'constant_private',
                    'property_public_static',
                    'property_public',
                    'property_public_readonly',
                    'property_protected_static',
                    'property_protected',
                    'property_protected_readonly',
                    'property_private_static',
                    'property_private',
                    'property_private_readonly',
                    'construct',
                    'destruct',
                    'magic',
                    'phpunit',
                    'method_public_static',
                    'method_public_abstract_static',
                    'method_public',
                    'method_public_abstract',
                    'method_protected_static',
                    'method_protected_abstract_static',
                    'method_protected',
                    'method_protected_abstract',
                    'method_private_static',
                    'method_private_abstract_static',
                    'method_private',
                    'method_private_abstract',
                ],
                'sort_algorithm' => 'none',
            ],
            'ordered_imports' => [
                'imports_order' => [
                    'class',
                    'const',
                    'function',
                ],
                'sort_algorithm' => 'alpha',
            ],
            'ordered_interfaces' => [
                'direction' => 'ascend',
                'order' => 'alpha',
            ],
            'ordered_traits' => false,
        ];
    }

    #[Override()]
    public function targetPhpVersion(): int
    {
        return 80_400;
    }
}
