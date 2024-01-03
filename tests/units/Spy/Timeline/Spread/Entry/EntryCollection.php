<?php

namespace tests\units\Spy\Timeline\Spread\Entry;

use Spy\Timeline\Spread\Entry\EntryInterface;
require_once __DIR__.'/../../../../../../vendor/autoload.php';

use atoum\atoum\test;
use Spy\Timeline\Spread\Entry\EntryCollection as TestedModel;

class EntryCollection extends test
{
    public function testAdd(): void
    {
        $this->if($collection = new TestedModel())
            ->and($this->mockClass('\\' . EntryInterface::class, '\Mock'))
            ->and($entry = new \Mock\EntryInterface())
            ->and($entry->getMockController()->getIdent = 'ident1')
            ->when($collection->add($entry, 'NOTGLOBAL'))
            ->object($collection->getIterator())
            ->isEqualTo(new \ArrayIterator(['NOTGLOBAL' => ['ident1' => $entry], 'GLOBAL' => ['ident1' => $entry]]))
            // send with global context
            ->and($entry2 = new \Mock\EntryInterface())
            ->and($entry2->getMockController()->getIdent = 'ident2')
            ->when($collection->add($entry2, 'GLOBAL'))
            ->object($collection->getIterator())
            ->isEqualTo(new \ArrayIterator(['NOTGLOBAL' => ['ident1' => $entry], 'GLOBAL' => ['ident1' => $entry, 'ident2' => $entry2]]))
            // not duplicate on global.
            ->and($collection->setDuplicateOnGlobal(false))
            ->and($entry3 = new \Mock\EntryInterface())
            ->and($entry3->getMockController()->getIdent = 'ident3')
            ->when($collection->add($entry3, 'OTHERCONTEXT'))
            ->object($collection->getIterator())
            ->isEqualTo(new \ArrayIterator(['OTHERCONTEXT' => ['ident3' => $entry3], 'NOTGLOBAL' => ['ident1' => $entry], 'GLOBAL' => ['ident1' => $entry, 'ident2' => $entry2]]))
        ;
    }

    public function testLoadUnawareEntries(): void
    {
    }

    public function testClear(): void
    {
        $this->if($collection = new TestedModel())
            ->and($this->mockClass('\\' . EntryInterface::class, '\Mock'))
            ->and($entry = new \Mock\EntryInterface())
            ->and($entry->getMockController()->getIdent = 'ident1')
            ->when($collection->add($entry, 'NOTGLOBAL'))
            ->object($collection->getIterator())
            ->isEqualTo(new \ArrayIterator(['NOTGLOBAL' => ['ident1' => $entry], 'GLOBAL' => ['ident1' => $entry]]))
            // send with global context
            ->when($collection->clear())
            ->object($collection->getIterator())
            ->isEqualTo(new \ArrayIterator([]))
        ;
    }
}
