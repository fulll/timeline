<?php

namespace tests\units\Spy\Timeline\ResolveComponent\ValueObject;

use Spy\Timeline\Exception\ResolveComponentDataException;
require_once __DIR__.'/../../../../../../vendor/autoload.php';

use atoum\atoum\test;
use Spy\Timeline\ResolveComponent\TestHelper\User;
use Spy\Timeline\ResolveComponent\ValueObject\ResolveComponentModelIdentifier as TestedModel;

class ResolveComponentModelIdentifier extends test
{
    public function testStringModelEmptyIdentifierThrowsException(): void
    {
        $this->exception(static function (): void {
            new TestedModel('user');
        })
            ->isInstanceOf(ResolveComponentDataException::class)
            ->hasMessage('Model has to be an object or (a scalar + an identifier in 2nd argument)')
        ;
    }

    public function testEmptyModelThrowsException(): void
    {
        $this->exception(static function (): void {
            new TestedModel('');
        })
            ->isInstanceOf(ResolveComponentDataException::class)
            ->hasMessage('Model has to be an object or (a scalar + an identifier in 2nd argument)')
        ;
    }

    public function testObjectModelWithIdentifierGivenReturnsNullAsIdentifier(): void
    {
        $model = new \stdClass();

        $this->when($object = new TestedModel($model, 5))
            ->variable($object->getIdentifier())->isNull()
        ;
    }

    public function testObjectWithNoIdentifierReturnsObjectAndNullAsIdentifier(): void
    {
        $model = new User('5');

        $this->when($object = new TestedModel($model, 5))
            ->variable($object->getModel())->isIdenticalTo($model)
            ->variable($object->getIdentifier())->isNull()
        ;
    }

    public function testArrayIdentifier(): void
    {
        $identifier = ['foo' => 5, 'bar' => 'baz'];
        $this->when($object = new TestedModel('user', $identifier))
            ->variable($object->getIdentifier())->isIdenticalTo($identifier)
        ;
    }

    public function testIdentifierCanBeIntegerZero(): void
    {
        $this->when($object = new TestedModel('user', 0))
            ->integer($object->getIdentifier())->isZero()
        ;
    }

    public function testIdentifierCanBeStringZero(): void
    {
        $this->when($object = new TestedModel('user', '0'))
            ->string($object->getIdentifier())->isEqualTo('0')
        ;
    }
}
