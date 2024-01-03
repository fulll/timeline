<?php

namespace Spy\Timeline\Model;

interface TimelineInterface
{
    /**
     * @var string
     */
    public const TYPE_TIMELINE = 'timeline';

    /**
     * {@inheritdoc}
     */
    public function setId($id);

    /**
     * {@inheritdoc}
     */
    public function getId();

    /**
     * @param  string            $context
     * @return TimelineInterface
     */
    public function setContext($context);

    /**
     * @return string
     */
    public function getContext();

    /**
     * @param  string            $type
     * @return TimelineInterface
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getType();

    /**
     * @return TimelineInterface
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * @return TimelineInterface
     */
    public function setSubject(ComponentInterface $subject);

    /**
     * @return ComponentInterface
     */
    public function getSubject();

    /**
     * @return TimelineInterface
     */
    public function setAction(ActionInterface $action);

    /**
     * @return ActionInterface
     */
    public function getAction();
}
