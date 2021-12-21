<?php namespace Maclof\Kubernetes\Collections;

use Maclof\Kubernetes\Models\Certificate;

class CertificateCollection extends Collection
{
    /**
     * The constructor.
     *
     * @param array $items
     */
    public function __construct(array $items)
    {
        parent::__construct($this->getCertificates($items));
    }

    /**
     * Get an array of certificates.
     *
     * @param  array $items
     * @return array
     */
    protected function getCertificates(array $items): array
    {
        foreach ($items as &$item) {
            if ($item instanceof Certificate) {
                continue;
            }

            $item = new Certificate($item);
        }

        return $items;
    }
}
