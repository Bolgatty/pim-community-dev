<?php

namespace Bolgatty\WorkFlowBundle\Component\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;

/**
 * Draft applier interface
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
interface DraftApplierInterface
{
    /**
     * Apply all changes on the enity, no matter the review statuses
     *
     * @param EntityWithValuesInterface      $entityWithValues
     * @param EntityWithValuesDraftInterface $entityWithValuesDraft
     */
    public function applyAllChanges(
        EntityWithValuesInterface $entityWithValues,
        EntityWithValuesDraftInterface $entityWithValuesDraft
    ): void;

    /**
     * Apply only changes with the status EntityWithValuesDraftInterface::TO_REVIEW on the entity
     *
     * @param EntityWithValuesInterface      $entityWithValues
     * @param EntityWithValuesDraftInterface $entityWithValuesDraft
     */
    public function applyToReviewChanges(
        EntityWithValuesInterface $entityWithValues,
        EntityWithValuesDraftInterface $entityWithValuesDraft
    ): void;
}
