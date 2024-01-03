<?php

namespace Spy\Timeline\ResultBuilder;

use Spy\Timeline\Filter\FilterManagerInterface;
use Spy\Timeline\ResultBuilder\Pager\PagerInterface;
use Spy\Timeline\ResultBuilder\QueryExecutor\QueryExecutorInterface;

class ResultBuilder implements ResultBuilderInterface
{
    /**
     * @var PagerInterface
     */
    protected $pager;

    public function __construct(protected QueryExecutorInterface $queryExecutor, protected FilterManagerInterface $filterManager)
    {
    }

    /**
     * @param PagerInterface $pager pager
     */
    public function setPager(PagerInterface $pager): void
    {
        $this->pager = $pager;
    }

    /**
     * @param mixed   $query      target
     * @param int     $page       page
     * @param int     $maxPerPage maxPerPage
     * @param boolean $filter     filter
     * @param boolean $paginate   paginate
     *
     * @throws \Exception
     * @return \Traversable
     */
    public function fetchResults(mixed $query, $page = 1, $maxPerPage = 10, $filter = false, $paginate = false)
    {
        if ($paginate) {
            if (!$this->pager) {
                throw new \Exception('Please inject a pager on ResultBuilder');
            }

            $results = $this->pager->paginate($query, $page, $maxPerPage);
        } else {
            $results = $this->queryExecutor->fetch($query, $page, $maxPerPage);
        }

        if ($filter) {
            return $this->filterManager->filter($results);
        }

        return $results;
    }
}
