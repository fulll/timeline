<?php

namespace Spy\Timeline\Driver\Redis\Pager;

abstract class AbstractPager
{
    /**
     * @param string $prefix prefix
     */
    public function __construct(protected object $client, protected string $prefix)
    {
    }

    /**
     * @param array $ids ids
     *
     * @return array
     */
    public function findActionsForIds(array $ids)
    {
        if ($ids === []) {
            return [];
        }

        $datas = $this->client->hmget($this->getActionKey(), $ids);

        return array_values(
            array_map(
                static fn ($v): mixed => unserialize($v),
                $datas
            )
        );
    }

    protected function getActionKey(): string
    {
        return sprintf('%s:action', $this->prefix);
    }
}
