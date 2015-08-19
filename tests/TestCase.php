<?php

class TestCase extends PHPUnit_Framework_TestCase
{
	/**
	 * The api version.
	 *
	 * @var string
	 */
	protected $apiVersion = 'v1';

	/**
	 * The namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'default';

	/**
	 * Get the contents of a fixture.
	 *
	 * @param  string $path
	 * @return string|null
	 */
	protected function getFixture($path)
	{
		$path = __DIR__ . '/fixtures/' . $path;

		if (!file_exists($path)) {
			return;
		}

		return file_get_contents($path);
	}
}
