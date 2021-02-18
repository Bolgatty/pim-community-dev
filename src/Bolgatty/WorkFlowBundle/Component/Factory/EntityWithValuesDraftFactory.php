<?php
namespace Bolgatty\WorkFlowBundle\Component\Factory;

use Bolgatty\WorkFlowBundle\Entity\EntityWithValuesDraftInterface;

/**
 * EntityWithValues factory interface
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
interface EntityWithValuesDraftFactory
{
    /**
     * Creates an entity with values draft instance.
     * @return EntityWithValuesDraftInterface|null
     */
    public function createEntityWithValueDraft($entityWithValues, $draftSource);
}
