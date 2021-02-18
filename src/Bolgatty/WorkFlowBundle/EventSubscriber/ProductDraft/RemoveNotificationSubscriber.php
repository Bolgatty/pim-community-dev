<?php
namespace Bolgatty\WorkFlowBundle\EventSubscriber\ProductDraft;

use Bolgatty\WorkFlowBundle\Component\Event\EntityWithValuesDraftEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Bolgatty\WorkFlowBundle\Entity\ProductModelDraft;
use Symfony\Component\EventDispatcher\GenericEvent;


/**
 * Send a notification to the reviewer when a proposal is removed *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class RemoveNotificationSubscriber extends AbstractProposalStateNotificationSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EntityWithValuesDraftEvents::POST_REMOVE => ['sendNotificationForRemoval', 10],
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function sendNotificationForRemoval(GenericEvent $event)
    {
        // if (!$this->isEventValid($event)) {
        //     return;
        // }

        $type = $event->getArgument('isPartial') ? 'partial_remove' : 'remove';
        $messageInfos = $this->buildNotificationMessageInfos($event, $type);

        $this->send($event, $messageInfos);
    }

    /**
     * {@inheritdoc}
     */
    protected function send(GenericEvent $event, array $messageInfos)
    {
        $productDraft = $event->getSubject();
        $user = $this->userContext->getUser();

        // if (null === $user || !$this->authorWantToBeNotified($productDraft)) {
        //     return;
        // }

        $message = isset($messageInfos['message'])
            ? $messageInfos['message']
            : 'bolgatty_workflow.product_draft.notification.remove';

        $notification = $this->notificationFactory->create();
        $notification
            ->setType('error')
            ->setMessage($message)
            ->setRoute($productDraft instanceof ProductModelDraft ? 'pim_enrich_product_model_edit' : 'pim_enrich_product_edit')
            ->setRouteParams(['id' => $productDraft->getEntityWithValue()->getId()]);

        $options = [
            'messageParams' => [
                '%product%' => $productDraft->getEntityWithValue()->getLabel($this->userContext->getCurrentLocaleCode()),
                '%owner%'   => sprintf('%s %s', $user->getFirstName(), $user->getLastName()),
            ],
            'context' => [
                'actionType'       => 'bolgatty_workflow_product_draft_notification_remove',
                'showReportButton' => false,
            ]
        ];

        $options = array_replace_recursive($options, $messageInfos);
        $notification
            ->setMessageParams($options['messageParams'])
            ->setContext($options['context']);

        if ($event->hasArgument('comment')) {
            $notification->setComment($event->getArgument('comment'));
        }

        $this->notifier->notify($notification, [$productDraft->getAuthor()]);
    }
}
