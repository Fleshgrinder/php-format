[![Latest Stable Version](https://poser.pugx.org/fleshgrinder/format/v/stable)](https://packagist.org/packages/fleshgrinder/format)
[![License](https://poser.pugx.org/fleshgrinder/format/license)](https://packagist.org/packages/fleshgrinder/format)
[![Travis CI build status](https://img.shields.io/travis/Fleshgrinder/php-format.svg)](https://travis-ci.org/Fleshgrinder/php-format)
[![AppVeyor CI build status](https://ci.appveyor.com/api/projects/status/36pbndq2e739llp1/branch/master?svg=true)](https://ci.appveyor.com/project/Fleshgrinder/php-format/branch/master)

[![Coveralls branch](https://img.shields.io/coveralls/Fleshgrinder/php-format/master.svg)](https://coveralls.io/github/Fleshgrinder/php-format)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/Fleshgrinder/php-format.svg)](https://scrutinizer-ci.com/g/Fleshgrinder/php-format/)
[![Code Climate: GPA](https://img.shields.io/codeclimate/github/Fleshgrinder/php-format.svg)](https://codeclimate.com/github/Fleshgrinder/php-format)
[![Total Downloads](https://poser.pugx.org/fleshgrinder/format/downloads)](https://packagist.org/packages/fleshgrinder/format)
# Formatter
The **formatter** library provides the functionality to format special string
patterns. The implementation is similar to [`sprintf`](https://php.net/sprintf)
and [`msgfmt_format_message`](https://php.net/messageformatter.formatmessage)
but with various unique and very useful features.

- [Installation](#installation)
- [Usage](#usage)
	- [Placeholders](#placeholders)
	- [Type Modifier](#type-modifier)
	- [String Formatting and Modifiers](#string-formatting-and-modifiers)
	- [Number Formatting and Modifiers](#number-formatting-and-modifiers)
	- [Iterable Listings and Modifiers](#iterable-listings-and-modifiers)
	- [Optional Parts](#optional-parts)
	- [Escaping](#escaping)
	- [Errors and Exceptions](#errors-and-exceptions)
- [Testing](#testing)

## Installation
Open a terminal, enter your project directory and execute the following command
to add this library to your dependencies:

```bash
composer require fleshgrinder/format
```

This command requires you to have Composer installed globally, as explained in
the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the
Composer documentation.

## Usage
This library features a single static method to format string patterns. Some
examples of the format method are:

```php
<?php use Fleshgrinder\Core\Formatter;

assert(Formatter::format('Hello, {}!', ['World'])    === 'Hello, World!');
assert(Formatter::format('The number is {}', [1000]) === 'The number is 1,000');
assert(Formatter::format('{}', [[1, 2, 3]])          === '1, 2, 3');
assert(Formatter::format('{value}', ['value' => 42]) === '42');
assert(Formatter::format('{} {}', [1, 2])            === '1 2');
assert(Formatter::format('{.3}', [0.123456789])      === '0.123');
assert(Formatter::format('{.2:and}', [[1, 2, 3]])    === '1.00, 2.00, and 3.00');
assert(Formatter::format('{#b}', [2])                === '0b10');
assert(Formatter::format('{:?}', [tmpfile()])        === 'stream resource');
```

### Placeholders
Each placeholder can specify which argument value it references, and if
omitted it is assumed to be “the next argument”. For example, the pattern
`{} {} {}` would take three arguments, and they would be formatted in the
same order as they are given. The pattern `{2} {1} {0}`, however, would format
arguments in reverse order.

Things can get a little tricky once you start intermingling the two types of
positional placeholders. The “next argument” specifier can be thought of as an
iterator over the arguments. Each time a “next argument” specifier is seen, the
iterator advances. This leads to behavior like this:

```php
<?php use Fleshgrinder\Core\Formatter;

assert(Formatter::format('{1} {} {0} {}', [1, 2]) === '2 1 1 2');
```

The internal iterator over the arguments has not been advanced by the time the
first `{}` is seen, so it prints the first argument. Then upon reaching the
second `{}`, the iterator has advanced forward to the second argument.
Essentially, placeholders which explicitly specify their argument do not affect
placeholders which do not specify an argument in terms of positional
placeholders.

Placeholders are not limited to numbers, it is also possible to access them
by names. Names are limited to `A-Za-z0-9_-` characters, this ensures highest
compatibility and should match 99&thinsp;% of all associative array keys in the
PHP world:

```php
<?php use Fleshgrinder\Core\Formatter;

assert(Formatter::format('{placeholder}', ['placeholder' => 'test'])        === 'test');
assert(Formatter::format('{placeholder} {}', ['placeholder' => 2, 1])       === '2 1');
assert(Formatter::format('{a} {c} {b}', ['a' => 'a', 'b' => 'b', 'c' => 3]) === 'a 3 b');
```

A pattern is required to use all placeholders, but not all arguments. A
[`MissingPlaceholderException`](src/Formatter/MissingPlaceholderException.php)
is thrown if a placeholder is missing from the arguments:

```php
<?php use Fleshgrinder\Core\Formatter;
use Fleshgrinder\Core\Formatter\MissingPlaceholderException;

try {
    Formatter::format('{}', []);
}
catch (MissingPlaceholderException $e) {
    assert($e->getMessage() === 'Placeholder `0` missing from arguments, got empty array');
}
```

You may refer to the same argument more than once in the pattern, including
different modifiers (which are explained in the following sections).

```php
<?php use Fleshgrinder\Core\Formatter;

assert(Formatter::format('{a} {a} {a}', ['a' => 'foo']) === 'foo foo foo');
```

### Type Modifier
The **type modifier** can be used to print the type of an argument value
instead of attempting to format the value itself:

```php
<?php use Fleshgrinder\Core\Formatter;

assert(Formatter::format('{:?}', [])             === 'void');
assert(Formatter::format('{:?}', [[]])           === 'array');
assert(Formatter::format('{:?}', [true])         === 'boolean');
assert(Formatter::format('{:?}', [false])        === 'boolean');
assert(Formatter::format('{:?}', [1.2])          === 'float');
assert(Formatter::format('{:?}', [1])            === 'integer');
assert(Formatter::format('{:?}', [null])         === 'null');
assert(Formatter::format('{:?}', [new stdClass]) === 'stdClass');
assert(Formatter::format('{:?}', [new DateTime]) === 'DateTime');
assert(Formatter::format('{:?}', [tmpfile()])    === 'stream resource');
assert(Formatter::format('{:?}', [''])           === 'string');
```

Combination with positional and named placeholders is of course possible:

```php
<?php use Fleshgrinder\Core\{Formatter, Value};

assert(
    Formatter::format(
        'Expected argument of type {expected}, got {actual:?}',
        ['expected' => Value::TYPE_ARRAY, 'actual' => '']
    ) === 'Expected argument of type array, got string'
);
```

The [`fleshgrinder/value` library](https://github.com/Fleshgrinder/php-value)
is used to get the type of the value, this means in effect that type names are
consistent in naming and case. However, the output for resources is slightly
extended by including the type of the resource:

```php
<?php use Fleshgrinder\Core\{Formatter, Value};

$r = tmpfile();

assert(Value::getType($r)              === 'resource');
assert(Formatter::format('{:?}', [$r]) === 'stream resource');
```

> **IMPORTANT**
>
> The type modifier cannot be combined with any other modifier and always
> overwrites anything else!

### String Formatting and Modifiers
The method is binary safe and strings are printed as is.

Since version 1.1.0 empty strings and objects that can be converted to strings
are formatted as `empty {:?}` instead of resulting in no output at all:

```php
<?php namespace Fleshgrinder\Examples;

use Fleshgrinder\Core\Formatter;

class Stringable { function __toString() { return ''; } }

assert(Formatter::format('{}', ['']) === 'empty string');
assert(Formatter::format('{}', [new Stringable]) === 'empty Fleshgrinder\Examples\Stringable');
```

Also available since 1.1.0 are the
[caret notation](https://en.wikipedia.org/wiki/Caret_notation) modifier `c`
and the printable Unicode replacement modifier `p` for ASCII control
characters:

```php
<?php use Fleshgrinder\Core\Formatter;

assert(Formatter::format('{#c}', ["\0\n"]) === '^@^J');
assert(Formatter::format('{#p}', ["\0\n"]) === '␀␊');
```

This is useful while constructing error messages where a string that violated
some constraint should be included in the error message, but might lead to
undesired side-effects due to the nature of control characters. Both string
modifiers help to mitigate this problem. Note that recreation of the original
string might be impossible with the caret notation, the printable Unicode
replacements should be used in such cases. However, those characters are UTF-8
characters and not ASCII characters, which might not be acceptable.

### Number Formatting and Modifiers
Numbers are formatted with [`number_format`](https://php.net/number-format) and
its default values for decimal and thousand separators, this ensures best
readability and consistent formatting. The amount of decimals defaults to zero,
but may be configured by inserting a natural number separated by a dot (`.`)
within the braces after the placeholder:

```php
<?php use Fleshgrinder\Core\Formatter;

assert(Formatter::format('{.3}', [1.23456])   === '1.235');
assert(Formatter::format('$ {.2}', [9999.99]) === '$ 9,999.99');
assert(Formatter::format('{0.3}', [1.2345])   === '1.235');
assert(Formatter::format('{a.2}', ['a' => 1]) === '1.00');
```

The output format of numbers can be changed with format modifiers. A format
modifier is added by inserting the desired modifier after a hash symbol (`#`)
within the braces after the placeholder. Available format modifiers are:

- `#b` for binary numbers,
- `#e` for exponent notation,
- `#o` for octal, and
- `#x` for hexadecimal.

```php
<?php use Fleshgrinder\Core\Formatter;

assert(Formatter::format('{#b}', [2])           === '0b10');
assert(Formatter::format('{#e}', [0.123456789]) === '1.234568e-1');
assert(Formatter::format('{#o}', [493])         === '0o755');
assert(Formatter::format('{#x}', [42])          === '0x2A');
```

Note that there are no `#E` or `#X` modifiers, this is on purpose to force
consistent output.

### Iterable Listings and Modifiers
Iterable data structures are formatted as comma-separated lists. An optional
conjunction may be configured by adding the desired word after a colon (`:`)
inside the braces after the placeholder:

```php
<?php use Fleshgrinder\Core\Formatter;

assert(Formatter::format('{}', [[]])                 === 'empty array');
assert(Formatter::format('{}', [new ArrayIterator])  === 'empty ArrayIterator');
assert(Formatter::format('{}', [['a']])              === 'a');
assert(Formatter::format('{}', [['a', 'b']])         === 'a, b');
assert(Formatter::format('{}', [['a', 'b', 'c']])    === 'a, b, c');
assert(Formatter::format('{:and}', [['a', 'b']])     === 'a and b');
assert(Formatter::format('{:or}', [['a', 'b', 'c']]) === 'a, b, or c');
```

This is perfect for the construction of error messages:

```php
<?php use Fleshgrinder\Core\Formatter;

try {
    $expected = ['a', 'b', 'c'];
    $actual   = 'x';

    if (in_array($actual, $expected, true) === false) {
        throw new InvalidArgumentException(Formatter::format(
            'Value must be one of {expected:or}, got {actual}',
            ['expected' => $expected, 'actual' => $actual]
        ));
    }
}
catch (InvalidArgumentException $e) {
    assert($e->getMessage() === 'Value must be one of a, b, or c, got x');
}
```

Other modifiers, except for type, are of course combinable with iterable
listings:

```php
<?php use Fleshgrinder\Core\Formatter;

assert(Formatter::format('{.1:and}', [[0, 1, 2]])   === '0.0, 1.0, and 2.0');
assert(Formatter::format('{#b:and}', [[0, 1, 2]])   === '0b0, 0b1, and 0b10');
assert(Formatter::format('{#o:and}', [[7, 8, 9]])   === '0o7, 0o10, and 0o11');
assert(Formatter::format('{#x:and}', [[9, 10, 11]]) === '0x9, 0xA, and 0xB');
```

Note well that expansion of iterable data structures is recursive, this might
lead to unexpected output, but it might also result in infinite loops in case
the data structure contains circular references. Ensure that your iterable
argument values are sane, you have been warned:

```php
<?php use Fleshgrinder\Core\Formatter;

assert(Formatter::format('{}', [[0, [1, 2], 3]]) === '0, 1, 2, 3');
```

### Optional Parts
Another unique feature are optional parts, which are included only if a marked
argument is present and contains a non-empty value (according to PHP’s
[empty](https://php.net/empty) rules). Optional parts are specified by
enclosing some text in brackets:

```php
<?php use Fleshgrinder\Core\Formatter;

$pattern = 'This is always printed[, and this is printed only if {this_argument}? is non-empty]';

assert(
    Formatter::format($pattern, ['this_argument' => ''])
    === 'This is always printed'
);

assert(
    Formatter::format($pattern, ['this_argument' => 'this argument'])
    === 'This is always printed, and this is printed only if this argument is non-empty'
);
```

A placeholder is marked by appending a question mark (`?`) after the closing
brace of the placeholder. All marked placeholders within an optional part must
be non-empty in order to be included. Note that no exception is thrown if a
marked placeholder within an optional part is missing from the arguments, as
this counts as being empty. Note further that nesting of optional parts is not
possible, however, if you think that this would be greater feature feel free to
[open an issue](/issues/new).

> **IMPORTANT**
>
> Only fixed positional and named placeholders can be marked, the internal
> argument iterator is not taken into account. This means in effect that a
> pattern like the following never includes the optional part because there
> is no fixed positional or named placeholder present:
>
> ```php
> <?php use Fleshgrinder\Core\Formatter;
>
> assert(Formatter::format('[{}?]', ['foobar']) === '');
> ```
>
> The reason for this limitation is simple: optional parts are evaluated
> separately before the full pattern is being evaluated. Hence, any internal
> iterator would start counting inside the optional part, which would lead to
> hard to understand and weird behavior; if supported.

### Escaping
The special characters `[`, `]`, `{`, and `}` can be escaped by doubling them,
`[[`, `]]`, `{{`, or `}}` respectively.

```php
<?php use Fleshgrinder\Core\Formatter;

assert(Formatter::format('[[{.1}, {.1}]]', [0, 1])     === '[0.0, 1.0]');
assert(Formatter::format('{:con}}junction}', [[0, 1]]) === '0 con}junction 1');
```

### Errors and Exceptions
Any error or exception that might be thrown by an argument that is being
formatted is not caught and bubbles up. The method itself throws the already
explained [`MissingPlaceholderException`](src/Formatter/MissingPlaceholderException.php).
It might throw an [`InvalidArgumentException`](src/Formatter/InvalidArgumentException.php)
as well, if the type modifier is not present and an argument is:

- a resource—as there is no meaningful way to format them—or
- an object that is not `Traversable`, and has none of the following methods:
    - `__toString`
    - `toFloat`
    - `toInt`
    - `toString`

## Testing
Open a terminal, enter the project directory and execute the following commands
to run the [PHPUnit](https://phpunit.de/) tests with your locally installed
PHP executable. This requires that you have at least `make` 4.0 installed:

```bash
make
```

You can also execute the following two commands, in case `make` is not
available on your system, or too old:

```bash
composer install
composer test
```
