<?php

use Maclof\Kubernetes\Models\CronJob;

class CronJobTest extends TestCase
{
	public function test_get_schema()
	{
		$cronJob = new CronJob;

		$schema = $cronJob->getSchema();
		$fixture = $this->getFixture('cron-jobs/empty.json');

		$this->assertEquals($schema, $fixture);
	}

	public function test_get_metadata()
	{
		$cronJob = new CronJob([
			'metadata' => [
				'name' => 'test',
			],
		]);

		$metadata = $cronJob->getMetadata('name');

		$this->assertEquals($metadata, 'test');
	}

	public function test_set_api_version()
	{
		$cronJob = new CronJob;

		$this->assertEquals($cronJob->getApiVersion(), 'batch/v1beta1');

		$cronJob->setApiVersion('batch/v2alpha1');
		$this->assertEquals($cronJob->getApiVersion(), 'batch/v2alpha1');
	}
}
