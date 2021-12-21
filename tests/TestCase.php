<?php

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCase extends PHPUnitTestCase
{
	/**
	 * The api version.
	 */
	protected string $apiVersion = 'v1';

	/**
	 * The namespace.
	 */
	protected string $namespace = 'default';

	/**
	 * Get the contents of a fixture.
	 */
	protected function getFixture(string $path): ?string
	{
		$path = __DIR__ . '/fixtures/' . $path;

		if (!file_exists($path)) {
			return null;
		}

		$contents = file_get_contents($path);

		// Fix for windows encoded fixtures.
		$contents = str_replace("\r\n", "\n", $contents);

		return $contents;
	}
}
