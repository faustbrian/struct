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
            'snake' => 'Hello World Example',
            'kebab' => 'Hello World Example',
            'camel' => 'hello world example',
            'studly' => 'hello world example',
            'pascal' => 'hello world example',
            'started' => 'value',
            'finished' => 'value',
            'wrapped' => 'value',
            'unwrapped' => '"value"',
            'choppedStart' => 'prefix-value',
            'choppedEnd' => 'value-suffix',
            'numbers' => 'a1b2c3',
            'deduplicated' => 'a---b----c',
            'replaced' => 'hello world',
            'replaceFirst' => 'foo foo',
            'replaceLast' => 'foo foo',
            'replaceStart' => 'foo value',
            'replaceEnd' => 'value foo',
            'masked' => '1234567890',
            'padLeft' => '42',
            'padRight' => '42',
            'padBoth' => '42',
            'reversed' => 'desserts',
            'repeated' => 'ha',
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
            ->and($data->snake)->toBe('hello_world_example')
            ->and($data->kebab)->toBe('hello-world-example')
            ->and($data->camel)->toBe('helloWorldExample')
            ->and($data->studly)->toBe('HelloWorldExample')
            ->and($data->pascal)->toBe('HelloWorldExample')
            ->and($data->started)->toBe('prefix-value')
            ->and($data->finished)->toBe('value-suffix')
            ->and($data->wrapped)->toBe('[value[')
            ->and($data->unwrapped)->toBe('value')
            ->and($data->choppedStart)->toBe('value')
            ->and($data->choppedEnd)->toBe('value')
            ->and($data->numbers)->toBe('123')
            ->and($data->deduplicated)->toBe('a-b-c')
            ->and($data->replaced)->toBe('hello there')
            ->and($data->replaceFirst)->toBe('bar foo')
            ->and($data->replaceLast)->toBe('foo bar')
            ->and($data->replaceStart)->toBe('bar value')
            ->and($data->replaceEnd)->toBe('value bar')
            ->and($data->masked)->toBe('12****7890')
            ->and($data->padLeft)->toBe('00042')
            ->and($data->padRight)->toBe('42000')
            ->and($data->padBoth)->toBe('004200')
            ->and($data->reversed)->toBe('stressed')
            ->and($data->repeated)->toBe('hahaha')
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
                'snake' => 'hello_world_example',
                'kebab' => 'hello-world-example',
                'camel' => 'helloWorldExample',
                'studly' => 'HelloWorldExample',
                'pascal' => 'HelloWorldExample',
                'started' => 'prefix-value',
                'finished' => 'value-suffix',
                'wrapped' => '[value[',
                'unwrapped' => 'value',
                'choppedStart' => 'value',
                'choppedEnd' => 'value',
                'numbers' => '123',
                'deduplicated' => 'a-b-c',
                'replaced' => 'hello there',
                'replaceFirst' => 'bar foo',
                'replaceLast' => 'foo bar',
                'replaceStart' => 'bar value',
                'replaceEnd' => 'value bar',
                'masked' => '12****7890',
                'padLeft' => '00042',
                'padRight' => '42000',
                'padBoth' => '004200',
                'reversed' => 'stressed',
                'repeated' => 'hahaha',
                'nullable' => null,
                'count' => 42,
            ]);
    });
});
