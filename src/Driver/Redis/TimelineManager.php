<?php

namespace Spy\Timeline\Driver\Redis;

use Spy\Timeline\Driver\Redis\Pager\PagerToken;
use Predis\Pipeline\PipelineContext;
use Predis\Pipeline\Pipeline;
use Spy\Timeline\Driver\TimelineManagerInterface;
use Spy\Timeline\Model\ActionInterface;
use Spy\Timeline\Model\ComponentInterface;
use Spy\Timeline\Model\TimelineInterface;
use Spy\Timeline\ResultBuilder\ResultBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimelineManager implements TimelineManagerInterface
{
    /**
     * @var array
     */
    protected $persistedDatas = [];

    /**
     * @param ResultBuilderInterface $resultBuilder resultBuilder
     * @param string                 $prefix        prefix
     * @param boolean                $pipeline      pipeline
     */
    public function __construct(protected object $client, protected ResultBuilderInterface $resultBuilder, protected string $prefix, protected bool $pipeline = true)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeline(ComponentInterface $subject, array $options = [])
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(['page'         => 1, 'max_per_page' => 10, 'type'         => TimelineInterface::TYPE_TIMELINE, 'context'      => 'GLOBAL', 'filter'       => true, 'paginate'     => false]);

        $options = $resolver->resolve($options);

        $token   = new PagerToken($this->getRedisKey($subject, $options['context'], $options['type']));

        return $this->resultBuilder->fetchResults($token, $options['page'], $options['max_per_page'], $options['filter'], $options['paginate']);
    }

    /**
     * {@inheritdoc}
     */
    public function countKeys(ComponentInterface $subject, array $options = [])
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(['type'    => TimelineInterface::TYPE_TIMELINE, 'context' => 'GLOBAL']);

        $options = $resolver->resolve($options);

        $redisKey = $this->getRedisKey($subject, $options['context'], $options['type']);

        return $this->client->zCard($redisKey);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ComponentInterface $subject, $actionId, array $options = []): void
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(['type'    => TimelineInterface::TYPE_TIMELINE, 'context' => 'GLOBAL']);

        $options = $resolver->resolve($options);

        $redisKey = $this->getSubjectRedisKey($subject);

        $this->persistedDatas[] = ['zRem', $redisKey, $actionId];
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll(ComponentInterface $subject, array $options = []): void
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(['type'    => TimelineInterface::TYPE_TIMELINE, 'context' => 'GLOBAL']);

        $options = $resolver->resolve($options);

        $redisKey = $this->getRedisKey($subject, $options['context'], $options['type']);

        $this->persistedDatas[] = ['del', $redisKey];
    }

    /**
     * {@inheritdoc}
     */
    public function createAndPersist(ActionInterface $action, ComponentInterface $subject, $context = 'GLOBAL', $type = TimelineInterface::TYPE_TIMELINE): void
    {
        $redisKey = $this->getRedisKey($subject, $context, $type);

        $this->persistedDatas[] = ['zAdd', $redisKey, $action->getSpreadTime(), $action->getId()];

        // we want to deploy on a subject action list to enable ->getSubjectActions feature..
        if ('timeline' === $type) {
            $redisKey = $this->getSubjectRedisKey($action->getSubject());

            $this->persistedDatas[] = ['zAdd', $redisKey, $action->getSpreadTime(), $action->getId()];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        if ($this->persistedDatas === []) {
            return [];
        }

        $client  = $this->client;
        $replies = [];

        if ($this->pipeline) {
            $client = $client->pipeline();
        }

        foreach ($this->persistedDatas as $persistData) {
            $replies[] = match ($persistData[0]) {
                'del' => $client->del($persistData[1]),
                'zAdd' => $client->zAdd($persistData[1], $persistData[2], $persistData[3]),
                'zRem' => $client->zRem($persistData[1], $persistData[2]),
                default => throw new \OutOfRangeException('This function is not supported'),
            };
        }

        if ($this->pipeline) {
            //Predis as a specific way to flush pipeline.
            $replies = $client instanceof PipelineContext || $client instanceof Pipeline ? $client->execute() : $client->exec();
        }

        $this->persistedDatas = [];

        return $replies;
    }

    /**
     * @param ComponentInterface $subject subject
     * @param string             $type    type
     * @param string             $context context
     */
    protected function getRedisKey(ComponentInterface $subject, $type, $context): string
    {
        return sprintf('%s:%s:%s:%s', $this->prefix, $subject->getHash(), $type, $context);
    }

    /**
     * @param ComponentInterface $subject subject
     */
    protected function getSubjectRedisKey(ComponentInterface $subject): string
    {
        return sprintf('%s:%s', $this->prefix, $subject->getHash());
    }
}
