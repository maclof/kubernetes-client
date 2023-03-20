<?php namespace Dasann\Kubernetes\Collections;

use Dasann\Kubernetes\Models\Issuer;

class IssuerCollection extends Collection
{
    /**
     * The constructor.
     */
    public function __construct(array $items)
    {
        parent::__construct($this->getIssuers($items));
    }

    /**
     * Get an array of certificate issuers.
     */
    protected function getIssuers(array $items): array
    {
        foreach ($items as &$item) {
            if ($item instanceof Issuer) {
                continue;
            }

            $item = new Issuer($item);
        }

        return $items;
    }
}
