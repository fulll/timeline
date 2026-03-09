<?php

namespace Spy\Timeline\Model;

trait HashTrait
{
    /**
     * @var string
     */
    protected $hash;

    protected string|null $hashMigrated = null;

    /**
     * {@inheritdoc}
     */
    public function buildHash()
    {
        $model = $this->getModel();
        $identifier = $this->getIdentifier();
        $this->hash = $model.'#'.serialize($identifier);

        if (is_scalar($identifier)) {
            // to avoid issue of serialization.
            $identifier = (string) $identifier;
        } elseif (!is_array($identifier)) {
            throw new \InvalidArgumentException('Identifier must be a scalar or an array');
        }
        $identifierMigrated = is_array($identifier) ? (string) reset($identifier) : $identifier;

        $this->hashMigrated = $model.'##'.$identifierMigrated;
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

    public function getHashMigrated(): string|null
    {
        return $this->hashMigrated;
    }
}
