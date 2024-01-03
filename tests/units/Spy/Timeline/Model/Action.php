<?php

namespace tests\units\Spy\Timeline\Model;

use Spy\Timeline\Model\ActionComponent;
use Spy\Timeline\Model\ComponentInterface;
require_once __DIR__.'/../../../../../vendor/autoload.php';

use atoum\atoum\test;
use Spy\Timeline\Model\Action as TestedModel;
use Spy\Timeline\Model\ActionInterface;

class Action extends test
{
    public function testConstruct(): void
    {
        $this->if($object = new TestedModel())
            ->object($object->getCreatedAt())->isInstanceOf('\DateTime')
            ->boolean($object->isDuplicated())->isFalse()
            ->array($object->getActionComponents())->isEmpty()
            ->string($object->getStatusCurrent())->isEqualTo(ActionInterface::STATUS_PENDING)
            ->string($object->getStatusWanted())->isEqualTo(ActionInterface::STATUS_PUBLISHED)
        ;
    }

    public function testAddComponent(): void
    {
        $this->if($action = new TestedModel())
            ->exception(static function () use ($action): void {
                $action->addComponent('subject', new \stdClass(), '\\' . ActionComponent::class);
            })
            ->isInstanceOf('\InvalidArgumentException')
            ->hasMessage('Component has to be a ComponentInterface or a scalar')
            // scalar
            ->when($action->addComponent('cod', 'chuckNorris', '\\' . ActionComponent::class))
            ->string($action->getComponent('cod'))->isEqualTo('chuckNorris')
            // componentInterface
            ->if($this->mockClass('\\' . ComponentInterface::class, '\Mock'))
            ->and($component = new \Mock\ComponentInterface())
            ->when($action->addComponent('coi', $component, '\\' . ActionComponent::class))
            ->object($action->getComponent('coi'))->isIdenticalTo($component)
            // two times same componen
            ->when($action->addComponent('coi', 'text', '\\' . ActionComponent::class))
            ->integer(is_countable($action->getActionComponents()) ? count($action->getActionComponents()) : 0)->isEqualTo(2)
        ;
    }

    public function testIsPublished(): void
    {
        $this->if($action = new TestedModel())
            ->boolean($action->isPublished())->isFalse()
            ->when($action->setStatusCurrent(TestedModel::STATUS_PUBLISHED))
            ->boolean($action->isPublished())->isTrue()
        ;
    }

    public function testHasDuplicateKey(): void
    {
        $this->if($action = new TestedModel())
            ->boolean($action->hasDuplicateKey())->isFalse()
            ->when($action->setDuplicateKey(uniqid()))
            ->boolean($action->hasDuplicateKey())->isTrue()
        ;
    }

    public function testGetValidStatus(): void
    {
        $this->if($object = new TestedModel())
            ->array($object->getValidStatus())
            ->isNotEmpty()
        ;
    }

    public function testIsValidStatus(): void
    {
        $this->if($object = new TestedModel())
            ->boolean($object->isValidStatus(TestedModel::STATUS_PENDING))->isTrue()
            ->boolean($object->isValidStatus(TestedModel::STATUS_PUBLISHED))->isTrue()
            ->boolean($object->isValidStatus(TestedModel::STATUS_FROZEN))->isTrue()
            ->boolean($object->isValidStatus('custom_status'))->isFalse()
        ;
    }

    public function testGetComponent(): void
    {
        // this is almost the same test than addComponent
        $this->if($action = new TestedModel())
            ->variable($action->getComponent('complement'))->isNull()
            // scalar
            ->when($action->addComponent('cod', 'chuckNorris', '\\' . ActionComponent::class))
            ->string($action->getComponent('cod'))->isEqualTo('chuckNorris')
            // componentInterface
            ->if($this->mockClass('\\' . ComponentInterface::class, '\Mock'))
            ->and($component = new \Mock\ComponentInterface())
            ->when($action->addComponent('coi', $component, '\\' . ActionComponent::class))
            ->object($action->getComponent('coi'))->isIdenticalTo($component)
        ;
    }

    public function testGetSubject(): void
    {
        // this is almost the same test than getComponent
        $this->if($action = new TestedModel())
            ->variable($action->getComponent('subject'))->isNull()
            // scalar
            ->when($action->addComponent('subject', 'chuckNorris', '\\' . ActionComponent::class))
            ->string($action->getComponent('subject'))->isEqualTo('chuckNorris')
            // componentInterface
            ->if($this->mockClass('\\' . ComponentInterface::class, '\Mock'))
            ->and($component = new \Mock\ComponentInterface())
            ->when($action->addComponent('subject', $component, '\\' . ActionComponent::class))
            ->object($action->getComponent('subject'))->isIdenticalTo($component)
        ;
    }
}
