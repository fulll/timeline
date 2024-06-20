<?php

namespace Spy\Timeline\ResultBuilder\Pager;

use Knp\Component\Pager\Pagination\AbstractPagination;

abstract class AbstractPager implements \ArrayAccess
{
    protected ?AbstractPagination $pager = null;

    public function offsetExists($offset): bool
    {
        return $this->pager->offsetExists($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->pager->offsetGet($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->pager->offsetSet($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->pager->offsetUnset($offset);
    }
}
