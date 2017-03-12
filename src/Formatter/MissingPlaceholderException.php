<?php
/**
 * @author Richard Fussenegger <fleshgrinder@users.noreply.github.com>
 * @copyright 2017 Richard Fussenegger
 * @license http://unlicense.org/ Unlicense
 */

declare(strict_types = 1);

namespace Fleshgrinder\Core\Formatter;

use Fleshgrinder\Core\Formatter;

/**
 * Formatter exception if a non-optional placeholder argument is missing from
 * the arguments that were given to format a pattern.
 */
class MissingPlaceholderException extends \DomainException {
	/** @noinspection PhpDocMissingThrowsInspection */
	/** @param int|string $placeholder */
	public static function new($placeholder, array $arguments): self {
		/** @noinspection ExceptionsAnnotatingAndHandlingInspection */
		return new self(Formatter::format(
			'Placeholder `{}` not found in arguments, the following placeholders were present: {arguments:and}',
			[$placeholder, 'arguments' => \array_keys($arguments) ?: 'none']
		));
	}
}
