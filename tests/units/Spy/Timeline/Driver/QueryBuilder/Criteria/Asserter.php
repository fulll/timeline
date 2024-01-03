<?php

namespace tests\units\Spy\Timeline\Driver\QueryBuilder\Criteria;

require_once __DIR__.'/../../../../../../../vendor/autoload.php';

use atoum\atoum\test;
use Spy\Timeline\Driver\QueryBuilder\Criteria\Asserter as TestedModel;

class Asserter extends test
{
    public function testField(): void
    {
        $this->if($model = new TestedModel())
            ->object($model->field('field'))
            ->isInstanceOf(TestedModel::class)
            ->string($model->getField())
            ->isEqualTo('field')
        ;
    }

    public function getAsserters(): array
    {
        return [['equals', '=', 'string'], ['notEquals', '!=', 'string'], ['in', 'IN', []], ['notIn', 'NOT IN', []], ['like', 'LIKE', 'string'], ['notLike', 'NOT LIKE', 'string'], ['lt', '<', 'string'], ['lte', '<=', 'string'], ['gt', '>', 'string'], ['gte', '>=', 'string']];
    }

    /**
     * @dataProvider getAsserters
     */
    public function testAsserters($method, string $operator, mixed $data): void
    {
        $this->if($model = new TestedModel())
            ->and($resultExpected = new TestedModel())
            ->and($resultExpected->field('field'))
            ->and($resultExpected->create($operator, $data))
            ->object($model->field('field')->{$method}($data))
            ->isEqualTo($resultExpected)
        ;
    }

    /**
     * @dataProvider getAsserters
     */
    public function testToArray($method, string $operator, mixed $data): void
    {
        $this->if($model = new TestedModel())
            ->and($model->field('field'))
            ->and($model->create($operator, $data))
            ->and($resultExpected = ['type' => 'expr', 'value' => ['field', $operator, $data]])
            ->array($model->toArray())
            ->isIdenticalTo($resultExpected)
        ;
    }

    /**
     * @dataProvider getAsserters
     */
    public function testFromArray($method, string $operator, mixed $data): void
    {
        $this->if($model = new TestedModel())
            ->and($resultExpected = new TestedModel())
            ->and($resultExpected->field('field'))
            ->and($resultExpected->create($operator, $data))
            ->and($arrayRepresentation = ['type' => 'expr', 'value' => ['field', $operator, $data]])
            ->object($model->fromArray($arrayRepresentation))
            ->isEqualTo($resultExpected)
        ;
    }
}
