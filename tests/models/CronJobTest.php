<?php

use Maclof\Kubernetes\Models\CronJob;

class CronJobTest extends TestCase
{
	public function test_get_schema()
	{
		$job = new CronJob;

		$schema = $job->getSchema();
		$fixture = $this->getFixture('cron-jobs/empty.json');

		$this->assertEquals($schema, $fixture);
	}

	public function test_get_metadata()
	{
		$job = new CronJob([
			'metadata' => [
				'name' => 'test',
			],
		]);

		$metadata = $job->getMetadata('name');

		$this->assertEquals($metadata, 'test');
	}
}
