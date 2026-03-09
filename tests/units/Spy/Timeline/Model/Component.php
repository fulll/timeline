<?php

namespace tests\units\Spy\Timeline\Model;

require_once __DIR__.'/../../../../../vendor/autoload.php';

use atoum\atoum\test;
use Spy\Timeline\Model\Component as TestedModel;

class Component extends test
{
    public function testBuildHash()
    {
        $this->if($component = new TestedModel())
            ->and($component->setModel('chuck'))
            ->and($component->setIdentifier('norris'))
            ->when($component->buildHash()) // should be already called on setModel or setIdentifier
            ->string($component->getHash())->isEqualTo('chuck#s:6:"norris";')
            ->string($component->getHashMigrated())->isEqualTo('chuck##norris')
            ->and($component->setIdentifier(['norris', 'testa']))
            ->when($component->buildHash()) // should be already called on setModel or setIdentifier
            ->string($component->getHash())->isEqualTo('chuck#a:2:{i:0;s:6:"norris";i:1;s:5:"testa";}')
            ->string($component->getHashMigrated())->isEqualTo('chuck##norris')
            ->and($component->setIdentifier(['norris' => 'foo', 'testa' => 1]))
            ->when($component->buildHash()) // should be already called on setModel or setIdentifier
            ->string($component->getHash())->isEqualTo('chuck#a:2:{s:6:"norris";s:3:"foo";s:5:"testa";i:1;}')
            ->string($component->getHashMigrated())->isEqualTo('chuck##foo')
        ;
    }

    public function testCreateFromHash()
    {
        $this->if($component = new TestedModel())
            ->exception(function () use ($component) {
                $component->createFromHash('invalidhash');
            })
            ->isInstanceOf('\InvalidArgumentException')
            ->hasMessage('Invalid hash, must be formatted {model}#{hash or identifier}')
            // real hash
            // ->when(function () use ($component) {
            //     $component->createFromHash('model#id');
            // })
            // ->error()->exists()
            // ok
            ->when($component->createFromHash('model#s:5:"chuck";'))
            ->string($component->getModel())->isEqualTo('model')
            ->string($component->getIdentifier())->isEqualTo('chuck')
            // ok
            ->when($component->createFromHashMigrated('model##chuck'))
            ->string($component->getModel())->isEqualTo('model')
            ->string($component->getIdentifier())->isEqualTo('chuck')
            // composite
            ->when($component->createFromHash('model#a:2:{i:0;s:5:"chuck";i:1;s:5:"testa";}'))
            ->string($component->getModel())->isEqualTo('model')
            ->array($component->getIdentifier())->isEqualTo(array('chuck', 'testa'))
            // composite associative
            ->when($component->createFromHash('chuck#a:2:{s:6:"norris";s:3:"foo";s:5:"testa";i:1;}'))
            ->string($component->getModel())->isEqualTo('chuck')
            ->array($component->getIdentifier())->isEqualTo(['norris' => 'foo', 'testa' => 1])
        ;
    }

    public function testSetModel()
    {
        $this->if($component = new TestedModel())
            ->variable($component->getHash())->isNull()
            ->and($component->setModel('chuck'))
            ->variable($component->getHash())->isNull()
            // reinit object
            ->if($component = new TestedModel())
            ->and($component->setIdentifier('norris'))
            ->and($component->setModel('chuck'))
            ->string($component->getHash())->isEqualTo('chuck#s:6:"norris";')
            ->string($component->getHashMigrated())->isEqualTo('chuck##norris')
        ;
    }

    public function testSetIdentifier()
    {
        $this->if($component = new TestedModel())
            ->variable($component->getHash())->isNull()
            ->and($component->setIdentifier('norris'))
            ->variable($component->getHash())->isNull()
            // reinit object
            ->if($component = new TestedModel())
            ->and($component->setModel('chuck'))
            ->and($component->setIdentifier('norris'))
            ->string($component->getHash())->isEqualTo('chuck#s:6:"norris";')
            ->string($component->getHashMigrated())->isEqualTo('chuck##norris')
        ;
    }
}
