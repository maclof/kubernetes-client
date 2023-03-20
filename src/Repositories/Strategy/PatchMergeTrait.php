<?php namespace Dasann\Kubernetes\Repositories\Strategy;

use Dasann\Kubernetes\Models\Model;

trait PatchMergeTrait {

    public function patch(Model $model): array
    {
        $this->client->setPatchType("merge");

        $result = parent::patch($model);

        // Reverting default patch type
        $this->client->setPatchType();

        return $result;
    }

}
