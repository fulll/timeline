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
            ->string($component->getHash())->isEqualTo('chuck##norris')
            ->and($component->setIdentifier(['norris', 'testa']))
            ->when($component->buildHash()) // should be already called on setModel or setIdentifier
            ->string($component->getHash())->isEqualTo('chuck##norris')
            ->and($component->setIdentifier(['norris' => 'foo', 'testa' => 1]))
            ->when($component->buildHash()) // should be already called on setModel or setIdentifier
            ->string($component->getHash())->isEqualTo('chuck##foo')
        ;
    }

    public function testCreateFromHash()
    {
        $this->if($component = new TestedModel())
            ->exception(function () use ($component) {
                $component->createFromHash('invalidhash');
            })
            ->isInstanceOf('\InvalidArgumentException')
            ->hasMessage('Invalid hash, must be formatted {model}##{hash or identifier}')
            // real hash
            // ->when(function () use ($component) {
            //     $component->createFromHash('model#id');
            // })
            // ->error()->exists()
            // ok
            ->when($component->createFromHash('model##chuck'))
            ->string($component->getModel())->isEqualTo('model')
            ->string($component->getIdentifier())->isEqualTo('chuck')
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
            ->string($component->getHash())->isEqualTo('chuck##norris')
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
            ->string($component->getHash())->isEqualTo('chuck##norris')
        ;
    }
}
