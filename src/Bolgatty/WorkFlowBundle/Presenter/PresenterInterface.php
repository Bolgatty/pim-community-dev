<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolgatty\WorkFlowBundle\Presenter;

/**
 * Present change data into HTML
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
interface PresenterInterface
{
    /**
     * Whether or not this class can present the provided change
     *
     * @param string $attributeType
     *
     * @return bool
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool;

    /**
     * Present the provided change into html
     *
     * @param mixed $formerData
     * @param array $change
     *
     * @return mixed
     */
    public function present($formerData, array $change);
}
