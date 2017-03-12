<?php
/**
 * @author Richard Fussenegger <fleshgrinder@users.noreply.github.com>
 * @copyright 2017 Richard Fussenegger
 * @license http://unlicense.org/ Unlicense
 */

declare(strict_types = 1);

namespace Fleshgrinder\Core\Formatter;

use Fleshgrinder\Core\Value;

/**
 * Formatter exception if an argument is given that cannot be formatted in a
 * meaningful way.
 */
class InvalidArgumentException extends \InvalidArgumentException {
	public static function new($argument): self {
		return new self('Cannot format ' . Value::getType($argument));
	}
}
