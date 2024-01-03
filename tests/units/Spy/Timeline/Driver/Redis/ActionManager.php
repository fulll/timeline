<?php

namespace Spy\Timeline\Tests\Units\Driver\Redis;

use Spy\Timeline\ResultBuilder\ResultBuilderInterface;
use Spy\Timeline\ResolveComponent\ComponentDataResolverInterface;
use mock\StdClass;
use Spy\Timeline\Model\Action;
use Spy\Timeline\Model\Component;
use Spy\Timeline\Model\ActionComponent;
require_once __DIR__.'/../../../../../../vendor/autoload.php';

use atoum\atoum\test;
use Spy\Timeline\Driver\Redis\ActionManager as TestedModel;
use Spy\Timeline\ResolveComponent\ValueObject\ResolveComponentModelIdentifier;
use Spy\Timeline\ResolveComponent\ValueObject\ResolvedComponentData;

class ActionManager extends test
{
    public function testFindOrCreateComponent(): void
    {
        $model = 'user';
        $identifier = ['foo' => 'bar', 'baz' => 'baz'];
        $resolve = new ResolveComponentModelIdentifier($model, $identifier);

        $this
            //mocks
            ->if($this->mockClass(ResultBuilderInterface::class, '\Mock'))
            ->and($this->mockClass(ComponentDataResolverInterface::class, '\Mock'))

            ->and($redis = new StdClass())
            ->and($resultBuilder = new \mock\ResultBuilderInterface())
            ->and($componentDataResolver = new \mock\ComponentDataResolverInterface())
            ->and($actionClass = Action::class)
            ->and($componentClass = Component::class)
            ->and($actionComponentClass = ActionComponent::class)
            ->and($this->calling($componentDataResolver)->resolveComponentData = static fn (): ResolvedComponentData => new ResolvedComponentData($model, $identifier))
            ->and($object = new TestedModel($redis, $resultBuilder, 'foo', $actionClass, $componentClass, $actionComponentClass))

            ->and($object->setComponentDataResolver($componentDataResolver))
            ->when($result = $object->findOrCreateComponent($model, $identifier))
            ->then(
                $this->mock($componentDataResolver)->call('resolveComponentData')->withArguments($resolve)->exactly(1)
                ->string($result->getModel())->isEqualTo($model)
                ->array($result->getIdentifier())->isEqualTo($identifier)
            )
        ;
    }
}
