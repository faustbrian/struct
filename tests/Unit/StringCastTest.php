<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tests\Fixtures\Data\StringAttributeData;

describe('Built-in string casts', function (): void {
    test('hydrates and serializes string transformation attributes', function (): void {
        $data = StringAttributeData::create([
            'headline' => "  hello\t\tworld  ",
            'slug' => '  Laravel Strings Are Fun!  ',
            'summary' => '  This summary is much longer than expected.  ',
            'after' => 'prefix:value',
            'afterLast' => 'one/two/three',
            'before' => 'value:suffix',
            'beforeLast' => 'one/two/three',
            'between' => 'prefix[value]suffix',
            'betweenFirst' => '[first] middle [second]',
            'snippet' => 'AbCdEfG',
            'title' => 'the quick brown fox',
            'shout' => 'quiet please',
            'ascii' => 'Über Café',
            'transliterated' => 'ⓣⓔⓢⓣ',
            'words' => 'one two three four five',
            'nullable' => null,
            'count' => 42,
        ]);

        expect($data->headline)->toBe('Hello World')
            ->and($data->slug)->toBe('laravel-strings-are-fun')
            ->and($data->summary)->toBe('This summary...')
            ->and($data->after)->toBe('value')
            ->and($data->afterLast)->toBe('three')
            ->and($data->before)->toBe('value')
            ->and($data->beforeLast)->toBe('one/two')
            ->and($data->between)->toBe('value')
            ->and($data->betweenFirst)->toBe('first')
            ->and($data->snippet)->toBe('abcde')
            ->and($data->title)->toBe('The Quick Brown Fox')
            ->and($data->shout)->toBe('QUIET PLEASE')
            ->and($data->ascii)->toBe('Uber Cafe')
            ->and($data->transliterated)->toBe('test')
            ->and($data->words)->toBe('one two three...')
            ->and($data->nullable)->toBeNull()
            ->and($data->count)->toBe(42)
            ->and($data->toArray())->toBe([
                'headline' => 'Hello World',
                'slug' => 'laravel-strings-are-fun',
                'summary' => 'This summary...',
                'after' => 'value',
                'afterLast' => 'three',
                'before' => 'value',
                'beforeLast' => 'one/two',
                'between' => 'value',
                'betweenFirst' => 'first',
                'snippet' => 'abcde',
                'title' => 'The Quick Brown Fox',
                'shout' => 'QUIET PLEASE',
                'ascii' => 'Uber Cafe',
                'transliterated' => 'test',
                'words' => 'one two three...',
                'nullable' => null,
                'count' => 42,
            ]);
    });
});
