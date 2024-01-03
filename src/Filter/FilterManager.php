<?php

namespace Spy\Timeline\Filter;

class FilterManager implements FilterManagerInterface
{
    /**
     * @var FilterInterface[]
     */
    protected $filters = [];

    /**
     * @var boolean
     */
    protected $sorted = true;

    /**
     * @param FilterInterface $filter filter
     */
    public function add(FilterInterface $filter): void
    {
        $this->filters[] = $filter;
        $this->sorted    = false;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($collection)
    {
        if (!$this->sorted) {
            $this->sortFilters();
        }

        if (!is_array($collection) && !$collection instanceof \Traversable) {
            throw new \Exception('Collection must be an array or traversable');
        }

        foreach ($this->filters as $filter) {
            $collection = $filter->filter($collection);
        }

        return $collection;
    }

    /**
     * Sort filters by priority.
     */
    protected function sortFilters()
    {
        usort($this->filters, static function (FilterInterface $a, FilterInterface $b): int {
            $a = $a->getPriority();
            $b = $b->getPriority();
            return $a <=> $b;
        });

        $this->sorted = true;
    }
}
