<?php
/**
 * @author Richard Fussenegger <fleshgrinder@users.noreply.github.com>
 * @copyright 2017 Richard Fussenegger
 * @license http://unlicense.org/ Unlicense
 */

declare(strict_types = 1);

namespace Fleshgrinder\Core;

use PHPUnit\Framework\TestCase;

final class FormatterTest extends TestCase {
	public static function providePatterns(): array {
		$closed_resource = \fopen('php://memory', 'rb');
		\fclose($closed_resource);

		return [
			'a named placeholder.' => [
				'prefix value suffix',
				'prefix {placeholder} suffix',
				['placeholder' => 'value'],
			],

			'a positional placeholder.' => [
				'prefix value suffix',
				'prefix {} suffix',
				['value'],
			],

			'a fixed positional placeholder.' => [
				'prefix value suffix',
				'prefix {42} suffix',
				[42 => 'value'],
			],

			'a named, positional, and fixed positional placeholder.' => [
				'prefix named infix fixed suffix positional',
				'prefix {placeholder} infix {42} suffix {}',
				['positional', 'placeholder' => 'named', 42 => 'fixed'],
			],

			'a null argument.' => [
				'NULL',
				'{}',
				[\null],
			],

			'an empty traversable argument.' => [
				'empty ArrayIterator',
				'{}',
				[new \ArrayIterator],
			],

			'a traversable argument that has object keys.' => [
				'Hello, World',
				'{}',
				[
					(static function () {
						yield (object) ['p' => 0] => 'Hello';
						yield (object) ['p' => 1] => 'World';
					})(),
				],
			],

			'a bool true argument.' => [
				'TRUE',
				'{}',
				[\true],
			],

			'a bool false argument.' => [
				'FALSE',
				'{}',
				[\false],
			],

			'a stream resource argument.' => [
				'stream resource',
				'{:?}',
				[\tmpfile()],
			],

			'a closed resource argument.' => [
				Value::TYPE_CLOSED_RESOURCE,
				'{:?}',
				[$closed_resource],
			],

			'a float argument.' => [
				'0',
				'{}',
				[0.123456789],
			],

			'a float argument and decimal modifier.' => [
				'0.12346',
				'{.5}',
				[0.123456789],
			],

			'a float argument and exponent notation modifier.' => [
				'1.234568e-1',
				'{#e}',
				[0.123456789],
			],

			'a named float argument and decimal modifier.' => [
				'0.12346',
				'{float.5}',
				['float' => 0.123456789],
			],

			'a fixed positional float argument and decimal modifier.' => [
				'0.12346',
				'{42.5}',
				[42 => 0.123456789],
			],

			'an integer argument.' => [
				'1',
				'{}',
				[1],
			],

			'an integer argument and decimal modifier.' => [
				'1.0',
				'{.1}',
				[1],
			],

			'an integer argument and binary modifier.' => [
				'0b10',
				'{#b}',
				[2],
			],

			'an integer argument and octal modifier.' => [
				'0o755',
				'{#o}',
				[493],
			],

			'an integer argument and hexadecimal modifier.' => [
				'0x2A',
				'{#x}',
				[42],
			],

			'an empty array argument.' => [
				'empty array',
				'{}',
				[[]],
			],

			'an associative array argument.' => [
				'one',
				'{}',
				[['k1' => 'one']],
			],

			'an array argument and two values.' => [
				'Hello, World',
				'{}',
				[['Hello', 'World']],
			],

			'an array argument, two values, and a conjunction.' => [
				'one and two',
				'{:and}',
				[['one', 'two']],
			],

			'an array argument and three values.' => [
				'one, two, three',
				'{}',
				[['one', 'two', 'three']],
			],

			'an array argument, four values, and a conjunction.' => [
				'one, two, three, and four',
				'{:and}',
				[['one', 'two', 'three', 'four']],
			],

			'an array argument, four values, and an escape closing brace conjunction.' => [
				'one, two, three, } four',
				'{:}}}',
				[['one', 'two', 'three', 'four']],
			],

			'an object with a `__toString` method.' => [
				'message',
				'{}',
				[new class {
					public function __toString() {
						return 'message';
					}
				}],
			],

			'an object with a `toString` method.' => [
				'message',
				'{}',
				[new class {
					public function toString(): string {
						return 'message';
					}
				}],
			],

			'an object with a `toInt` method, and a decimal modifier.' => [
				'1,000.00',
				'{.2}',
				[new class {
					public function toInt(): int {
						return 1000;
					}
				}],
			],

			'an object with a `toFloat` method, and a decimal modifier.' => [
				'0.12346',
				'{.5}',
				[new class {
					public function toFloat(): float {
						return 0.123456789;
					}
				}],
			],

			'optional part that is not included.' => [
				'always',
				'always[ {}?]',
				[''],
			],

			'optional part that is included.' => [
				'always optional',
				'always[ {0}?]',
				['optional'],
			],

			'optional part that is include, with a single marked placeholder.' => [
				'always optional suffix',
				'always[ {0}? {1}]',
				['optional', 'suffix'],
			],

			'optional part that is not included, with multiple marked placeholders where all are empty.' => [
				'always',
				'always[ {0}? {1}? {2}?]',
				['', '', ''],
			],

			'optional part that is not included, with multiple marked placeholders where one is empty.' => [
				'always',
				'always[ {0}? {1}? {2}?]',
				['foo', 'bar', ''],
			],

			'two positional placeholders, and escaped brackets.' => [
				'[0.0, 1.0]',
				'[[{.1}, {.1}]]',
				[0, 1],
			],

			'various placeholders, and escaped brackets and braces.' => [
				'{prefix} value [suffix]',
				'{{prefix}} {} [[suffix]]',
				['value'],
			],

			'empty string argument.' => [
				'empty string',
				'{}',
				[''],
			],

			'void argument.' => [
				'void',
				'{:?}',
				[],
			],

			'caret notation for all ASCII control characters.' => [
				'^@, ^A, ^B, ^C, ^D, ^E, ^F, ^G, ^H, ^I, ^J, ^K, ^L, ^M, ^N, ^O, ^P, ^Q, ^R, ^S, ^T, ^U, ^V, ^W, ^X, ^Y, ^Z, ^[, ^\, ^], ^^, ^_,  , ~, ^?',
				'{#c}',
				[[
					"\u{00}", "\u{01}", "\u{02}", "\u{03}", "\u{04}", "\u{05}",
					"\u{06}", "\u{07}", "\u{08}", "\u{09}", "\u{0A}", "\u{0B}",
					"\u{0C}", "\u{0D}", "\u{0E}", "\u{0F}", "\u{10}", "\u{11}",
					"\u{12}", "\u{13}", "\u{14}", "\u{15}", "\u{16}", "\u{17}",
					"\u{18}", "\u{19}", "\u{1A}", "\u{1B}", "\u{1C}", "\u{1D}",
					"\u{1E}", "\u{1F}", ' ', '~', "\u{7F}",
				]]
			],

			'Unicode characters for all ASCII control characters.' => [
				'␀, ␁, ␂, ␃, ␄, ␅, ␆, ␇, ␈, ␉, ␊, ␋, ␌, ␍, ␎, ␏, ␐, ␑, ␒, ␓, ␔, ␕, ␖, ␗, ␘, ␙, ␚, ␛, ␜, ␝, ␞, ␟,  , ~, ␡',
				'{#p}',
				[[
					 "\u{00}", "\u{01}", "\u{02}", "\u{03}", "\u{04}", "\u{05}",
					 "\u{06}", "\u{07}", "\u{08}", "\u{09}", "\u{0A}", "\u{0B}",
					 "\u{0C}", "\u{0D}", "\u{0E}", "\u{0F}", "\u{10}", "\u{11}",
					 "\u{12}", "\u{13}", "\u{14}", "\u{15}", "\u{16}", "\u{17}",
					 "\u{18}", "\u{19}", "\u{1A}", "\u{1B}", "\u{1C}", "\u{1D}",
					 "\u{1E}", "\u{1F}", ' ', '~', "\u{7F}",
				 ]]
			],
		];
	}

	/**
	 * @testdox Format with
	 * @covers \Fleshgrinder\Core\Formatter::format
	 * @covers \Fleshgrinder\Core\Formatter::formatArg
	 * @covers \Fleshgrinder\Core\Formatter::formatArrayArg
	 * @covers \Fleshgrinder\Core\Formatter::formatNumber
	 * @covers \Fleshgrinder\Core\Formatter::formatString
	 * @covers \Fleshgrinder\Core\Formatter::formatControlChars
	 * @dataProvider providePatterns
	 */
	public static function testFormat(string $expected, string $pattern, array $arguments) {
		static::assertSame($expected, Formatter::format($pattern, $arguments));
	}

	/**
	 * @testdox Format returns the original string if arguments are empty.
	 *
	 * Please note that this is not an intended use case for this method,
	 * simply use a plain string if there is nothing to format. This test just
	 * verifies that the method does not break if there are no arguments.
	 *
	 * @covers \Fleshgrinder\Core\Formatter::format
	 */
	public static function testToStringNoArgs() {
		static::assertSame('NoOp', Formatter::format('NoOp', []));
	}

	/**
	 * @testdox Format throws a `MissingPlaceholderException` if a placeholder is missing from the arguments.
	 * @covers \Fleshgrinder\Core\Formatter::format
	 * @covers \Fleshgrinder\Core\Formatter::formatArg
	 * @covers \Fleshgrinder\Core\Formatter::formatNumber
	 * @covers \Fleshgrinder\Core\Formatter::formatString
	 * @uses \Fleshgrinder\Core\Formatter\MissingPlaceholderException
	 * @expectedException \Fleshgrinder\Core\Formatter\MissingPlaceholderException
	 * @expectedExceptionMessage Placeholder `0` not found in arguments, the following placeholders were present: none
	 */
	public static function testMissingPlaceholderException() {
		Formatter::format('{}', []);
	}

	/**
	 * @testdox Format throws an `InvalidArgumentException` if an object is given that has no conversion method.
	 * @covers \Fleshgrinder\Core\Formatter::format
	 * @covers \Fleshgrinder\Core\Formatter::formatArg
	 * @uses \Fleshgrinder\Core\Formatter\InvalidArgumentException
	 * @expectedException \Fleshgrinder\Core\Formatter\InvalidArgumentException
	 * @expectedExceptionMessage Cannot format
	 */
	public static function testInvalidObjectException() {
		Formatter::format('{}', [new class {}]);
	}

	/**
	 * @testdox Format throws an `InvalidArgumentException` if a resource is given.
	 * @covers \Fleshgrinder\Core\Formatter::format
	 * @covers \Fleshgrinder\Core\Formatter::formatArg
	 * @uses \Fleshgrinder\Core\Formatter\InvalidArgumentException
	 * @expectedException \Fleshgrinder\Core\Formatter\InvalidArgumentException
	 * @expectedExceptionMessage Cannot format resource
	 */
	public static function testInvalidResourceException() {
		Formatter::format('{}', [\tmpfile()]);
	}
}
