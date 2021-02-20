<?php
namespace Bolgatty\WorkFlowBundle\Presenter;

/**
 * Present change data into HTML
 *
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
