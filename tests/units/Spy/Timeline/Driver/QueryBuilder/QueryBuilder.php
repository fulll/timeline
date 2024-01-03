<?php

namespace tests\units\Spy\Timeline\Driver\QueryBuilder;

use Spy\Timeline\Driver\QueryBuilder\Criteria\CriteriaInterface;
use Spy\Timeline\Model\ComponentInterface;
use Spy\Timeline\Driver\ActionManagerInterface;
require_once __DIR__.'/../../../../../../vendor/autoload.php';

use atoum\atoum\test;
use Spy\Timeline\Driver\QueryBuilder\Criteria\Asserter;
use Spy\Timeline\Driver\QueryBuilder\Criteria\Operator;
use Spy\Timeline\Driver\QueryBuilder\QueryBuilder as QueryBuilderTested;
use Spy\Timeline\Driver\QueryBuilder\QueryBuilderFactory;

class QueryBuilder extends test
{
    public function testLogicalAnd(): void
    {
        $this->if($qb = new QueryBuilderTested())
            ->exception(static function () use ($qb): void {
                $qb->logicalAnd();
            })
            ->isInstanceOf('\InvalidArgumentException')
            ->hasMessage(QueryBuilderTested::class . '::createNewOperator accept minimum 2 arguments')
            // add criterias
            ->if($this->mockClass(CriteriaInterface::class, '\Mock'))
            ->and($criteria = new \Mock\CriteriaInterface())
            ->and($criteria2 = new \Mock\CriteriaInterface())
            ->and($resultExpected = new Operator())
            ->and($resultExpected->setType(Operator::TYPE_AND))
            ->and($resultExpected->setCriterias([$criteria, $criteria2]))
            ->object($qb->logicalAnd($criteria, $criteria2))
            ->isEqualTo($qb->createNewOperator(Operator::TYPE_AND, [$criteria, $criteria2]))
            ->isEqualTo($resultExpected)
        ;
    }

    public function testLogicalOr(): void
    {
        $this->if($qb = new QueryBuilderTested())
            ->exception(static function () use ($qb): void {
                $qb->logicalOr();
            })
            ->isInstanceOf('\InvalidArgumentException')
            ->hasMessage(QueryBuilderTested::class . '::createNewOperator accept minimum 2 arguments')
            // add criterias
            ->if($this->mockClass(CriteriaInterface::class, '\Mock'))
            ->and($criteria = new \Mock\CriteriaInterface())
            ->and($criteria2 = new \Mock\CriteriaInterface())
            ->and($resultExpected = new Operator())
            ->and($resultExpected->setType(Operator::TYPE_OR))
            ->and($resultExpected->setCriterias([$criteria, $criteria2]))
            ->object($qb->logicalOr($criteria, $criteria2))
            ->isEqualTo($qb->createNewOperator(Operator::TYPE_OR, [$criteria, $criteria2]))
            ->isEqualTo($resultExpected)
        ;
    }

    public function testField(): void
    {
        $this->if($qb = new QueryBuilderTested())
            ->exception(static function () use ($qb): void {
                $qb->field('unknownfield');
            })
                ->isInstanceOf('\InvalidArgumentException')
                ->hasMessage('Field "unknownfield" not supported, prefer: context, createdAt, verb, type, text, model, identifier')
            // real field
            ->and($resultExpected = new Asserter())
            ->and($resultExpected->field('createdAt'))
            ->object($qb->field('createdAt'))
            ->isEqualTo($resultExpected)
        ;
    }

    public function testOrderBy(): void
    {
        $this->if($qb = new QueryBuilderTested())
            ->exception(static function () use ($qb): void {
                $qb->orderBy('unknownfield', 'ASC');
            })
                ->isInstanceOf('\InvalidArgumentException')
                ->hasMessage('Field "unknownfield" not supported, prefer: context, createdAt, verb, type, text, model, identifier')
            // bad order
            ->exception(static function () use ($qb): void {
                $qb->orderBy('createdAt', 'badorder');
            })
                ->isInstanceOf('\InvalidArgumentException')
                ->hasMessage('Order "badorder" not supported, prefer: ASC or DESC')
        ;
    }

    public function testGetAvailableFields(): void
    {
        $this->if($qb = new QueryBuilderTested())
            ->array($qb->getAvailableFields())
            ->isNotEmpty()
        ;
    }

    public function testAddSubject(): void
    {
        $this->if($qb = new QueryBuilderTested())
            ->and($this->mockClass(ComponentInterface::class, '\Mock'))
            ->and($subject  = new \Mock\ComponentInterface())
            ->and($subject->getMockController()->getHash = 'hash')
            ->and($qb->addSubject($subject))

            ->array($qb->getSubjects())->hasSize(1)

            ->and($subject2  = new \Mock\ComponentInterface())
            ->and($subject2->getMockController()->getHash = 'hash')
            ->and($qb->addSubject($subject2))

            ->and($subject3  = new \Mock\ComponentInterface())
            ->and($subject3->getMockController()->getHash = 'hash2')
            ->and($qb->addSubject($subject3))

            ->array($qb->getSubjects())->hasSize(2)
        ;
    }

    public function testFromArray(): void
    {
        $this->if($this->mockClass(QueryBuilderFactory::class, '\Mock'))
            ->and($this->mockClass(CriteriaInterface::class, '\Mock'))
            ->and($this->mockClass(ActionManagerInterface::class, '\Mock'))
            ->and($this->mockClass(ComponentInterface::class, '\Mock'))
            ->and($criteria = new \Mock\CriteriaInterface())
            ->and($factory = new \Mock\QueryBuilderFactory())
            ->and($factory->getMockController()->createAsserterFromArray = $criteria)
            ->and($component = new \Mock\ComponentInterface())
            ->and($actionManager = new \Mock\ActionManagerInterface())
            ->and($actionManager->getMockController()->findComponents = [$component])
            ->and($qb = new QueryBuilderTested($factory))
            ->and($data = ['subject' => ['hash'], 'page' => 10, 'max_per_page' => 100, 'sort' => ['createdAt', 'DESC'], 'criterias' => ['type' => 'expr']])
            ->and($resultExpected = new QueryBuilderTested($factory))
            ->and($resultExpected->setPage(10))
            ->and($resultExpected->setMaxPerPage(100))
            ->and($resultExpected->orderBy('createdAt', 'DESC'))
            ->and($resultExpected->setCriterias($criteria))
            ->and($resultExpected->addSubject($component))
            ->object($data = $qb->fromArray($data, $actionManager))
            ->isEqualTo($resultExpected)
        ;
    }

    public function testToArray(): void
    {
        $this->if($qb = new QueryBuilderTested())
            ->and($this->mockClass(CriteriaInterface::class, '\Mock'))
            ->and($this->mockClass(ComponentInterface::class, '\Mock'))
            ->and($subject  = new \Mock\ComponentInterface())
            ->and($subject->getMockController()->getHash = 'hash')
            ->and($criteria = new \Mock\CriteriaInterface())
            ->and($criteria->getMockController()->toArray = 'TOARRAYRESULT')
            ->and($qb->setCriterias($criteria))
            ->and($qb->setPage(10))
            ->and($qb->setMaxPerPage(100))
            ->and($qb->orderBy('createdAt', 'DESC'))
            ->and($qb->addSubject($subject))
            ->array($qb->toArray())
            ->isIdenticalTo(
                ['subject' => ['hash'], 'page' => 10, 'max_per_page' => 100, 'criterias' => 'TOARRAYRESULT', 'sort' => ['createdAt', 'DESC']]
            )
        ;
    }
}
