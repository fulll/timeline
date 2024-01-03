<?php

namespace Spy\Timeline\Spread;

use Spy\Timeline\Driver\ActionManagerInterface;
use Spy\Timeline\Model\ActionInterface;
use Spy\Timeline\Notification\NotifierInterface;

interface DeployerInterface
{
    /**
     * @var string
     */
    public const DELIVERY_IMMEDIATE = 'immediate';


    /**
     * @var string
     */
    public const DELIVERY_WAIT      = 'wait';

    /**
     * @param ActionInterface        $action        action
     * @param ActionManagerInterface $actionManager actionManager
     */
    public function deploy(ActionInterface $action, ActionManagerInterface $actionManager);

    /**
     * @param string $delivery delivery
     */
    public function setDelivery(string $delivery);

    /**
     * @return boolean
     */
    public function isDeliveryImmediate();

    /**
     * @param SpreadInterface $spread spread
     */
    public function addSpread(SpreadInterface $spread);

    /**
     * @param NotifierInterface $notifier notifier
     */
    public function addNotifier(NotifierInterface $notifier);

    /**
     * @return \ArrayIterator of SpreadInterface
     */
    public function getSpreads();
}
