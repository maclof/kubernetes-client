<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\Issuer;

class IssuerCollection extends Collection
{
    /**
     * The constructor.
     *
     * @param array $items
     */
    public function __construct(array $items)
    {
        parent::__construct($this->getIssuers($items));
    }

    /**
     * Get an array of certificate issuers.
     *
     * @param  array $items
     * @return array
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
