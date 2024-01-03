<?php

namespace Spy\Timeline\Spread\Entry;

use Spy\Timeline\Model\ComponentInterface;

class Entry implements EntryInterface
{
    /**
     * @param ComponentInterface $subject subject
     */
    public function __construct(protected ComponentInterface $subject)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getIdent()
    {
        return $this->subject->getHash();
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject(): ComponentInterface
    {
        return $this->subject;
    }
}
