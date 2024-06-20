<?php

namespace Spy\Timeline\Driver\Redis\Pager;

use Spy\Timeline\ResultBuilder\Pager\PagerInterface;

class Pager extends AbstractPager implements PagerInterface, \IteratorAggregate, \Countable, \ArrayAccess
{
    protected int $page;
    private array $items;
    private int $nbResults;
    private int $lastPage;

    public function paginate($target, int $page = 1, int $limit = 10, array $options = []): static
    {
        if (!$target instanceof PagerToken) {
            throw new \Exception('Not supported, must give a PagerToken');
        }

        $offset = ($page - 1) * $limit;
        $limit = $limit - 1; // due to redis

        $ids = $this->client->zRevRange($target->key, $offset, ($offset + $limit));

        $this->page = $page;
        $this->items = $this->findActionsForIds($ids);
        $this->nbResults = $this->client->zCard($target->key);
        $this->lastPage  = intval(ceil($this->nbResults / ($limit + 1)));

        return $this;
    }

    public function getLastPage(): int
    {
        return $this->lastPage;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function haveToPaginate(): bool
    {
        return $this->getLastPage() > 1;
    }

    public function getNbResults(): int
    {
        return $this->nbResults;
    }

    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }
}
