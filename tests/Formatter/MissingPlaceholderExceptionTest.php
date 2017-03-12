<?php
/**
 * @author Richard Fussenegger <fleshgrinder@users.noreply.github.com>
 * @copyright 2017 Richard Fussenegger
 * @license http://unlicense.org/ Unlicense
 */

declare(strict_types = 1);

namespace Fleshgrinder\Core\Formatter;

use PHPUnit\Framework\TestCase;

class MissingPlaceholderExceptionTest extends TestCase {
	/**
	 * @testdox Message ends in none if arguments are empty.
	 * @covers \Fleshgrinder\Core\Formatter\MissingPlaceholderException::new
	 * @uses \Fleshgrinder\Core\Formatter
	 */
	public static function testNewNone() {
		static::assertSame(
			'Placeholder `0` not found in arguments, the following placeholders were present: none',
			MissingPlaceholderException::new(0, [])->getMessage()
		);
	}

	/**
	 * @testdox Message ends in a placeholder listing with and conjunction.
	 * @covers \Fleshgrinder\Core\Formatter\MissingPlaceholderException::new
	 * @uses \Fleshgrinder\Core\Formatter
	 */
	public static function testNew() {
		static::assertSame(
			'Placeholder `3` not found in arguments, the following placeholders were present: 0, 1, and 2',
			MissingPlaceholderException::new(3, ['a', 'b', 'c'])->getMessage()
		);
	}
}
