<?php
namespace Bolgatty\WorkFlowBundle\Presenter;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * Present changes on options datasetRenderer
 */
class OptionsPresenter extends AbstractProductValuePresenter
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $optionRepository;

    public function __construct(
        IdentifiableObjectRepositoryInterface $optionRepository
    ) {
        $this->optionRepository = $optionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function present($formerData, array $change)
    {
        $options = [];

        if (is_array($formerData)) {
            foreach ($formerData as $optionCode) {
                $options[] = $this->optionRepository->findOneByIdentifier(
                    $change['attribute'].'.'.$optionCode
                );
            }
        }
        $before = $this->normalizeData($options);
        $after = $this->normalizeData($options);

        return [
            "before" => !empty($this->normalizeData($options)) ? implode("," ,$this->normalizeData($options)): "",
            "after"  => implode(",", $this->normalizeChange($change)) 
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return AttributeTypes::OPTION_MULTI_SELECT === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        $result = [];
        foreach ($data as $option) {
            $result[] = (string) $option;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        if (null === $change['data']) {
            return null;
        }

        $result = [];

        foreach ($change['data'] as $option) {
            $identifier = sprintf('%s.%s', $change['attribute'], $option);
            $result[] = (string) $this->optionRepository->findOneByIdentifier($identifier);
        }

        return $result;
    }
}
