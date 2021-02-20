<?php
namespace Bolgatty\WorkFlowBundle\Presenter\ReferenceData;

/**
 * Present changes on reference data
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class ReferenceDataPresenter extends AbstractReferenceDataPresenter
{
    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return 'pim_reference_data_simpleselect' === $attributeType;
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
        $repository = $this->repositoryResolver->resolve($change['reference_data_name']);

        return (string) $repository->findOneBy(['code' => $change['data']]);
    }
}
