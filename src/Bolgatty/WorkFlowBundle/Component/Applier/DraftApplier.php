<?php
namespace Bolgatty\WorkFlowBundle\Component\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
// use Bolgatty\WorkFlowBundle\Component\Event\EntityWithValuesDraftEvents;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Apply a draft
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class DraftApplier implements DraftApplierInterface
{
    /** @var PropertySetterInterface */
    protected $propertySetter;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    public function __construct(
        PropertySetterInterface $propertySetter,
        EventDispatcherInterface $dispatcher,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->propertySetter = $propertySetter;
        $this->dispatcher = $dispatcher;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAllChanges(
        $entityWithValues,
        $entityWithValuesDraft
    ): void {
        // $this->dispatcher->dispatch(EntityWithValuesDraftEvents::PRE_APPLY, new GenericEvent($entityWithValuesDraft));

        $changes = $entityWithValuesDraft->getChanges();
        if (!isset($changes['values'])) {
            return;
        }

        $this->applyValues($entityWithValues, $changes['values']);

        // $this->dispatcher->dispatch(EntityWithValuesDraftEvents::POST_APPLY, new GenericEvent($entityWithValuesDraft));
    }

    /**
     * {@inheritdoc}
     */
    public function applyToReviewChanges(
        $entityWithValues,
        $entityWithValuesDraft
    ): void {
        // $this->dispatcher->dispatch(EntityWithValuesDraftEvents::PRE_APPLY, new GenericEvent($entityWithValuesDraft));

        $changes = $entityWithValuesDraft->getChangesToReview();
        if (!isset($changes['values'])) {
            return;
        }

        $this->applyValues($entityWithValues, $changes['values']);

        // $this->dispatcher->dispatch(EntityWithValuesDraftEvents::POST_APPLY, new GenericEvent($entityWithValuesDraft));
    }

    protected function applyValues(EntityWithValuesInterface $entityWithValues, array $changesValues): void
    {
        foreach ($changesValues as $code => $values) {
            if ($this->attributeExists((string) $code)) {
                foreach ($values as $value) {
                    $this->propertySetter->setData(
                        $entityWithValues,
                        $code,
                        $value['data'],
                        ['locale' => $value['locale'], 'scope' => $value['scope']]
                    );
                }
            }
        }
    }

    protected function attributeExists(string $code): bool
    {
        return null !== $this->attributeRepository->findOneByIdentifier($code);
    }
}
