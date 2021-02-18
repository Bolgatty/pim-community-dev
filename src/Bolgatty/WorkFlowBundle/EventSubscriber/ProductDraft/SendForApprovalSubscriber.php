<?php
namespace Bolgatty\WorkFlowBundle\EventSubscriber\ProductDraft;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Bolgatty\WorkFlowBundle\Component\Event\EntityWithValuesDraftEvents;
use Bolgatty\WorkFlowBundle\Entity\EntityWithValuesDraftInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * This subscriber listens to entity with values draft submission for approval.
 * This way, we can send notifications to the right users.
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class SendForApprovalSubscriber implements EventSubscriberInterface
{
    const NOTIFICATION_TYPE = 'bolgatty_workflow_product_draft_notification_new_proposal';

    /** @var NotifierInterface */
    protected $notifier;

    /** @var UserRepositoryInterface */
    protected $userRepository;

    /** @var SimpleFactoryInterface */
    protected $notificationFactory;

    public function __construct(
        NotifierInterface $notifier,
        UserRepositoryInterface $userRepository,
        SimpleFactoryInterface $notificationFactory
    ) {
        $this->notifier = $notifier;
        $this->userRepository = $userRepository;
        $this->notificationFactory = $notificationFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EntityWithValuesDraftEvents::POST_READY => ['sendNotificationToOwners'],
        ];
    }

    public function sendNotificationToOwners(GenericEvent $event): void
    {
        $entityWithValuesDraft = $event->getSubject();
        $entityWithValue = $entityWithValuesDraft->getEntityWithValue();

        $filters = ['locales' => $this->getChangeToReviewLocales($event->getSubject())];
        
        $author = $this->userRepository->findOneBy(['username' => $entityWithValuesDraft->getAuthor()]);
        $authorCatalogLocale = $author->getCatalogLocale()->getCode();

        $gridParameters = [
            'f' => [
                'author' => [
                    'value' => [
                        $author->getUsername(),
                    ],
                ],
                'identifier'    => [
                    'value' => $entityWithValue instanceof ProductInterface ? $entityWithValue->getIdentifier() : $entityWithValue->getCode(),
                    'type' => 1,
                ],
            ],
        ];

        $notification = $this->notificationFactory->create();
        $notification
            ->setMessage('bolgatty_workflow.proposal.to_review')
            ->setMessageParams(
                [
                    '%product.label%'    => $entityWithValue->getLabel($authorCatalogLocale),
                    '%author.firstname%' => $author->getFirstName(),
                    '%author.lastname%'  => $author->getLastName()
                ]
            )
            ->setType('add')
            ->setRoute('#')
            ->setComment($event->getArgument('comment'))
            ->setContext(
                [
                    'actionType'       => static::NOTIFICATION_TYPE,
                    'showReportButton' => false,
                    'gridParameters'   => http_build_query($gridParameters, 'flags_')
                ]
            );

        $this->notifier->notify($notification, $usersToNotify);
    }

    /**
     * Return the locales on which there is some changes to review.
     * If a change is not localized, returns null.
     *
     * @param EntityWithValuesDraftInterface $entityWithValuesDraft
     * @return string[]
     */
    private function getChangeToReviewLocales(EntityWithValuesDraftInterface $entityWithValuesDraft): ?array
    {
        $changes = $entityWithValuesDraft->getChangesToReview();

        if (!isset($changes['values'])) {
            return null;
        }

        $locales = [];
        foreach ($changes['values'] as $code => $changeset) {
            foreach ($changeset as $index => $change) {
                if ($change['locale'] === null) {
                    return null;
                }

                $locales[$change['locale']] = 1;
            }
        }

        return array_keys($locales);
    }
}
