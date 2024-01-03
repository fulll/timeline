<?php

namespace tests\units\Spy\Timeline\Filter;

use Spy\Timeline\Model\ActionInterface;
require_once __DIR__.'/../../../../../vendor/autoload.php';

use atoum\atoum\test;
use Spy\Timeline\Filter\DuplicateKey as TestedModel;

class DuplicateKey extends test
{
    public function testFilterNoDuplicateKey(): void
    {
        $this->if($filter = new TestedModel())
            ->and($this->mockClass('\\' . ActionInterface::class, '\Mock'))
            ->and($action1 = new \Mock\ActionInterface())
            ->and($action2 = new \Mock\ActionInterface())
            ->and($coll = [$action1, $action2])
            ->array($filter->filter($coll))
                ->hasSize(2)
                ->isIdenticalTo($coll)
            ->mock($action1)
                ->call('setIsDuplicated')
                ->never()
            ->mock($action2)
                ->call('setIsDuplicated')
                ->never()
        ;
    }

    public function testFilterOneDuplicateKey(): void
    {
        $this->if($filter = new TestedModel())
            ->and($this->mockClass('\\' . ActionInterface::class, '\Mock'))
            ->and($action1 = new \Mock\ActionInterface())
            ->and($action1->getMockController()->hasDuplicateKey = true)
            ->and($action1->getMockController()->getDuplicateKey = '123')
            ->and($action2 = new \Mock\ActionInterface())
            ->and($coll = [$action1, $action2])
            ->array($filter->filter($coll))
                ->hasSize(2)
                ->isIdenticalTo($coll)
            ->mock($action1)
                ->call('setIsDuplicated')
                ->never()
            ->mock($action2)
                ->call('setIsDuplicated')
                ->never()
        ;
    }

    public function testFilterTwoDuplicateKeyDifferent(): void
    {
        $this->if($filter = new TestedModel())
            ->and($this->mockClass('\\' . ActionInterface::class, '\Mock'))
            ->and($action1 = new \Mock\ActionInterface())
            ->and($action1->getMockController()->hasDuplicateKey = true)
            ->and($action1->getMockController()->getDuplicateKey = '123')
            ->and($action2 = new \Mock\ActionInterface())
            ->and($action2->getMockController()->hasDuplicateKey = true)
            ->and($action2->getMockController()->getDuplicateKey = '456')
            ->and($coll = [$action1, $action2])
            ->array($filter->filter($coll))
                ->hasSize(2)
                ->isIdenticalTo($coll)
            ->mock($action1)
                ->call('setIsDuplicated')
                ->never()
            ->mock($action2)
                ->call('setIsDuplicated')
                ->never()
        ;
    }

    public function testFilterTwoDuplicateKeyNoPriority(): void
    {
        $this->if($filter = new TestedModel())
            ->and($this->mockClass('\\' . ActionInterface::class, '\Mock'))
            ->and($action1 = new \Mock\ActionInterface())
            ->and($action1->getMockController()->hasDuplicateKey = true)
            ->and($action1->getMockController()->getDuplicateKey = '123')
            ->and($action2 = new \Mock\ActionInterface())
            ->and($action2->getMockController()->hasDuplicateKey = true)
            ->and($action2->getMockController()->getDuplicateKey = '123')
            ->and($coll = [$action1, $action2])
            ->array($filter->filter($coll))
                ->hasSize(1)
                ->isIdenticalTo([$action1])
            ->mock($action1)
                ->call('setIsDuplicated')
                ->once()
            ->mock($action2)
                ->call('setIsDuplicated')
                ->never()
        ;
    }

    public function testFilterTwoDuplicateKeyPriorityEquals(): void
    {
        $this->if($filter = new TestedModel())
            ->and($this->mockClass('\\' . ActionInterface::class, '\Mock'))
            ->and($action1 = new \Mock\ActionInterface())
            ->and($action1->getMockController()->hasDuplicateKey = true)
            ->and($action1->getMockController()->getDuplicateKey = '123')
            ->and($action1->getMockController()->getDuplicatePriority = 10)
            ->and($action2 = new \Mock\ActionInterface())
            ->and($action2->getMockController()->hasDuplicateKey = true)
            ->and($action2->getMockController()->getDuplicateKey = '123')
            ->and($action2->getMockController()->getDuplicatePriority = 10)
            ->and($coll = [$action1, $action2])
            ->array($filter->filter($coll))
                ->hasSize(1)
                ->isIdenticalTo([$action1])
            ->mock($action1)
                ->call('setIsDuplicated')
                ->once()
            ->mock($action2)
                ->call('setIsDuplicated')
                ->never()
        ;
    }

    public function testFilterTwoDuplicateKeyPriorityFirst(): void
    {
        $this->if($filter = new TestedModel())
            ->and($this->mockClass('\\' . ActionInterface::class, '\Mock'))
            ->and($action1 = new \Mock\ActionInterface())
            ->and($action1->getMockController()->hasDuplicateKey = true)
            ->and($action1->getMockController()->getDuplicateKey = '123')
            ->and($action1->getMockController()->getDuplicatePriority = 20)
            ->and($action2 = new \Mock\ActionInterface())
            ->and($action2->getMockController()->hasDuplicateKey = true)
            ->and($action2->getMockController()->getDuplicateKey = '123')
            ->and($action2->getMockController()->getDuplicatePriority = 10)
            ->and($coll = [$action1, $action2])
            ->array($filter->filter($coll))
                ->hasSize(1)
                ->isIdenticalTo([$action1])
            ->mock($action1)
                ->call('setIsDuplicated')
                ->once()
            ->mock($action2)
                ->call('setIsDuplicated')
                ->never()
        ;
    }

    public function testFilterTwoDuplicateKeyPrioritySecond(): void
    {
        $this->if($filter = new TestedModel())
            ->and($this->mockClass('\\' . ActionInterface::class, '\Mock'))
            ->and($action1 = new \Mock\ActionInterface())
            ->and($action1->getMockController()->hasDuplicateKey = true)
            ->and($action1->getMockController()->getDuplicateKey = '123')
            ->and($action1->getMockController()->getDuplicatePriority = 10)
            ->and($action2 = new \Mock\ActionInterface())
            ->and($action2->getMockController()->hasDuplicateKey = true)
            ->and($action2->getMockController()->getDuplicateKey = '123')
            ->and($action2->getMockController()->getDuplicatePriority = 20)
            ->and($coll = [$action1, $action2])
            ->array($filter->filter($coll))
                ->hasSize(1)
                ->isIdenticalTo([1 => $action2])
            ->mock($action1)
                ->call('setIsDuplicated')
                ->never()
            ->mock($action2)
                ->call('setIsDuplicated')
                ->once()
        ;
    }
}
