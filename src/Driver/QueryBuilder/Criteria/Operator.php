<?php

namespace Spy\Timeline\Driver\QueryBuilder\Criteria;

use Spy\Timeline\Driver\QueryBuilder\QueryBuilderFactory;

class Operator implements CriteriaInterface
{
    protected OperatorType $type;

    /**
     * @var CriteriaInterface[]
     */
    protected array $criterias = [];

    public function setType(OperatorType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function setCriterias(array $criterias): static
    {
        foreach ($criterias as $criteria) {
            $this->addCriteria($criteria);
        }

        return $this;
    }

    public function addCriteria(CriteriaInterface $criteria): void
    {
        $this->criterias[] = $criteria;
    }

    public function getType(): OperatorType
    {
        return $this->type;
    }

    public function getCriterias(): array
    {
        return $this->criterias;
    }

    /**
     * @return array{type: string, value: string, criterias: mixed[][]}
     */
    public function toArray(): array
    {
        $criterias = array_map(static fn ($criteria): array => $criteria->toArray(), $this->getCriterias());

        return ['type' => 'operator', 'value' => $this->getType(), 'criterias' => $criterias];
    }

    public function fromArray(array $data, QueryBuilderFactory $factory): static
    {
        $criterias = array_map(static function ($v) use ($factory): CriteriaInterface {
            if ('operator' === $v['type']) {
                return $factory->createOperatorFromArray($v);
            }

            if ('expr' === $v['type']) {
                return $factory->createAsserterFromArray($v);
            }

            throw new \InvalidArgumentException(sprintf('Type "%s" is not supported, use expr or operator.', $v['type']));
        }, $data['criterias']);

        $this->setType($data['value']);
        $this->setCriterias($criterias);

        return $this;
    }
}
