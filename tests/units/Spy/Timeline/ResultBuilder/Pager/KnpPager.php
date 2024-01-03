<?php

namespace tests\units\Spy\Timeline\ResultBuilder\Pager;

use Spy\Timeline\Filter\FilterManagerInterface;
use Mock\SlidingPagination;
use Mock\Paginator;
require_once __DIR__.'/../../../../../../vendor/autoload.php';

use atoum\atoum\test;
use Spy\Timeline\ResultBuilder\Pager\KnpPager as TestedModel;

class KnpPager extends test
{
    public function testPaginate(): void
    {
        $this->if($this->mockClass('\Knp\Component\Pager\Paginator', '\Mock'))
            ->and($this->mockClass('\Knp\Component\Pager\Pagination\SlidingPagination', '\Mock'))
            ->and($this->mockClass('\\' . FilterManagerInterface::class, '\Mock'))
            ->and($pagination = new SlidingPagination())
            ->and($pagination->getMockController()->getPaginationData = ['last' => 2, 'totalCount' => 17, 'currentItemCount' => 10])
            ->and($paginator  = new Paginator())
            ->and($paginator->getMockController()->paginate = $pagination)
            ->and($pager = new TestedModel($paginator))
            ->when($pagination = $pager->paginate('target', 1, 10))
            ->mock($paginator)
                ->call('paginate')
                ->withArguments('target', 1, 10)
                ->once()
            ->integer($pagination->getLastPage())->isEqualTo(2)
            ->boolean($pagination->haveToPaginate())->isTrue()
            ->integer($pagination->getNbResults())->isEqualTo(17)
            ->integer(is_countable($pagination) ? count($pagination) : 0)->isEqualTo(10)
        ;
    }
}
