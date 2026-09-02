<?php namespace Maclof\Kubernetes\Repositories\Utils;

use RuntimeException;

class ParsingException extends RuntimeException
{
	public function __construct(int $lineNumber, int $charNumber, string $message)
	{
		parent::__construct(sprintf(
			'JSON parse error at line %d, character %d: %s',
			$lineNumber,
			$charNumber,
			$message
		));
	}
}
