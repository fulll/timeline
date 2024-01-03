<?php

namespace Spy\Timeline\Filter;

abstract class AbstractFilter
{
    /**
     * @var integer
     */
    protected $priority = 255;

    public function setPriority(mixed $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @return integer
     */
    public function getPriority()
    {
        return $this->priority;
    }
}
