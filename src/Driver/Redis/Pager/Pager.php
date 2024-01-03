<?php

namespace Spy\Timeline\Driver\Redis\Pager;

use Spy\Timeline\ResultBuilder\Pager\PagerInterface;

class Pager extends AbstractPager implements PagerInterface, \IteratorAggregate, \Countable, \ArrayAccess
{
    public $client;

    /**
     * @var mixed[]|mixed
     */
    public $items;

    public $nbResults;

    /**
     * @var int|mixed
     */
    public $lastPage;

    /**
     * @var integer
     */
    protected $page;

    /**
     * {@inheritdoc}
     */
    public function paginate($target, int $page = 1, $limit = 10, $options = [])
    {
        if (!$target instanceof PagerToken) {
            throw new \Exception('Not supported, must give a PagerToken');
        }

        $offset = ($page - 1) * $limit;
        --$limit; // due to redis

        $ids = $this->client->zRevRange($target->key, $offset, ($offset + $limit));

        $this->page = $page;
        $this->items = $this->findActionsForIds($ids);
        $this->nbResults = $this->client->zCard($target->key);
        $this->lastPage  = (int) ceil($this->nbResults / ($limit + 1));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastPage()
    {
        return $this->lastPage;
    }

    /**
     * {@inheritdoc}
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * {@inheritdoc}
     */
    public function haveToPaginate()
    {
        return $this->getLastPage() > 1;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        return $this->nbResults;
    }

    /**
     * @param array $items items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        return is_countable($this->items) ? count($this->items) : 0;
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
