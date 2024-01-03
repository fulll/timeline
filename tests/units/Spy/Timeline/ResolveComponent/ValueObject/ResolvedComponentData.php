<?php

namespace tests\units\Spy\Timeline\ResolveComponent\ValueObject;

use Spy\Timeline\Exception\ResolveComponentDataException;
require_once __DIR__.'/../../../../../../vendor/autoload.php';

use atoum\atoum\test;
use Spy\Timeline\ResolveComponent\ValueObject\ResolvedComponentData as TestedModel;

class ResolvedComponentData extends test
{
    public function testEmptyModelThrowsException(): void
    {
        $this->exception(static function (): void {
            new TestedModel('', 2);
        })
            ->isInstanceOf(ResolveComponentDataException::class)
            ->hasMessage('The resolved model can not be empty')
        ;
    }

    public function testArrayModelThrowsException(): void
    {
        $this->exception(static function (): void {
            new TestedModel(['foo'], 2);
        })
            ->isInstanceOf(ResolveComponentDataException::class)
            ->hasMessage('The resolved model has to be a string')
        ;
    }

    public function testObjectModelThrowsException(): void
    {
        $this->exception(static function (): void {
            new TestedModel(new \stdClass(), 2);
        })
            ->isInstanceOf(ResolveComponentDataException::class)
            ->hasMessage('The resolved model has to be a string')
        ;
    }

    public function testEmptyIdentifierThrowsException(): void
    {
        $invalidData = [null, ''];

        foreach ($invalidData as $invalid) {
            $this->exception(static function () use ($invalid): void {
                new TestedModel('user', $invalid);
            })
                ->isInstanceOf(ResolveComponentDataException::class)
                ->hasMessage('No resolved identifier given')
            ;
        }
    }

    public function testObjectAsIdentifierThrowsException(): void
    {
        $this->exception(static function (): void {
            new TestedModel('user', new \stdClass());
        })
            ->isInstanceOf(ResolveComponentDataException::class)
            ->hasMessage('Identifier has to be a scalar or an array')
        ;
    }

    public function testIntegerIdentifierReturnsAsString(): void
    {
        $this->if($action = new TestedModel('user', 1))
            ->string($action->getModel())->isEqualTo('user')
            ->string($action->getIdentifier())->isEqualTo('1')
        ;
    }

    public function testValidModelAndIdentifiersWhereIdentifierArray(): void
    {
        $stringModel = 'foo/bar/baz';
        $arrayIdentifier = ['foo' => 'bar', 'bar' => 5];

        $this->if($action = new TestedModel($stringModel, $arrayIdentifier))
            ->string($action->getModel())->isEqualTo($stringModel)
            ->array($action->getIdentifier())->isEqualTo($arrayIdentifier)
        ;
    }

    public function testSettingDataWorks(): void
    {
        $object = new \stdClass();
        $object->title = 'foo';

        $this->if($action = new TestedModel('user', 1, $object))
            ->string($action->getData()->title)->isEqualTo('foo')
        ;
    }

    public function testNoDataSetReturnsNull(): void
    {
        $this->if($action = new TestedModel('user', 1))
            ->variable($action->getData())->isNull()
        ;
    }

    public function testIdentifierCanBeZero(): void
    {
        $this->if($action = new TestedModel('user', '0'))
            ->string($action->getIdentifier())->isEqualTo('0')
        ;
    }
}
