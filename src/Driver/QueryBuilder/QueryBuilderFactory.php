<?php

namespace Spy\Timeline\Driver\QueryBuilder;

use Spy\Timeline\Driver\ActionManagerInterface;
use Spy\Timeline\Driver\QueryBuilder\Criteria\Asserter;
use Spy\Timeline\Driver\QueryBuilder\Criteria\CriteriaInterface;
use Spy\Timeline\Driver\QueryBuilder\Criteria\Operator;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QueryBuilderFactory
{
    /**
     * @var array
     */
    protected $classes = [];

    /**
     * @param array $classes classes could contain query_builder, operator, asserter/
     */
    public function __construct(array $classes = [])
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(['asserter'      => '\\' . Asserter::class, 'operator'      => '\\' . Operator::class, 'query_builder' => '\\' . QueryBuilder::class]);

        $this->classes = $resolver->resolve($classes);
    }

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder()
    {
        return new $this->classes['query_builder']($this);
    }

    /**
     * @param array                  $data          data
     * @param ActionManagerInterface|null $actionManager actionManager
     *
     * @return QueryBuilder
     */
    public function createQueryBuilderFromArray(array $data, ActionManagerInterface $actionManager = null)
    {
        return $this->createQueryBuilder()
            ->fromArray($data, $actionManager)
        ;
    }

    /**
     * @return CriteriaInterface
     */
    public function createOperator()
    {
        return new $this->classes['operator']();
    }

    /**
     * @param array $data data
     *
     * @return CriteriaInterface
     */
    public function createOperatorFromArray(array $data)
    {
        return $this->createOperator()->fromArray($data, $this);
    }

    /**
     * @return CriteriaInterface
     */
    public function createAsserter()
    {
        return new $this->classes['asserter']();
    }

    /**
     * @param array $data data
     *
     * @return CriteriaInterface
     */
    public function createAsserterFromArray(array $data)
    {
        return $this->createAsserter()->fromArray($data);
    }
}
