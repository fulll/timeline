<?php

namespace tests\units\Spy\Timeline\Filter;

use Spy\Timeline\Filter\FilterInterface;
require_once __DIR__.'/../../../../../vendor/autoload.php';

use atoum\atoum\test;
use Spy\Timeline\Filter\FilterManager as TestedFilterManager;

class FilterManager extends test
{
    public function testException(): void
    {
        $this->if($manager = new TestedFilterManager())
            ->exception(static function () use ($manager): void {
                $manager->filter('scalar');
            })
            ->isInstanceof('\Exception')
            ->hasMessage('Collection must be an array or traversable')
        ;
    }

    public function testFilters(): void
    {
        $this->mockClass(FilterInterface::class, '\Mock');

        $this->if($manager = new TestedFilterManager())
            ->and($filter1 = new \Mock\FilterInterface())
            ->and($filter1->getMockController()->getPriority = 50)
            ->and($filter1->getMockController()->filter = static function ($collection) {
                $collection[] = 1;
                return $collection;
            })
            ->and($manager->add($filter1))
            ->and($filter2 = new \Mock\FilterInterface())
            ->and($filter2->getMockController()->getPriority = 20)
            ->and($filter2->getMockController()->filter = static function ($collection) {
                $collection[] = 2;
                return $collection;
            })
            ->and($manager->add($filter2))
                ->array($manager->filter([]))
                ->isIdenticalTo([2, 1])
            // change property of filter ...
            ->and($filter2->getMockController()->getPriority = 60)
            // not change because not re sorted.
            ->array($manager->filter([]))
                ->isIdenticalTo([2, 1])
            // add a filter
            ->and($filter3 = new \Mock\FilterInterface())
            ->and($filter3->getMockController()->getPriority = -20)
            ->and($filter3->getMockController()->filter = static function ($collection) {
                $collection[] = 3;
                return $collection;
            })
            ->and($manager->add($filter3))
            ->array($manager->filter([]))
                ->isIdenticalTo([3, 1, 2])
        ;
    }
}
