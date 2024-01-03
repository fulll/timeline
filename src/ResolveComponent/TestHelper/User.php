<?php

namespace Spy\Timeline\ResolveComponent\TestHelper;

/**
 * User object with get id method.
 */
class User
{
    /**
     * @param $id
     */
    public function __construct(protected $id)
    {
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
