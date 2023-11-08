<?php

namespace tests\units\Spy\Timeline\Model;

require_once __DIR__.'/../../../../../vendor/autoload.php';

use atoum\atoum\test;
use Spy\Timeline\Model\ActionComponent as TestedModel;

class ActionComponent extends test
{
    public function testIsText()
    {
        $this->if($object = new TestedModel())
            ->boolean($object->isText())->isFalse()
            ->and($object->setText('text'))
            ->boolean($object->isText())->isTrue()
        ;
    }
}
