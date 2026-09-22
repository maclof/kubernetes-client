<?php

use Maclof\Kubernetes\Collections\PodCollection;
use Maclof\Kubernetes\Models\Pod;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection as IlluminateCollection;

class PodCollectionTest extends TestCase
{
	protected array $items = [
		[],
		[],
		[],
	];

	protected function getPodCollection(): PodCollection
	{
		$podCollection = new PodCollection($this->items);

		return $podCollection;
	}

	public function test_get_items(): void
	{
		$podCollection = $this->getPodCollection();
		$items = $podCollection->toArray();

		$this->assertTrue(is_array($items));
		$this->assertEquals(3, count($items));
	}

	public function test_models_serialize_through_illuminate_collections(): void
	{
		$items = ['pod' => ['metadata' => ['name' => 'example']]];
		$collection = new PodCollection($items);

		$this->assertInstanceOf(Arrayable::class, $collection->first());
		$this->assertSame($items, $collection->toArray());
		$this->assertSame($items, json_decode($collection->toJson(), true));
		$this->assertSame(['pods' => $items], (new IlluminateCollection([
			'pods' => $collection,
		]))->toArray());
	}

	public function test_mapping_preserves_keys_and_can_return_scalars(): void
	{
		$collection = new PodCollection([
			'pod' => ['metadata' => ['name' => 'example']],
		]);
		$mapped = $collection->map(function (Pod $pod, string $key): string {
			return $key . ':' . $pod->getMetadata('name');
		});

		$this->assertSame(IlluminateCollection::class, get_class($mapped));
		$this->assertSame(['pod' => 'pod:example'], $mapped->all());
		$this->assertInstanceOf(Pod::class, $collection->first());
	}

	public function test_filtering_preserves_model_instances_and_keys(): void
	{
		$kept = new Pod(['metadata' => ['name' => 'keep']]);
		$collection = new PodCollection([
			'discarded' => ['metadata' => ['name' => 'discard']],
			'kept' => $kept,
		]);
		$filtered = $collection->filter(function (Pod $pod): bool {
			return $pod->getMetadata('name') === 'keep';
		});

		$this->assertInstanceOf(PodCollection::class, $filtered);
		$this->assertSame(['kept' => $kept], $filtered->all());
		$this->assertCount(2, $collection);
	}
}
