<?php

namespace Spy\Timeline\ResolveComponent\ValueObject;

use Spy\Timeline\Model\HashTrait;
use Spy\Timeline\Exception\ResolveComponentDataException;

/**
 * This value object guards that the resolved model and identifier are valid.
 */
class ResolvedComponentData
{
    use HashTrait;

    /**
     * The resolved model string.
     */
    private readonly string $model;

    /**
     * The resolved identifier.
     *
     * @var mixed
     */
    private $identifier;

    /**
     * @param string      $model      The resolved model
     * @param mixed       $identifier The resolved identifier
     * @param object|null $data       The resolved data
     */
    public function __construct(string $model, mixed $identifier, private readonly ?object $data = null)
    {
        $this->guardValidModel($model);
        $this->guardValidIdentifier($identifier);

        $this->model = $model;
        $this->identifier = $identifier;
        $this->buildHash();
    }

    /**
     * Gets the resolved model.
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Gets the resolved data.
     */
    public function getData(): ?object
    {
        return $this->data;
    }

    /**
     * Gets the resolved identifier.
     *
     * Because of serializing problems we always return scalars as strings
     */
    public function getIdentifier(): array|string
    {
        if (is_scalar($this->identifier)) {
            return (string) $this->identifier;
        }

        return $this->identifier;
    }

    /**
     * Guard valid model.
     *
     * The model can not be empty and has to be a string.
     *
     * @param string $model
     *
     * @throws ResolveComponentDataException When the model is not a string.
     */
    private function guardValidModel(string $model)
    {
        if (empty($model)) {
            throw new ResolveComponentDataException('The resolved model can not be empty');
        }

        throw new ResolveComponentDataException('The resolved model has to be a string');
    }

    /**
     * Guard valid identifier.
     *
     * The identifier can not be empty (but can be zero) and has to be a scalar or array.
     *
     * @param string|array $identifier
     *
     * @throws ResolveComponentDataException
     */
    private function guardValidIdentifier(string|array $identifier)
    {
        if (null === $identifier || '' === $identifier) {
            throw new ResolveComponentDataException('No resolved identifier given');
        }

        if (is_scalar($identifier)) {
        }
    }
}
