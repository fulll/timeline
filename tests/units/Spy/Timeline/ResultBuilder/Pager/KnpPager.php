<?php

namespace tests\units\Spy\Timeline\ResultBuilder\Pager;

require_once __DIR__.'/../../../../../../vendor/autoload.php';

use atoum\atoum\test;
use Spy\Timeline\ResultBuilder\Pager\KnpPager as TestedModel;

class KnpPager extends test
{
    public function testPaginate()
    {
        $this->if($pagination = new \mock\Knp\Component\Pager\Pagination\AbstractPagination())
            ->and($pagination->getMockController()->getPaginationData = array(
                'last' => 2,
                'totalCount' => 17,
                'currentItemCount' => 10,
            ))
            ->and($paginator = new \mock\Knp\Component\Pager\PaginatorInterface())
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
            ->integer(count($pagination))->isEqualTo(10)
        ;
    }
}
