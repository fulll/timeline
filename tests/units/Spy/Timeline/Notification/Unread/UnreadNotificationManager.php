<?php

namespace tests\units\Spy\Timeline\Notification\Unread;

use Spy\Timeline\Driver\TimelineManagerInterface;
use Spy\Timeline\Model\ActionInterface;
use Spy\Timeline\Model\ComponentInterface;
require_once __DIR__.'/../../../../../../vendor/autoload.php';

use atoum\atoum\test;
use Spy\Timeline\Notification\Unread\UnreadNotificationManager as TestedModel;
use Spy\Timeline\Spread\Entry\Entry;
use Spy\Timeline\Spread\Entry\EntryCollection;

class UnreadNotificationManager extends test
{
    public function testNotify(): void
    {
        $this->if($this->mockClass('\\' . TimelineManagerInterface::class, '\Mock'))
            ->and($this->mockClass('\\' . ActionInterface::class, '\Mock'))
            ->and($this->mockClass('\\' . ComponentInterface::class, '\Mock'))
            ->and($manager = new \Mock\TimelineManagerInterface())
            ->and($notifier = new TestedModel($manager))
            ->and($action = new \Mock\ActionInterface())
            ->and($ec = new EntryCollection())
            ->when($notifier->notify($action, $ec))
                ->mock($manager)
                    ->call('createAndPersist')
                    ->never()
                ->mock($manager)
                    ->call('flush')
                    ->never()
            ->and($component = new \Mock\ComponentInterface())
            ->and($component->getMockController()->getModel = 'User')
            ->and($component->getMockController()->getIdentifier  = '1337')
            ->and($ec->add(new Entry($component)))
            ->when($notifier->notify($action, $ec))
                ->mock($manager)
                    ->call('createAndPersist')
                        ->withArguments($action, $component, 'GLOBAL', 'notification')
                    ->once()
                ->mock($manager)
                    ->call('flush')
                    ->once()
        ;
    }

    public function testGetUnreadNotifications(): void
    {
        $this->if($this->mockClass('\\' . TimelineManagerInterface::class, '\Mock'))
            ->and($this->mockClass('\\' . ComponentInterface::class, '\Mock'))
            ->and($manager = new \Mock\TimelineManagerInterface())
            ->and($notifier = new TestedModel($manager))
            ->and($component = new \Mock\ComponentInterface())
            ->and($options = ['page' => 1])
            ->when($notifier->getUnreadNotifications($component, 'CONTEXT', $options))
                ->mock($manager)
                    ->call('getTimeline')
                    ->withArguments($component, array_merge($options, ['context' => 'CONTEXT', 'type' => 'notification']))
                    ->once()
        ;
    }

    public function testCountKeys(): void
    {
        $this->if($this->mockClass('\\' . TimelineManagerInterface::class, '\Mock'))
            ->and($this->mockClass('\\' . ComponentInterface::class, '\Mock'))
            ->and($manager = new \Mock\TimelineManagerInterface())
            ->and($notifier = new TestedModel($manager))
            ->and($component = new \Mock\ComponentInterface())
            ->when($notifier->countKeys($component, 'CONTEXT'))
                ->mock($manager)
                    ->call('countKeys')
                    ->withArguments($component, ['context' => 'CONTEXT', 'type' => 'notification'])
                    ->once()
        ;
    }

    public function testMarkAsReadAction(): void
    {
        $this->if($this->mockClass('\\' . ComponentInterface::class, '\Mock'))
            ->and($this->mockGenerator()->orphanize('__construct'))
            ->and($this->mockClass('\\' . TestedModel::class, '\Mock'))
            ->and($notifier = new \Mock\UnreadNotificationManager())
            ->and($notifier->getMockController()->markAsReadActions = null)
            ->and($component = new \Mock\ComponentInterface())
            ->when($notifier->markAsReadAction($component, 1, 'CONTEXT'))
            ->mock($notifier)
                ->call('markAsReadActions')
                ->withArguments([['CONTEXT', $component, 1]])
                ->once()
        ;
    }

    public function testMarkAsReadActions(): void
    {
        $this->if($this->mockClass('\\' . TimelineManagerInterface::class, '\Mock'))
            ->and($this->mockClass('\\' . ComponentInterface::class, '\Mock'))
            ->and($manager = new \Mock\TimelineManagerInterface())
            ->and($notifier = new TestedModel($manager))
            ->and($component = new \Mock\ComponentInterface())
            ->and($component2 = new \Mock\ComponentInterface())
            ->when($notifier->markAsReadActions([['CONTEXT', $component, 1], ['CONTEXT', $component2, 2]]))
            ->mock($manager)
                ->call('remove')
                    ->withArguments($component, '1', ['type' =>  'notification', 'context' => 'CONTEXT'])
                ->once()
                ->call('remove')
                    ->withArguments($component2, '2', ['type' =>  'notification', 'context' => 'CONTEXT'])
                ->once()
                ->call('flush')
                ->once()
        ;
    }

    public function testMarkAllAsRead(): void
    {
        $this->if($this->mockClass('\\' . TimelineManagerInterface::class, '\Mock'))
            ->and($this->mockClass('\\' . ComponentInterface::class, '\Mock'))
            ->and($manager = new \Mock\TimelineManagerInterface())
            ->and($notifier = new TestedModel($manager))
            ->and($component = new \Mock\ComponentInterface())
            ->when($notifier->markAllAsRead($component, 'CONTEXT'))
                ->mock($manager)
                    ->call('removeAll')
                        ->withArguments($component, ['context' => 'CONTEXT', 'type' => 'notification'])
                    ->once()
                    ->call('flush')
                    ->once()
        ;
    }
}
