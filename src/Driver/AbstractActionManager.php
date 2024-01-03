<?php

namespace Spy\Timeline\Driver;

use Spy\Timeline\Model\ActionInterface;
use Spy\Timeline\Model\ComponentInterface;
use Spy\Timeline\ResolveComponent\ComponentDataResolverInterface;
use Spy\Timeline\ResolveComponent\ValueObject\ResolveComponentModelIdentifier;
use Spy\Timeline\ResolveComponent\ValueObject\ResolvedComponentData;
use Spy\Timeline\Spread\DeployerInterface;

abstract class AbstractActionManager implements ActionManagerInterface
{
    /**
     * @var DeployerInterface
     */
    protected $deployer;

    private ?ComponentDataResolverInterface $componentDataResolver = null;

    /**
     * @param string $actionClass          FQCN of the action class
     * @param string $componentClass       FQCN of the component class
     * @param string $actionComponentClass FQCN of the action component class
     */
    public function __construct(protected string $actionClass, protected string $componentClass, protected string $actionComponentClass)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function create($subject, $verb, array $components = [])
    {
        /** @var $action ActionInterface */
        $action = new $this->actionClass();
        $action->setVerb($verb);

        if (!$subject instanceof ComponentInterface && !is_object($subject)) {
            throw new \Exception('Subject must be a ComponentInterface or an object');
        }

        $components['subject'] = $subject;

        foreach ($components as $type => $component) {
            $this->addComponent($action, $type, $component);
        }

        return $action;
    }

    /**
     * @param  ActionInterface $action    action
     * @param  string          $type      type
     * @param  mixed           $component component
     * @throws \Exception
     */
    public function addComponent($action, $type, mixed $component)
    {
        if (!$component instanceof ComponentInterface && !is_scalar($component)) {
            $component = $this->findOrCreateComponent($component);

            if (null === $component) {
                throw new \Exception(sprintf('Impossible to create component from %s.', $type));
            }
        }

        $action->addComponent($type, $component, $this->actionComponentClass);
    }

    /**
     * @param ActionInterface $action action
     *
     * @return void
     */
    protected function deployActionDependOnDelivery(ActionInterface $action)
    {
        if (!$this->deployer) {
            return;
        }

        if (!$this->deployer->isDeliveryImmediate()) {
            return;
        }

        $this->deployer->deploy($action, $this);
    }

    /**
     * @param DeployerInterface $deployer deployer
     */
    public function setDeployer(DeployerInterface $deployer): void
    {
        $this->deployer = $deployer;
    }

    /**
     * Sets the component data resolver.
     */
    public function setComponentDataResolver(ComponentDataResolverInterface $componentDataResolver): void
    {
        $this->componentDataResolver = $componentDataResolver;
    }

    /**
     * Gets the component data resolver.
     *
     *
     * @throws \Exception When no component data resolver has been set
     */
    public function getComponentDataResolver(): ?ComponentDataResolverInterface
    {
        if (empty($this->componentDataResolver) || !$this->componentDataResolver instanceof ComponentDataResolverInterface) {
            throw new \Exception('Component data resolver not set');
        }

        return $this->componentDataResolver;
    }

    /**
     * Resolves the model and identifier.
     *
     * @param string|object     $model
     * @param null|string|array $identifier
     *
     * @return ResolvedComponentData
     */
    protected function resolveModelAndIdentifier($model, $identifier)
    {
        $resolve = new ResolveComponentModelIdentifier($model, $identifier);

        return $this->getComponentDataResolver()->resolveComponentData($resolve);
    }

    /**
     * Creates a new component object from the resolved data.
     *
     * @param ResolvedComponentData $resolved The resolved component data
     *
     * @return ComponentInterface The newly created and populated component
     */
    protected function getComponentFromResolvedComponentData(ResolvedComponentData $resolved)
    {
        /** @var $component ComponentInterface */
        $component = new $this->componentClass();
        $component->setModel($resolved->getModel());
        $component->setData($resolved->getData());
        $component->setIdentifier($resolved->getIdentifier());

        return $component;
    }
}
