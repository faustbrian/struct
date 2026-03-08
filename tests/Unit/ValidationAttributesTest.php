<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Struct\Contracts\ProvidesValidationRulesInterface;
use Illuminate\Validation\Rules\AnyOf;
use Illuminate\Validation\Rules\Enum;
use Tests\Fixtures\Enums\UserStatus;
use Tests\Fixtures\ValidationAttributes\Accepted;
use Tests\Fixtures\ValidationAttributes\AcceptedIf;
use Tests\Fixtures\ValidationAttributes\ActiveUrl;
use Tests\Fixtures\ValidationAttributes\After;
use Tests\Fixtures\ValidationAttributes\AfterOrEqual;
use Tests\Fixtures\ValidationAttributes\Alpha;
use Tests\Fixtures\ValidationAttributes\AlphaDash;
use Tests\Fixtures\ValidationAttributes\AlphaNumeric;
use Tests\Fixtures\ValidationAttributes\ArrayType;
use Tests\Fixtures\ValidationAttributes\Ascii;
use Tests\Fixtures\ValidationAttributes\Bail;
use Tests\Fixtures\ValidationAttributes\Before;
use Tests\Fixtures\ValidationAttributes\BeforeOrEqual;
use Tests\Fixtures\ValidationAttributes\Between;
use Tests\Fixtures\ValidationAttributes\Boolean;
use Tests\Fixtures\ValidationAttributes\Confirmed;
use Tests\Fixtures\ValidationAttributes\Contains;
use Tests\Fixtures\ValidationAttributes\CurrentPassword;
use Tests\Fixtures\ValidationAttributes\DateEquals;
use Tests\Fixtures\ValidationAttributes\DateFormat;
use Tests\Fixtures\ValidationAttributes\DateValue;
use Tests\Fixtures\ValidationAttributes\Decimal;
use Tests\Fixtures\ValidationAttributes\Declined;
use Tests\Fixtures\ValidationAttributes\DeclinedIf;
use Tests\Fixtures\ValidationAttributes\Different;
use Tests\Fixtures\ValidationAttributes\Digits;
use Tests\Fixtures\ValidationAttributes\DigitsBetween;
use Tests\Fixtures\ValidationAttributes\Dimensions;
use Tests\Fixtures\ValidationAttributes\Distinct;
use Tests\Fixtures\ValidationAttributes\DoesntContain;
use Tests\Fixtures\ValidationAttributes\DoesntEndWith;
use Tests\Fixtures\ValidationAttributes\DoesntStartWith;
use Tests\Fixtures\ValidationAttributes\Email;
use Tests\Fixtures\ValidationAttributes\Encoding;
use Tests\Fixtures\ValidationAttributes\EndsWith;
use Tests\Fixtures\ValidationAttributes\EnumValue;
use Tests\Fixtures\ValidationAttributes\Exclude;
use Tests\Fixtures\ValidationAttributes\ExcludeIf;
use Tests\Fixtures\ValidationAttributes\ExcludeUnless;
use Tests\Fixtures\ValidationAttributes\ExcludeWith;
use Tests\Fixtures\ValidationAttributes\ExcludeWithout;
use Tests\Fixtures\ValidationAttributes\Exists;
use Tests\Fixtures\ValidationAttributes\Extensions;
use Tests\Fixtures\ValidationAttributes\FileType;
use Tests\Fixtures\ValidationAttributes\Filled;
use Tests\Fixtures\ValidationAttributes\GreaterThan;
use Tests\Fixtures\ValidationAttributes\GreaterThanOrEqual;
use Tests\Fixtures\ValidationAttributes\HexColor;
use Tests\Fixtures\ValidationAttributes\Image;
use Tests\Fixtures\ValidationAttributes\In;
use Tests\Fixtures\ValidationAttributes\InArray;
use Tests\Fixtures\ValidationAttributes\InArrayKeys;
use Tests\Fixtures\ValidationAttributes\Integer;
use Tests\Fixtures\ValidationAttributes\IpAddress;
use Tests\Fixtures\ValidationAttributes\Json;
use Tests\Fixtures\ValidationAttributes\LessThan;
use Tests\Fixtures\ValidationAttributes\LessThanOrEqual;
use Tests\Fixtures\ValidationAttributes\ListRule;
use Tests\Fixtures\ValidationAttributes\Lowercase;
use Tests\Fixtures\ValidationAttributes\MacAddress;
use Tests\Fixtures\ValidationAttributes\Max;
use Tests\Fixtures\ValidationAttributes\MaxDigits;
use Tests\Fixtures\ValidationAttributes\MimeTypeByFileExtension;
use Tests\Fixtures\ValidationAttributes\MimeTypes;
use Tests\Fixtures\ValidationAttributes\Min;
use Tests\Fixtures\ValidationAttributes\MinDigits;
use Tests\Fixtures\ValidationAttributes\Missing;
use Tests\Fixtures\ValidationAttributes\MissingIf;
use Tests\Fixtures\ValidationAttributes\MissingUnless;
use Tests\Fixtures\ValidationAttributes\MissingWith;
use Tests\Fixtures\ValidationAttributes\MissingWithAll;
use Tests\Fixtures\ValidationAttributes\MultipleOf;
use Tests\Fixtures\ValidationAttributes\NotIn;
use Tests\Fixtures\ValidationAttributes\NotRegularExpression;
use Tests\Fixtures\ValidationAttributes\Nullable;
use Tests\Fixtures\ValidationAttributes\Numeric;
use Tests\Fixtures\ValidationAttributes\Present;
use Tests\Fixtures\ValidationAttributes\PresentIf;
use Tests\Fixtures\ValidationAttributes\PresentUnless;
use Tests\Fixtures\ValidationAttributes\PresentWith;
use Tests\Fixtures\ValidationAttributes\PresentWithAll;
use Tests\Fixtures\ValidationAttributes\Prohibited;
use Tests\Fixtures\ValidationAttributes\ProhibitedIf;
use Tests\Fixtures\ValidationAttributes\ProhibitedIfAccepted;
use Tests\Fixtures\ValidationAttributes\ProhibitedIfDeclined;
use Tests\Fixtures\ValidationAttributes\ProhibitedUnless;
use Tests\Fixtures\ValidationAttributes\Prohibits;
use Tests\Fixtures\ValidationAttributes\RegularExpression;
use Tests\Fixtures\ValidationAttributes\Required;
use Tests\Fixtures\ValidationAttributes\RequiredArrayKeys;
use Tests\Fixtures\ValidationAttributes\RequiredIf;
use Tests\Fixtures\ValidationAttributes\RequiredIfAccepted;
use Tests\Fixtures\ValidationAttributes\RequiredIfDeclined;
use Tests\Fixtures\ValidationAttributes\RequiredUnless;
use Tests\Fixtures\ValidationAttributes\RequiredWith;
use Tests\Fixtures\ValidationAttributes\RequiredWithAll;
use Tests\Fixtures\ValidationAttributes\RequiredWithout;
use Tests\Fixtures\ValidationAttributes\RequiredWithoutAll;
use Tests\Fixtures\ValidationAttributes\Same;
use Tests\Fixtures\ValidationAttributes\Size;
use Tests\Fixtures\ValidationAttributes\Sometimes;
use Tests\Fixtures\ValidationAttributes\StartsWith;
use Tests\Fixtures\ValidationAttributes\StringType;
use Tests\Fixtures\ValidationAttributes\Timezone;
use Tests\Fixtures\ValidationAttributes\Ulid;
use Tests\Fixtures\ValidationAttributes\Unique;
use Tests\Fixtures\ValidationAttributes\Uppercase;
use Tests\Fixtures\ValidationAttributes\Url;
use Tests\Fixtures\ValidationAttributes\Uuid;

describe('Validation attributes', function (): void {
    beforeEach(function (): void {
        $this->normalizer = static fn (array $rules): array => normalizeValidationRules($rules);
    });

    describe('Happy Paths', function (): void {
        test('maps all validation attributes to Laravel rule strings', function (string $class, array $arguments, array $expected): void {
            // Arrange
            $attribute = new $class(...$arguments);
            $normalizer = $this->normalizer;

            // Act
            $rules = $normalizer($attribute->rules());

            // Assert
            expect($attribute)->toBeInstanceOf(ProvidesValidationRulesInterface::class)
                ->and($rules)->toBe($expected);
        })->with([
            [Accepted::class, [], ['accepted']],
            [AcceptedIf::class, ['status', 'active'], ['accepted_if:status,active']],
            [Boolean::class, [], ['boolean']],
            [Declined::class, [], ['declined']],
            [DeclinedIf::class, ['status', 'archived'], ['declined_if:status,archived']],

            [ActiveUrl::class, [], ['active_url']],
            [Alpha::class, [], ['alpha']],
            [AlphaDash::class, [], ['alpha_dash']],
            [AlphaNumeric::class, [], ['alpha_num']],
            [Ascii::class, [], ['ascii']],
            [Confirmed::class, [], ['confirmed']],
            [CurrentPassword::class, ['api'], ['current_password:api']],
            [Different::class, ['email'], ['different:email']],
            [DoesntStartWith::class, [['tmp', 'draft']], ['doesnt_start_with:tmp,draft']],
            [DoesntEndWith::class, [['.tmp', '.draft']], ['doesnt_end_with:.tmp,.draft']],
            [Email::class, [['rfc', 'dns']], ['email:rfc,dns']],
            [EndsWith::class, [['.jpg', '.png']], ['ends_with:.jpg,.png']],
            [EnumValue::class, [UserStatus::class], [Enum::class]],
            [HexColor::class, [], ['hex_color']],
            [In::class, [['draft', 'published']], ['in:draft,published']],
            [IpAddress::class, [], ['ip']],
            [Json::class, [], ['json']],
            [Lowercase::class, [], ['lowercase']],
            [MacAddress::class, [], ['mac_address']],
            [Max::class, [255], ['max:255']],
            [Min::class, [3], ['min:3']],
            [NotIn::class, [['archived']], ['not_in:archived']],
            [RegularExpression::class, ['/^[a-z]+$/'], ['regex:/^[a-z]+$/']],
            [NotRegularExpression::class, ['/^[A-Z]+$/'], ['not_regex:/^[A-Z]+$/']],
            [Same::class, ['password'], ['same:password']],
            [Size::class, [8], ['size:8']],
            [StartsWith::class, [['pre', 'draft']], ['starts_with:pre,draft']],
            [StringType::class, [], ['string']],
            [Uppercase::class, [], ['uppercase']],
            [Url::class, [['http', 'https']], ['url:http,https']],
            [Ulid::class, [], ['ulid']],
            [Uuid::class, [], ['uuid']],

            [Between::class, [1, 10], ['between:1,10']],
            [Decimal::class, [2, 4], ['decimal:2,4']],
            [Digits::class, [6], ['digits:6']],
            [DigitsBetween::class, [2, 4], ['digits_between:2,4']],
            [GreaterThan::class, ['minimum'], ['gt:minimum']],
            [GreaterThanOrEqual::class, ['minimum'], ['gte:minimum']],
            [Integer::class, [], ['integer']],
            [LessThan::class, ['maximum'], ['lt:maximum']],
            [LessThanOrEqual::class, ['maximum'], ['lte:maximum']],
            [MaxDigits::class, [12], ['max_digits:12']],
            [MinDigits::class, [3], ['min_digits:3']],
            [MultipleOf::class, [0.5], ['multiple_of:0.5']],
            [Numeric::class, [], ['numeric']],

            [ArrayType::class, [['name', 'email']], ['array:name,email']],
            [Contains::class, [['foo', 'bar']], ['contains:foo,bar']],
            [DoesntContain::class, [['baz']], ['doesnt_contain:baz']],
            [Distinct::class, [true, true], ['distinct:strict,ignore_case']],
            [InArray::class, ['items.*.id'], ['in_array:items.*.id']],
            [InArrayKeys::class, [['name', 'email']], ['in_array_keys:name,email']],
            [ListRule::class, [], ['list']],
            [RequiredArrayKeys::class, [['name', 'email']], ['required_array_keys:name,email']],

            [After::class, ['today'], ['after:today']],
            [AfterOrEqual::class, ['today'], ['after_or_equal:today']],
            [Before::class, ['tomorrow'], ['before:tomorrow']],
            [BeforeOrEqual::class, ['tomorrow'], ['before_or_equal:tomorrow']],
            [DateValue::class, [], ['date']],
            [DateEquals::class, ['2026-03-07'], ['date_equals:2026-03-07']],
            [DateFormat::class, ['Y-m-d'], ['date_format:Y-m-d']],
            [Timezone::class, [['UTC']], ['timezone:UTC']],

            [Dimensions::class, ['width' => 100, 'height' => 200], ['dimensions:width=100,height=200']],
            [Encoding::class, ['UTF-8'], ['encoding:UTF-8']],
            [Extensions::class, [['jpg', 'png']], ['extensions:jpg,png']],
            [FileType::class, [], ['file']],
            [Image::class, [true], ['image:allow_svg']],
            [MimeTypes::class, [['text/plain', 'image/png']], ['mimetypes:text/plain,image/png']],
            [MimeTypeByFileExtension::class, [['jpg', 'png']], ['mimes:jpg,png']],

            [Exists::class, ['users', 'email'], ['exists:users,email']],
            [Unique::class, ['users', 'email'], ['unique:users,email']],

            [Tests\Fixtures\ValidationAttributes\AnyOf::class, [[['string'], ['integer']]], [AnyOf::class]],
            [Bail::class, [], ['bail']],
            [Exclude::class, [], ['exclude']],
            [ExcludeIf::class, ['draft', true], ['exclude_if:draft,true']],
            [ExcludeUnless::class, ['draft', true], ['exclude_unless:draft,true']],
            [ExcludeWith::class, [['name', 'email']], ['exclude_with:name,email']],
            [ExcludeWithout::class, [['name', 'email']], ['exclude_without:name,email']],
            [Filled::class, [], ['filled']],
            [Missing::class, [], ['missing']],
            [MissingIf::class, ['draft', true], ['missing_if:draft,true']],
            [MissingUnless::class, ['draft', true], ['missing_unless:draft,true']],
            [MissingWith::class, [['name', 'email']], ['missing_with:name,email']],
            [MissingWithAll::class, [['name', 'email']], ['missing_with_all:name,email']],
            [Nullable::class, [], ['nullable']],
            [Present::class, [], ['present']],
            [PresentIf::class, ['draft', true], ['present_if:draft,true']],
            [PresentUnless::class, ['draft', true], ['present_unless:draft,true']],
            [PresentWith::class, [['name', 'email']], ['present_with:name,email']],
            [PresentWithAll::class, [['name', 'email']], ['present_with_all:name,email']],
            [Prohibited::class, [], ['prohibited']],
            [ProhibitedIf::class, ['draft', true], ['prohibited_if:draft,true']],
            [ProhibitedIfAccepted::class, ['terms'], ['prohibited_if_accepted:terms']],
            [ProhibitedIfDeclined::class, ['terms'], ['prohibited_if_declined:terms']],
            [ProhibitedUnless::class, ['draft', true], ['prohibited_unless:draft,true']],
            [Prohibits::class, [['name', 'email']], ['prohibits:name,email']],
            [Required::class, [], ['required']],
            [RequiredIf::class, ['draft', true], ['required_if:draft,true']],
            [RequiredIfAccepted::class, ['terms'], ['required_if_accepted:terms']],
            [RequiredIfDeclined::class, ['terms'], ['required_if_declined:terms']],
            [RequiredUnless::class, ['draft', true], ['required_unless:draft,true']],
            [RequiredWith::class, [['name', 'email']], ['required_with:name,email']],
            [RequiredWithAll::class, [['name', 'email']], ['required_with_all:name,email']],
            [RequiredWithout::class, [['name', 'email']], ['required_without:name,email']],
            [RequiredWithoutAll::class, [['name', 'email']], ['required_without_all:name,email']],
            [Sometimes::class, [], ['sometimes']],
        ]);
    });

    describe('Sad Paths', function (): void {});

    describe('Edge Cases', function (): void {});

    describe('Regressions', function (): void {});
});

/**
 * @param  array<int, mixed>  $rules
 * @return array<int, string>
 */
function normalizeValidationRules(array $rules): array
{
    return array_map(static function (mixed $rule): string {
        if (is_string($rule)) {
            return $rule;
        }

        if ($rule instanceof Enum) {
            return $rule::class;
        }

        if ($rule instanceof Stringable) {
            return (string) $rule;
        }

        return $rule::class;
    }, $rules);
}
