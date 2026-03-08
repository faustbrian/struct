<?php declare(strict_types=1);

use Cline\Struct\Resolvers\DefaultModelPayloadResolver;
use Cline\Struct\Resolvers\DefaultRequestPayloadResolver;
use Cline\Struct\Enums\SuperfluousKeys;
use Cline\Struct\Enums\UndefinedValues;

return [
    /*
    |--------------------------------------------------------------------------
    | Date Format
    |--------------------------------------------------------------------------
    |
    | Struct uses this format when hydrating and serializing date values.
    | When an array is provided, incoming values are parsed using the first
    | format that matches and outgoing values are serialized using the first
    | configured format.
    |
    */

    'date_format' => DATE_ATOM,

    /*
    |--------------------------------------------------------------------------
    | Date Timezone
    |--------------------------------------------------------------------------
    |
    | When hydrating or serializing dates, Struct will use this timezone to
    | parse naive timestamps and to convert date output before formatting. Set
    | this to null to leave incoming and outgoing dates in their original
    | timezone context.
    |
    */

    'date_timezone' => null,

    /*
    |--------------------------------------------------------------------------
    | Structure Caching
    |--------------------------------------------------------------------------
    |
    | Struct can persist reflected metadata to Laravel's cache so DTO
    | structure analysis does not need to run on every cold request.
    | Discovery can scan configured directories to warm cache entries
    | ahead of runtime via the `struct:cache` command.
    |
    */

    'structure_caching' => [
        'enabled' => false,
        'directories' => [],
        'cache' => [
            'store' => null,
            'prefix' => 'struct',
            'duration' => null,
        ],
        'reflection_discovery' => [
            'enabled' => false,
            'base_path' => base_path(),
            'root_namespace' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Replace Empty Strings With Null
    |--------------------------------------------------------------------------
    |
    | When enabled, empty string values encountered during normalization are
    | converted to null before validation and hydration. This helps align
    | incoming payloads with nullable PHP values and reduces manual cleanup
    | when empty strings should be treated as missing data.
    |
    */

    'replace_empty_strings_with_null' => true,

    /*
    |--------------------------------------------------------------------------
    | Undefined Values
    |--------------------------------------------------------------------------
    |
    | This option controls how Struct should handle values that are not defined
    | by the target schema or data contract. Use the enum value that matches
    | the strictness you want when unexpected input is encountered.
    |
    */

    'undefined_values' => UndefinedValues::Allow,

    /*
    |--------------------------------------------------------------------------
    | Superfluous Keys
    |--------------------------------------------------------------------------
    |
    | This option determines how Struct handles keys that are not required by
    | the expected input structure. Adjust this behavior to either allow extra
    | keys or enforce a stricter payload shape during processing.
    |
    */

    'superfluous_keys' => SuperfluousKeys::Allow,

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    |
    | These options configure how Struct builds and applies validation rules.
    | Use this section to tune rule inference and other validation-related
    | behavior for your application.
    |
    */

    'validation' => [
        /*
        |--------------------------------------------------------------------------
        | Infer Rules
        |--------------------------------------------------------------------------
        |
        | When enabled, Struct will infer Laravel validation rules from the
        | defined data objects and property types. Disable this if you want to
        | rely entirely on explicitly declared validation rules.
        |
        */

        'infer_rules' => true,

        /*
        |--------------------------------------------------------------------------
        | Rule Inferrers
        |--------------------------------------------------------------------------
        |
        | These classes contribute inferred validation rules for DTO
        | properties. Override the list to add, remove, or reorder default
        | inference behavior without replacing validation code in the
        | container.
        |
        */

        'rule_inferrers' => [
            Cline\Struct\Validation\SometimesRuleInferrer::class,
            Cline\Struct\Validation\BuiltInTypesRuleInferrer::class,
            Cline\Struct\Validation\AttributesRuleInferrer::class,
            Cline\Struct\Validation\NullableRuleInferrer::class,
            Cline\Struct\Validation\RequiredRuleInferrer::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payload Resolvers
    |--------------------------------------------------------------------------
    |
    | These classes determine how Struct extracts payload arrays from Laravel
    | requests and model-like sources when using createFromRequest() and
    | createFromModel(). Override them if your application needs custom
    | transport or persistence mapping defaults.
    |
    */

    'payload_resolvers' => [
        /*
        |--------------------------------------------------------------------------
        | Request Payload Resolver
        |--------------------------------------------------------------------------
        |
        | This resolver is used by createFromRequest() and
        | createFromRequestWithValidation() unless a DTO-specific resolver
        | attribute overrides it.
        |
        */

        'request' => DefaultRequestPayloadResolver::class,

        /*
        |--------------------------------------------------------------------------
        | Model Payload Resolver
        |--------------------------------------------------------------------------
        |
        | This resolver is used by createFromModel() and
        | createFromModelWithValidation() unless a DTO-specific resolver
        | attribute overrides it.
        |
        */

        'model' => DefaultModelPayloadResolver::class,
    ],
];
