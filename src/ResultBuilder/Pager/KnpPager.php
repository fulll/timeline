<?php

namespace Spy\Timeline\ResultBuilder\Pager;

use Knp\Component\Pager\PaginatorInterface;

class KnpPager extends AbstractPager implements PagerInterface, \IteratorAggregate, \Countable
{
    protected int $page;
    protected array $data;

    public function __construct(
        protected ?PaginatorInterface $paginator = null
    ) {
    }

    public function paginate(mixed $target, int $page = 1, int $limit = 10): static
    {
        if (null === $this->paginator) {
            throw new \LogicException(sprintf('Knp\Component\Pager\Paginator not injected in constructor of %s', __CLASS__));
        }

        $this->page  = $page;
        $this->pager = $this->paginator->paginate($target, $page, $limit, array('distinct' => true));
        $this->data  = $this->pager->getPaginationData();

        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLastPage(): int
    {
        return $this->data['last'];
    }

    public function haveToPaginate(): bool
    {
        return $this->getLastPage() > 1;
    }

    public function getNbResults(): int
    {
        return $this->data['totalCount'];
    }

    public function setItems(array $items): void
    {
        if (!$this->pager) {
            throw new \Exception('Paginate before set items');
        }

        $this->pager->setItems($items);
    }

    public function getIterator(): \Traversable
    {
        return $this->pager;
    }

    public function count(): int
    {
        return $this->data['currentItemCount'];
    }
}
