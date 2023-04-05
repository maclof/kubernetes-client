<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\SubnamespaceAnchor;

class SubnamespaceAnchorCollection extends Collection
{
    /**
     * The constructor.
     */
    public function __construct(array $items)
    {
        parent::__construct($this->getSubnamespaceAnchors($items));
    }

    /**
     * Get an array of replication sets.
     */
    protected function getSubnamespaceAnchors(array $items): array
    {
        foreach ($items as &$item) {
            if ($item instanceof SubnamespaceAnchor) {
                continue;
            }

            $item = new SubnamespaceAnchor($item);
        }

        return $items;
    }
}
