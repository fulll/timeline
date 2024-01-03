<?php

namespace Spy\Timeline\Driver\QueryBuilder\Criteria;

class Asserter implements CriteriaInterface
{
    final public const ASSERTER_EQUAL = '=';
    final public const ASSERTER_NOT_EQUAL = '!=';
    final public const ASSERTER_IN = 'IN';
    final public const ASSERTER_NOT_IN = 'NOT IN';
    final public const ASSERTER_LIKE = 'LIKE';
    final public const ASSERTER_NOT_LIKE = 'NOT LIKE';
    final public const ASSERTER_LOWER_THAN = '<';
    final public const ASSERTER_LOWER_THAN_EQUAL = '<=';
    final public const ASSERTER_GREATER_THAN = '>';
    final public const ASSERTER_GREATER_THAN_EQUAL = '>=';

    protected string $field;
    protected string $operator;
    protected mixed $value;

    public function field(string $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function transform(mixed $value): mixed
    {
        return $value;
    }

    public function equals($value): static
    {
        return $this->create(self::ASSERTER_EQUAL, $this->transform($value));
    }

    public function notEquals(mixed $value): static
    {
        return $this->create(self::ASSERTER_NOT_EQUAL, $this->transform($value));
    }

    public function in(array $values): static
    {
        return $this->create(self::ASSERTER_IN, $this->transform($values));
    }

    public function notIn(array $values): static
    {
        return $this->create(self::ASSERTER_NOT_IN, $this->transform($values));
    }

    public function like(mixed $value): static
    {
        return $this->create(self::ASSERTER_LIKE, $this->transform($value));
    }

    public function notLike(mixed $value): static
    {
        return $this->create(self::ASSERTER_NOT_LIKE, $this->transform($value));
    }

    public function lt(mixed $value): static
    {
        return $this->create(self::ASSERTER_LOWER_THAN, $this->transform($value));
    }

    public function lte(mixed $value): static
    {
        return $this->create(self::ASSERTER_LOWER_THAN_EQUAL, $this->transform($value));
    }

    public function gt(mixed $value): static
    {
        return $this->create(self::ASSERTER_GREATER_THAN, $this->transform($value));
    }

    public function gte(mixed $value): static
    {
        return $this->create(self::ASSERTER_GREATER_THAN_EQUAL, $this->transform($value));
    }

    public function create(string $operator, mixed $value): static
    {
        $this->operator = $operator;
        $this->value    = $value;

        return $this;
    }

    /**
     * @return array{type: string, value: array}
     */
    public function toArray(): array
    {
        return ['type' => 'expr', 'value' => [$this->field, $this->operator, $this->value]];
    }

    public function fromArray(array $data): static
    {
        [$field, $operator, $value] = $data['value'];

        return $this->field($field)
            ->create($operator, $value)
        ;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
