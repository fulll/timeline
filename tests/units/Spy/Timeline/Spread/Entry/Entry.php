<?php

namespace tests\units\Spy\Timeline\Spread\Entry;

use Spy\Timeline\Model\ComponentInterface;
require_once __DIR__.'/../../../../../../vendor/autoload.php';

use atoum\atoum\test;
use Spy\Timeline\Spread\Entry\Entry as TestedModel;

class Entry extends test
{
    public function testGetIdent(): void
    {
        $this->if($this->mockClass('\\' . ComponentInterface::class, '\Mock'))
            ->and($component = new \Mock\ComponentInterface())
            ->and($component->getMockController()->getHash = 'myhash')
            ->and($entry = new TestedModel($component))
            ->string($entry->getIdent())->isEqualTo('myhash')
            ->mock($component)
                ->call('getHash')
                ->once()
        ;
    }
}
