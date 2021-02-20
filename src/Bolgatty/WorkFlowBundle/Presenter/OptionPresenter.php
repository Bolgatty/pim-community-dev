<?php
namespace Bolgatty\WorkFlowBundle\Presenter;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * Present changes on option data
 */
class OptionPresenter extends AbstractProductValuePresenter
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
        $option = $this->optionRepository->findOneByIdentifier(
            $change['attribute'].'.'.$formerData
        );

        return $this->renderer->renderDiff(
            $this->normalizeData($option),
            $this->normalizeChange($change)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return AttributeTypes::OPTION_SIMPLE_SELECT === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        return (string) $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        if (null === $change['data']) {
            return null;
        }

        $identifier = sprintf('%s.%s', $change['attribute'], $change['data']);

        return (string) $this->optionRepository->findOneByIdentifier($identifier);
    }
}
