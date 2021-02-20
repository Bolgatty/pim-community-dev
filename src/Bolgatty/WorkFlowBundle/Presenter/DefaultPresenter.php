<?php
namespace Bolgatty\WorkFlowBundle\Presenter;

/**
 * Present data without pre-transformation
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class DefaultPresenter extends AbstractProductValuePresenter
{
    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        return $change['data'];
    }
}
