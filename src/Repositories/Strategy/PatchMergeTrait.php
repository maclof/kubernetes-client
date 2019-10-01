<?php namespace Maclof\Kubernetes\Repositories\Strategy;

use Maclof\Kubernetes\Models\Model;

trait PatchMergeTrait {

    public function patch(Model $model)
    {
        $this->client->setPatchType("merge");

        $result = parent::patch($model);

        // Reverting default patch type
        $this->client->setPatchType();

        return $result;
    }

}
