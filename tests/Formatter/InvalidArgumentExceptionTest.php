<?php
/**
 * @author Richard Fussenegger <fleshgrinder@users.noreply.github.com>
 * @copyright 2017 Richard Fussenegger
 * @license http://unlicense.org/ Unlicense
 */

declare(strict_types = 1);

namespace Fleshgrinder\Core\Formatter;

use Fleshgrinder\Core\Value;
use PHPUnit\Framework\TestCase;

class InvalidArgumentExceptionTest extends TestCase {
	public static function provideArguments(): array {
		return [
			Value::TYPE_STRING        => ['arg'],
			\DateTimeImmutable::CLASS => [new \DateTimeImmutable],
		];
	}

	/**
	 * @testdox Message ends with
	 * @covers \Fleshgrinder\Core\Formatter\InvalidArgumentException::new
	 * @dataProvider provideArguments
	 */
	public static function testNew($argument) {
		static::assertSame(
			'Cannot format ' . Value::getType($argument),
			InvalidArgumentException::new($argument)->getMessage()
		);
	}
}
