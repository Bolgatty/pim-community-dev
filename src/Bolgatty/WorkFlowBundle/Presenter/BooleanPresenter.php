<?php
namespace Bolgatty\WorkFlowBundle\Presenter;

use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * Present changes on boolean data
 *
 * @author firoj ahmad <firojahmad07@gmail.com>
 */
class BooleanPresenter extends AbstractProductValuePresenter implements TranslatorAwareInterface
{
    use TranslatorAware;

    /** @staticvar string */
    const YES = 'Yes';

    /** @staticvar string */
    const NO = 'No';

    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return AttributeTypes::BOOLEAN === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        return $this->translator->trans($data['data'] ? self::YES : self::NO);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        return $this->translator->trans($change['data'] ? self::YES : self::NO);
    }
}
