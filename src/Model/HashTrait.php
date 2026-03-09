<?php

namespace Spy\Timeline\Model;

trait HashTrait
{
    /**
     * @var string
     */
    protected $hash;

    /**
     * @var string
     */
    protected $hashBackup;

    /**
     * {@inheritdoc}
     */
    public function buildHash()
    {
        $model = $this->getModel();
        $identifier = $this->getIdentifier();
        $identifier = is_array($identifier) ? (string) reset($identifier) : $identifier;

        $this->hash = $model.'##'.$identifier;
    }

    /**
     * Gets the resolved hash.
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }
}
