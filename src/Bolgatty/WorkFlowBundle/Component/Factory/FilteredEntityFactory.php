<?php

namespace Bolgatty\WorkFlowBundle\Component\Factory;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;

/**
 * Create a filtered entity (meaning with only granted data) from the entity.
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class FilteredEntityFactory
{
    /**
     * @param mixed $fullEntity     *
     * @return mixed
     */
    public function create($fullEntity)
    {
        return $fullEntity;
    }
}
