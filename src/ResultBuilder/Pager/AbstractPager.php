<?php

namespace Spy\Timeline\ResultBuilder\Pager;

abstract class AbstractPager implements \ArrayAccess
{
    /**
     * @var array
     */
    protected array $pager;

    /**
     * {@inheritdoc}
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->pager);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->pager[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        if (null === $offset) {
            $this->pager[] = $value;
        } else {
            $this->pager[$offset] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        unset($this->pager[$offset]);
    }
}
