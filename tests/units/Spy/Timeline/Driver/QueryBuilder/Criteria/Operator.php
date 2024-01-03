<?php

namespace tests\units\Spy\Timeline\Driver\QueryBuilder\Criteria;

use Spy\Timeline\Driver\QueryBuilder\Criteria\CriteriaInterface;
use Spy\Timeline\Driver\QueryBuilder\Criteria\Asserter;
use Spy\Timeline\Driver\QueryBuilder\QueryBuilderFactory;
require_once __DIR__.'/../../../../../../../vendor/autoload.php';

use atoum\atoum\test;
use Spy\Timeline\Driver\QueryBuilder\Criteria\Operator as TestedModel;

class Operator extends test
{
    public function testSetType(): void
    {
        $this->if($model = new TestedModel())
            ->exception(static function () use ($model): void {
                $model->setType('unknown');
            })
            ->isInstanceOf('\InvalidArgumentException')
            ->hasMessage('Type "unknown" not supported')
            ->object($model->setType('AND'))
            ->isInstanceOf(TestedModel::class)
            ->string($model->getType())
            ->isEqualTo('AND')
        ;
    }

    public function testToArray(): void
    {
        $this->if($model = new TestedModel())
            ->and($this->mockClass(CriteriaInterface::class, '\Mock'))
            ->and($criteria = new \Mock\CriteriaInterface())
            ->and($criteria->getMockController()->toArray = 'CRITERIA_TO_ARRAY')
            ->and($model->addCriteria($criteria))
            ->and($model->setType('AND'))
            ->array($model->toArray())
            ->isEqualTo(['type' => 'operator', 'value' => 'AND', 'criterias' => ['CRITERIA_TO_ARRAY']])
        ;
    }

    public function testFromArray(): void
    {
        $this->if($model = new TestedModel())
            ->and($this->mockClass(TestedModel::class, '\Mock'))
            ->and($this->mockClass(Asserter::class, '\Mock'))
            ->and($this->mockClass(QueryBuilderFactory::class, '\Mock'))
            ->and($operator = new \Mock\Operator())
            ->and($asserter = new \Mock\Asserter())
            // init factory
            ->and($factory = new \Mock\QueryBuilderFactory())
            ->and($factory->getMockController()->createOperatorFromArray = $operator)
            ->and($factory->getMockController()->createAsserterFromArray = $asserter)
            // reuslt expected
            ->and($resultExpected = new TestedModel())
            ->and($resultExpected->addCriteria($asserter))
            ->and($resultExpected->addCriteria($operator))
            ->and($resultExpected->setType('AND'))
            // let's go.
            ->object($model->fromArray(['type'      => 'operator', 'value'     => 'AND', 'criterias' => [['type' => 'expr'], ['type' => 'operator']]], $factory))
            ->isEqualTo($resultExpected)
            // to be sure
            ->object($model->fromArray(['type'      => 'operator', 'value'     => 'AND', 'criterias' => [['type' => 'operator'], ['type' => 'expr']]], $factory))
            ->isNotEqualTo($resultExpected)
        ;
    }
}
