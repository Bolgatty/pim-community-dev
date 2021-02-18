<?php
namespace Bolgatty\WorkFlowBundle\Presenter\ReferenceData;

/**
 * Present changes on a collection of reference data
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class ReferenceDataCollectionPresenter extends AbstractReferenceDataPresenter
{
    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return 'pim_reference_data_multiselect' === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        if (null === $data) {
            return [];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        $result = [];
        $repository = $this->repositoryResolver->resolve($change['reference_data_name']);
        $references = $repository->findBy(['code' => $change['data']]);

        foreach ($references as $reference) {
            $result[] = (string) $reference;
        }

        return $result;
    }
}
