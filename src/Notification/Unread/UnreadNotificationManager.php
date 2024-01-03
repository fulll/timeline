<?php

namespace Spy\Timeline\Notification\Unread;

use Spy\Timeline\Driver\TimelineManagerInterface;
use Spy\Timeline\Model\ActionInterface;
use Spy\Timeline\Model\ComponentInterface;
use Spy\Timeline\Notification\NotifierInterface;
use Spy\Timeline\Spread\Entry\EntryCollection;

class UnreadNotificationManager implements NotifierInterface
{
    /**
     * @param TimelineManagerInterface $timelineManager timelineManager
     */
    public function __construct(protected TimelineManagerInterface $timelineManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function notify(ActionInterface $action, EntryCollection $entryCollection): void
    {
        $i = 0;
        foreach ($entryCollection as $context => $entries) {
            foreach ($entries as $entry) {
                ++$i;
                $this->timelineManager->createAndPersist($action, $entry->getSubject(), $context, 'notification');
            }
        }

        if ($i > 0) {
            $this->timelineManager->flush();
        }
    }

    /**
     * @param ComponentInterface $subject The subject
     * @param string             $context The context
     * @param array              $options An array of options (offset, limit), see your timelineManager
     *
     * @return array
     */
    public function getUnreadNotifications(ComponentInterface $subject, $context = "GLOBAL", array $options = [])
    {
        $options['context'] = $context;
        $options['type']    = 'notification';

        return $this->timelineManager->getTimeline($subject, $options);
    }

    /**
     * count how many timeline had not be read
     *
     * @param ComponentInterface $subject The subject
     * @param string             $context The context
     *
     * @return integer
     */
    public function countKeys(ComponentInterface $subject, $context = "GLOBAL")
    {
        $options = ['context' => $context, 'type'    => 'notification'];

        return $this->timelineManager->countKeys($subject, $options);
    }

    /**
     * @param ComponentInterface $subject          The subject
     * @param string             $timelineActionId The actionId
     * @param string             $context          The context
     */
    public function markAsReadAction(ComponentInterface $subject, $timelineActionId, $context = 'GLOBAL'): void
    {
        $this->markAsReadActions([[$context, $subject, $timelineActionId]]);
    }

    /**
     * Give an array like this
     * array(
     *   array( *CONTEXT*, *SUBJECT*, *KEY* )
     *   array( *CONTEXT*, *SUBJECT*, *KEY* )
     *   ....
     * )
     */
    public function markAsReadActions(array $actions): void
    {
        $options = ['type' => 'notification'];

        foreach ($actions as $action) {
            [$context, $subject, $actionId] = $action;

            $options['context'] = $context;

            $this->timelineManager->remove($subject, $actionId, $options);
        }

        $this->timelineManager->flush();
    }

    /**
     * markAllAsRead
     *
     * @param ComponentInterface $subject subject
     * @param string             $context The context
     */
    public function markAllAsRead(ComponentInterface $subject, $context = "GLOBAL"): void
    {
        $options = ['context' => $context, 'type'    => 'notification'];

        $this->timelineManager->removeAll($subject, $options);
        $this->timelineManager->flush();
    }
}
