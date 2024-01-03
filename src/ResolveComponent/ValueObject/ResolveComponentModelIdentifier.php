<?php

namespace Spy\Timeline\ResolveComponent\ValueObject;

use Spy\Timeline\Exception\ResolveComponentDataException;

/**
 * This value object is responsible for
 * - Checking if the given model and identifier are resolvable
 * - In the current implementation when an object is given, we retrieve the identifier from the object
 * - So when asking for the identifier, if an object is given we return null.
 */
class ResolveComponentModelIdentifier
{
    private readonly \object|\string $model;

    private $identifier;

    /**
     * @param null|string|array $identifier
     */
    public function __construct(string|object $model, $identifier = null)
    {
        $this->guardValidModelAndIdentifier($model, $identifier);
        $this->model = $model;
        $this->identifier = $identifier;
    }

    /**
     * Gets the model.
     */
    public function getModel(): object|string
    {
        return $this->model;
    }

    /**
     * Gets the identifier.
     *
     * @return array|null|string
     */
    public function getIdentifier()
    {
        if (is_object($this->model)) {
            return null;
        }

        return $this->identifier;
    }

    /**
     * @param $model
     * @param $identifier
     *
     * @throws ResolveComponentDataException
     */
    private function guardValidModelAndIdentifier(\object $model, $identifier)
    {
        if (empty($model) || (!is_object($model) && (null === $identifier || '' === $identifier))) {
            throw new ResolveComponentDataException('Model has to be an object or (a scalar + an identifier in 2nd argument)');
        }
    }
}
