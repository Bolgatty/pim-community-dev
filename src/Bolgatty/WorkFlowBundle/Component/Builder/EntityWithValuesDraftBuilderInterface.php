<?php
namespace  Bolgatty\WorkFlowBundle\Component\Builder;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Bolgatty\WorkFlowBundle\Entity\DraftSource;
use Bolgatty\WorkFlowBundle\Entity\EntityWithValuesDraftInterface;

/**
 * EntityWithValues draft builder interface
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
interface EntityWithValuesDraftBuilderInterface
{
    /**
     * @param EntityWithValuesInterface $entityWithValues
     * @param DraftSource $draftSource
     * @return EntityWithValuesDraftInterface|null returns null if no draft is created
     */
    public function build(EntityWithValuesInterface $entityWithValues, DraftSource $draftSource): ?EntityWithValuesDraftInterface;
}
