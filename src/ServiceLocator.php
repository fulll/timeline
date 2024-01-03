<?php

namespace Spy\Timeline;

use Spy\Timeline\Filter\FilterManager;
use Spy\Timeline\Filter\DuplicateKey;
use Spy\Timeline\Filter\DataHydrator;
use Spy\Timeline\Notification\Unread\UnreadNotificationManager;
use Spy\Timeline\Driver\QueryBuilder\QueryBuilderFactory;
use Spy\Timeline\Driver\QueryBuilder\QueryBuilder;
use Spy\Timeline\Driver\QueryBuilder\Criteria\Asserter;
use Spy\Timeline\Driver\QueryBuilder\Criteria\Operator;
use Spy\Timeline\ResultBuilder\ResultBuilder;
use Spy\Timeline\Spread\Deployer;
use Spy\Timeline\Spread\Entry\EntryCollection;
use Spy\Timeline\Driver\Redis\TimelineManager;
use Spy\Timeline\Driver\Redis\ActionManager;
use Spy\Timeline\Driver\Redis\QueryExecutor;
use Spy\Timeline\Driver\Redis\Pager\Pager;
use Spy\Timeline\Model\Action;
use Spy\Timeline\Model\Component;
use Spy\Timeline\Model\ActionComponent;
use Spy\Timeline\ResolveComponent\BasicComponentDataResolver;
class ServiceLocator
{
    /**
     * @var \Pimple
     */
    protected $container;

    /**
     * Build container
     */
    public function __construct()
    {
        $this->buildContainer();
    }

    /**
     * Build default container.
     */
    public function buildContainer()
    {
        if (!class_exists('\Pimple')) {
            throw new \Exception('Please install Pimple.');
        }

        $c = new \Pimple();
        // ---- classes ----

        // filters
        $c['filter.manager.class']                   = FilterManager::class;
        $c['filter.duplicate_key.class']             = DuplicateKey::class;
        $c['filter.data_hydrator.class']             = DataHydrator::class;
        $c['filter.data_hydrator.filter_unresolved'] = false;

        // notifications
        $c['unread_notifications.class']             = UnreadNotificationManager::class;

        // query builder
        $c['query_builder.factory.class']            = QueryBuilderFactory::class;
        $c['query_builder.class']                    = QueryBuilder::class;
        $c['query_builder.asserter.class']           = Asserter::class;
        $c['query_builder.operator.class']           = Operator::class;

        // result builder
        $c['result_builder.class']                   = ResultBuilder::class;

        // deployer
        $c['spread.deployer.class']                  = Deployer::class;
        $c['spread.entry_collection.class']          = EntryCollection::class;
        $c['spread.on_subject']                      = true;
        $c['spread.on_global_context']               = true;
        $c['spread.batch_size']                      = 50;
        $c['spread.delivery']                        = 'immediate';

        // ---- services ----

        // filters
        $c['filter.manager'] = $c->share(static fn ($c): object => new $c['filter.manager.class']());

        $c['filter.duplicate_key'] = $c->share(static fn ($c): object => new $c['filter.duplicate_key.class']());

        $c['filter.data_hydrator'] = $c->share(static fn ($c): object => new $c['filter.data_hydrator.class'](
            $c['filter.data_hydrator.filter_unresolved']
        ));

        // notifications

        $c['unread_notifications'] = $c->share(static fn ($c): object => new $c['unread_notifications.class'](
            $c['timeline_manager']
        ));

        // query_builder

        $c['query_builder.factory'] = $c->share(static fn ($c): object => new $c['query_builder.factory.class'](
            $c['query_builder.class'],
            $c['query_builder.asserter.class'],
            $c['query_builder.operator.class']
        ));

        // result builder

        $c['result_builder'] = $c->share(static function ($c): object {
            $instance = new $c['result_builder.class'](
                $c['query_executor'],
                $c['filter.manager']
            );
            $instance->setPager($c['pager']);
            return $instance;
        });

        // deployers

        $c['spread.deployer'] = $c->share(static function ($c): object {
            $instance = new $c['spread.deployer.class'](
                $c['timeline_manager'],
                $c['spread.entry_collection'],
                $c['spread.on_subject'],
                $c['spread.batch_size']
            );
            $instance->setDelivery($c['spread.delivery']);
            return $instance;
        });

        $c['spread.entry_collection'] = $c->share(static fn ($c): object => new $c['spread.entry_collection.class'](
            $c['spread.on_global_context'],
            $c['spread.batch_size']
        ));

        $this->container = $c;
    }

    public function addRedisDriver($client): void
    {
        $c = $this->container;

        $c['timeline_manager.class']  = TimelineManager::class;
        $c['action_manager.class']    = ActionManager::class;
        $c['query_executor.class']    = QueryExecutor::class;
        $c['pager.class']             = Pager::class;
        $c['redis.prefix']            = 'spy_timeline';
        $c['redis.pipeline']          = true;
        $c['class.action']            = Action::class;
        $c['class.component']         = Component::class;
        $c['class.action_component']  = ActionComponent::class;
        $c['class.component_data_resolver'] = BasicComponentDataResolver::class;
        $c['redis.client'] = $client;

        $c['timeline_manager'] = $c->share(static fn ($c): object => new $c['timeline_manager.class'](
            $c['redis.client'],
            $c['result_builder'],
            $c['redis.prefix'],
            $c['redis.pipeline']
        ));

        $c['action_manager'] = $c->share(static function ($c): object {
            $instance = new $c['action_manager.class'](
                $c['redis.client'],
                $c['result_builder'],
                $c['redis.prefix'],
                $c['class.action'],
                $c['class.component'],
                $c['class.action_component']
            );
            $instance->setDeployer($c['spread.deployer']);
            $instance->setComponentDataResolver($c['class.component_data_resolver']);
            return $instance;
        });

        $c['query_executor'] = $c->share(static fn ($c): object => new $c['query_executor.class'](
            $c['redis.client'],
            $c['redis.prefix']
        ));

        $c['pager'] = $c->share(static fn ($c): object => new $c['pager.class'](
            $c['redis.client'],
            $c['redis.prefix']
        ));

        $this->container = $c;
    }

    /**
     * @return \Pimple
     */
    public function getContainer()
    {
        return $this->container;
    }
}
