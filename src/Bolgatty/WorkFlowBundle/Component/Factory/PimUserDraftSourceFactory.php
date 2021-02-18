<?php

namespace Bolgatty\WorkFlowBundle\Component\Factory;

use Bolgatty\WorkFlowBundle\Entity\DraftSource;
use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class PimUserDraftSourceFactory
{
    const PIM_SOURCE_CODE = 'pim';
    const PIM_SOURCE_LABEL = 'PIM';

    public function createFromUser(UserInterface $user): DraftSource
    {
        return new DraftSource(
            self::PIM_SOURCE_CODE,
            self::PIM_SOURCE_LABEL,
            $user->getUsername(),
            $user->getFullName()
        );
    }
}
