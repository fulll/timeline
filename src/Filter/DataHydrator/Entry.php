<?php

namespace Spy\Timeline\Filter\DataHydrator;

use Doctrine\Persistence\Proxy;
use Spy\Timeline\Model\ActionComponentInterface;
use Spy\Timeline\Model\ActionInterface;

class Entry
{
    private array $components = [];

    /**
     * @param ActionInterface $action action
     * @param string          $key    key
     */
    public function __construct(private readonly ActionInterface $action, protected $key)
    {
    }

    /**
     * Build references (subject, directComplement, indirectComplement)
     * of timeline action
     */
    public function build(): void
    {
        foreach ($this->action->getActionComponents() as $actionComponent) {
            if (!$actionComponent->isText()) {
                $this->buildComponent($actionComponent);
            }
        }
    }

    /**
     * @param ActionComponentInterface $actionComponent actionComponent
     */
    protected function buildComponent(ActionComponentInterface $actionComponent)
    {
        $component = $actionComponent->getComponent();
        if (!is_object($component)) {
            return;
        }

        $data      = $component->getData();

        if (null !== $data
            && (!$data instanceof Proxy || $data->isInitialized())
        ) {
            return;
        }

        $this->components[$component->getHash()] = $component;
    }

    /**
     * @return array<*,Reference>
     */
    public function getComponents(): array
    {
        return $this->components;
    }
}
