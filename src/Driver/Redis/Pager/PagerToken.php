<?php

namespace Spy\Timeline\Driver\Redis\Pager;

class PagerToken
{
    /**
     * @param string $key key
     */
    public function __construct(public $key)
    {
    }
}
