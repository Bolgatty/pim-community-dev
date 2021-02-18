<?php
namespace Bolgatty\WorkFlowBundle\Manager;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Bolgatty\WorkFlowBundle\Component\Applier\DraftApplierInterface;
use Bolgatty\WorkFlowBundle\Component\Event\EntityWithValuesDraftEvents;
use Bolgatty\WorkFlowBundle\Component\Exception\DraftNotReviewableException;
use Bolgatty\WorkFlowBundle\Component\Factory\EntityWithValuesDraftFactory;
use Bolgatty\WorkFlowBundle\Component\Factory\PimUserDraftSourceFactory;
use Bolgatty\WorkFlowBundle\Entity\DraftSource;
use Bolgatty\WorkFlowBundle\Entity\EntityWithValuesDraftInterface;
use Bolgatty\WorkFlowBundle\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;


/**
 * Manage entity drafts
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */

class EntityWithValuesDraftManager
{
    /** @var SaverInterface */
    protected $workingCopySaver;

    /** @var UserContext */
    protected $userContext;

    /** @var EntityWithValuesDraftFactory */
    protected $factory;

    /** @var */
    protected $repository;

    /** @var DraftApplierInterface */
    protected $applier;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var SaverInterface */
    protected $draftSaver;

    /** @var RemoverInterface */
    protected $draftRemover;

    /** @var CollectionFilterInterface */
    protected $valuesFilter;

    /** @var PimUserDraftSourceFactory */
    private $draftSourceFactory;

    public function __construct(
        SaverInterface $workingCopySaver,
        UserContext $userContext,
        EntityWithValuesDraftFactory $factory,
        $repository,
        DraftApplierInterface $applier,
        EventDispatcherInterface $dispatcher,
        SaverInterface $draftSaver,
        RemoverInterface $draftRemover,
        CollectionFilterInterface $valuesFilter,
        PimUserDraftSourceFactory $draftSourceFactory
    ) {
        $this->workingCopySaver = $workingCopySaver;
        $this->userContext = $userContext;
        $this->factory = $factory;
        $this->repository = $repository;
        $this->applier = $applier;
        $this->dispatcher = $dispatcher;
        $this->draftSaver = $draftSaver;
        $this->draftRemover = $draftRemover;
        $this->valuesFilter = $valuesFilter;
        $this->draftSourceFactory = $draftSourceFactory;
    }

    /**
     * Approve a single "ready to review" change of the given $entityWithValuesDraft.
     * This approval is only applied if current user have edit rights on the change.
     *
     * To do that we create a temporary draft that contains the change that we want to apply,
     * then we apply this temporary draft and remove this change from the original one.
     *
     * @param EntityWithValuesDraftInterface $entityWithValuesDraft
     * @param AttributeInterface             $attribute
     * @param LocaleInterface|null           $locale
     * @param ChannelInterface|null          $channel
     * @param array                          $context      ['comment' => string|null]
     *
     * @throws DraftNotReviewableException If the $entityWithValuesDraft is not ready to be reviewed or if no permission
     *                                     to approve the given change.
     */
    public function approveChange(
        EntityWithValuesDraftInterface $entityWithValuesDraft,
        AttributeInterface $attribute,
        LocaleInterface $locale = null,
        ChannelInterface $channel = null,
        array $context = []
    ): void {
        // $this->dispatcher->dispatch(EntityWithValuesDraftEvents::PRE_PARTIAL_APPROVE, new GenericEvent($entityWithValuesDraft, $context));

        if (EntityWithValuesDraftInterface::READY !== $entityWithValuesDraft->getStatus()) {
            throw new DraftNotReviewableException('A draft not in ready state can not be partially approved');
        }

        $localeCode = null !== $locale ? $locale->getCode() : null;
        $channelCode = null !== $channel ? $channel->getCode() : null;

        $data = $entityWithValuesDraft->getChange($attribute->getCode(), $localeCode, $channelCode);
        $filteredValues = $this->valuesFilter->filterCollection(
            [
                $attribute->getCode() => [['locale' => $localeCode, 'scope' => $channelCode, 'data' => $data]]
            ],
            'pim.internal_api.attribute.edit'
        );

        if (empty($filteredValues)) {
            throw new DraftNotReviewableException('Impossible to approve a single change without permission on it');
        }

        $partialDraft = $this->createDraft($entityWithValuesDraft, $filteredValues);
        $this->applyDraftOnEntity($partialDraft);
        $this->removeDraftChanges($entityWithValuesDraft, $filteredValues);

        $context['updatedValues'] = $filteredValues;

        // $this->dispatcher->dispatch(
        //     EntityWithValuesDraftEvents::POST_PARTIAL_APPROVE,
        //     new GenericEvent($entityWithValuesDraft, $context)
        // );
    }

    /**
     * Approve all "ready to review" changes of the given $entityWithValuesDraft.
     * This approval is only applied if current user have edit rights on the change, so if
     * not all changes can be approved, a "partial approval" is done instead.
     *
     * @param EntityWithValuesDraftInterface $entityWithValuesDraft
     * @param array                          $context      ['comment' => string|null]
     *
     * @throws DraftNotReviewableException If the $entityWithValuesDraft is not ready to be reviewed.
     */
    public function approve(EntityWithValuesDraftInterface $entityWithValuesDraft, array $context = []): void
    {
        // $this->dispatcher->dispatch(EntityWithValuesDraftEvents::PRE_APPROVE, new GenericEvent($entityWithValuesDraft, $context));

        // if (EntityWithValuesDraftInterface::READY !== $entityWithValuesDraft->getStatus()) {
        //     throw new DraftNotReviewableException('A draft not in ready state can not be approved');
        // }

        $draftChanges = $entityWithValuesDraft->getChangesToReview();
        $filteredValues = $this->valuesFilter->filterCollection(
            $draftChanges['values'],
            'pim.internal_api.attribute.edit'
        );

        $isPartial = ($filteredValues != $draftChanges['values']);

        if (!empty($filteredValues)) {
            $draftToApply = $isPartial ? $this->createDraft($entityWithValuesDraft, $filteredValues) : $entityWithValuesDraft;

            $this->applyDraftOnEntity($draftToApply);
            $this->removeDraftChanges($entityWithValuesDraft, $filteredValues);
        }

        $context['updatedValues'] = $filteredValues;
        $context['originalValues'] = $draftChanges['values'];
        $context['isPartial'] = $isPartial;

        $this->dispatcher->dispatch(EntityWithValuesDraftEvents::POST_APPROVE, new GenericEvent($entityWithValuesDraft, $context));
    }

    /**
     * Refuse a single "ready to review" change of the given $entityWithValuesDraft.
     * This refusal is only applied if current user have edit rights on the change.
     *
     * @param EntityWithValuesDraftInterface $entityWithValuesDraft
     * @param AttributeInterface             $attribute
     * @param LocaleInterface|null           $locale
     * @param ChannelInterface|null          $channel
     * @param array                          $context      ['comment' => string|null]
     *
     * @throws DraftNotReviewableException If the $entityWithValuesDraft is not ready to be reviewed or if no permission to
     *                                     refuse the given change.
     */
    public function refuseChange(
        EntityWithValuesDraftInterface $entityWithValuesDraft,
        AttributeInterface $attribute,
        LocaleInterface $locale = null,
        ChannelInterface $channel = null,
        array $context = []
    ): void {
        // $this->dispatcher->dispatch(EntityWithValuesDraftEvents::PRE_PARTIAL_REFUSE, new GenericEvent($entityWithValuesDraft, $context));

        // if (EntityWithValuesDraftInterface::READY !== $entityWithValuesDraft->getStatus()) {
        //     throw new DraftNotReviewableException('A draft not in ready state can not be partially rejected');
        // }

        $localeCode = null !== $locale ? $locale->getCode() : null;
        $channelCode = null !== $channel ? $channel->getCode() : null;

        $filteredValues = $this->valuesFilter->filterCollection(
            [
                $attribute->getCode() => [['locale'  => $localeCode, 'scope' => $channelCode]]
            ],
            'pim.internal_api.attribute.edit'
        );
        if (empty($filteredValues)) {
            throw new DraftNotReviewableException('Impossible to refuse a single change without permission on it');
        }

        $this->refuseDraftChanges($entityWithValuesDraft, $filteredValues);

        $context['updatedValues'] = $filteredValues;

        // $this->dispatcher->dispatch(
        //     EntityWithValuesDraftEvents::POST_PARTIAL_REFUSE,
        //     new GenericEvent($entityWithValuesDraft, $context)
        // );
    }

    /**
     * Refuse all "ready to review" changes of the given $entityWithValuesDraft.
     * This refusal is only applied if current user have edit rights on the change, so if
     * not all changes can be refused, a "partial refusal" is done instead.
     *
     * @param EntityWithValuesDraftInterface $entityWithValuesDraft
     * @param array                          $context
     *
     * @throws DraftNotReviewableException If the $entityWithValuesDraft is not ready to be reviewed.
     */
    public function refuse(EntityWithValuesDraftInterface $entityWithValuesDraft, array $context = []): void
    {
        // $this->dispatcher->dispatch(EntityWithValuesDraftEvents::PRE_REFUSE, new GenericEvent($entityWithValuesDraft, $context));

        // if (EntityWithValuesDraftInterface::READY !== $entityWithValuesDraft->getStatus()) {
        //     throw new DraftNotReviewableException('A draft not in ready state can not be rejected');
        // }

        $draftChanges    = $entityWithValuesDraft->getChangesToReview();
        $filteredValues = $this->valuesFilter->filterCollection(
            $draftChanges['values'],
            'pim.internal_api.attribute.edit'
        );
      
        if (!empty($filteredValues)) {
            $this->refuseDraftChanges($entityWithValuesDraft, $filteredValues);
        }

        $context['updatedValues'] = $filteredValues;
        $context['originalValues'] = $draftChanges['values'];
        $context['isPartial'] = ($filteredValues != $draftChanges['values']);

        $this->dispatcher->dispatch(EntityWithValuesDraftEvents::POST_REFUSE, new GenericEvent($entityWithValuesDraft, $context));
    }

    /**
     * Remove an in progress entity with values draft.
     * This removal is only applied if current user have edit rights on the change, so if
     * not all changes can be removed, a "partial removal" is done instead.
     *
     * @param EntityWithValuesDraftInterface $entityWithValuesDraft
     * @param array                          $context
     *
     * @throws DraftNotReviewableException If the $entityWithValuesDraft is not in progress or if no permission to remove the draft.
     */
    public function remove(EntityWithValuesDraftInterface $entityWithValuesDraft, array $context = []): void
    {
        // $this->dispatcher->dispatch(EntityWithValuesDraftEvents::PRE_REMOVE, new GenericEvent($entityWithValuesDraft, $context));

        // if (EntityWithValuesDraftInterface::READY === $entityWithValuesDraft->getStatus()) {
        //     throw new DraftNotReviewableException('A draft in ready state can not be removed');
        // }

        $draftChanges = $entityWithValuesDraft->getChangesByStatus(EntityWithValuesDraftInterface::CHANGE_DRAFT);
        $filteredValues = $this->valuesFilter->filterCollection(
            $draftChanges['values'],
            'pim.internal_api.attribute.edit'
        );

        // if (empty($filteredValues)) {
        //     throw new DraftNotReviewableException('Impossible to delete a draft if no permission at all on it');
        // }

        $isPartial = ($filteredValues != $draftChanges['values']);

        if (!$isPartial) {
            $this->draftRemover->remove($entityWithValuesDraft);
        } else {
            $this->removeDraftChanges($entityWithValuesDraft, $filteredValues);
        }

        $context['updatedValues'] = $filteredValues;
        $context['originalValues'] = $draftChanges['values'];
        $context['isPartial'] = $isPartial;

        // $this->dispatcher->dispatch(EntityWithValuesDraftEvents::POST_REMOVE, new GenericEvent($entityWithValuesDraft, $context));
    }

    // /**
    //  * Find or create a draft
    //  *
    //  * @param EntityWithValuesInterface $entityWithValues
    //  *
    //  * @throws \LogicException
    //  *
    //  * @return EntityWithValuesDraftInterface
    //  */
    // public function findOrCreate(EntityWithValuesInterface $entityWithValues): EntityWithValuesDraftInterface
    // {
    //     if (null === $this->userContext->getUser()) {
    //         throw new \LogicException('Current user cannot be resolved');
    //     }
    //     $user = $this->userContext->getUser();
    //     $draft = $this->repository->findUserEntityWithValuesDraft($entityWithValues, $user->getUsername());

    //     if (null === $draft) {
    //         $draft = $this->factory->createEntityWithValueDraft(
    //             $entityWithValues,
    //             $this->draftSourceFactory->createFromUser($user)
    //         );
    //     }

    //     return $draft;
    // }

    /**
     * Mark a draft as ready
     *
     * @param EntityWithValuesDraftInterface $draft
     * @param string                         $comment
     */
    public function markAsReady(EntityWithValuesDraftInterface $draft, $comment = null): void
    {
        // $this->dispatcher->dispatch(EntityWithValuesDraftEvents::PRE_READY, new GenericEvent($draft));
        $draft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);
        $this->draftSaver->save($draft);

        $this->dispatcher->dispatch(
            EntityWithValuesDraftEvents::POST_READY,
            new GenericEvent($draft, ['comment' => $comment])
        );
    }

    /**
     * Create a draft with the given changes.
     *
     * @param EntityWithValuesDraftInterface $draft
     * @param array                          $draftChanges
     *
     * @return EntityWithValuesDraftInterface
     */
    protected function createDraft(
        EntityWithValuesDraftInterface $draft,
        array $draftChanges
    ): EntityWithValuesDraftInterface {
        $draftSource = new DraftSource(
            $draft->getSource(),
            $draft->getSourceLabel(),
            $draft->getAuthor(),
            $draft->getAuthorLabel()
        );
        $partialDraft = $this->factory->createEntityWithValueDraft($draft->getEntityWithValue(), $draftSource);
        $partialDraft->setChanges([
            'values' => $draftChanges
        ]);

        return $partialDraft;
    }

    /**
     * Apply a draft on the related entity with values.
     *
     * @param EntityWithValuesDraftInterface $draft
     */
    protected function applyDraftOnEntity(EntityWithValuesDraftInterface $draft): void
    {
        $entityWithValue = $draft->getEntityWithValue();
        $isPartialDraft = null === $draft->getId();
       
        if ($isPartialDraft) {
            $this->applier->applyAllChanges($entityWithValue, $draft);
        } else {
            $this->applier->applyToReviewChanges($entityWithValue, $draft);
        }

        $this->workingCopySaver->save($entityWithValue);
    }

    /**
     * Refuse changes from a draft. The draft is saved.
     *
     * @param EntityWithValuesDraftInterface $draft
     * @param array                          $refusedChanges
     */
    protected function refuseDraftChanges(
        EntityWithValuesDraftInterface $draft,
        array $refusedChanges
    ): void {
        foreach ($refusedChanges as $attributeCode => $values) {
            foreach ($values as $value) {
                $draft->setReviewStatusForChange(
                    EntityWithValuesDraftInterface::CHANGE_DRAFT,
                    $attributeCode,
                    $value['locale'],
                    $value['scope']
                );
            }
        }
       
        $this->draftSaver->save($draft);
    }

    /**
     * Remove the given changes from a draft and saves it.
     * It the draft has no more changes, it is removed.
     *
     * @param EntityWithValuesDraftInterface $draft
     * @param array                          $appliedChanges
     */
    protected function removeDraftChanges(EntityWithValuesDraftInterface $draft, array $appliedChanges): void
    {
        foreach ($appliedChanges as $attributeCode => $values) {
            foreach ($values as $value) {
                $draft->removeChange((string) $attributeCode, $value['locale'], $value['scope']);
                $valueToRemove = $draft->getValues()->getByCodes(
                    $attributeCode,
                    $value['scope'],
                    $value['locale']
                );
                if (null !== $valueToRemove) {
                    $draft->getValues()->remove($valueToRemove);
                }
            }
        }

        if (!$draft->hasChanges()) {
            $this->draftRemover->remove($draft);
        } else {
            $this->draftSaver->save($draft);
        }
    }
}
