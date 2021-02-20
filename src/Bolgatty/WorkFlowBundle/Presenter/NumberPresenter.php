<?php
namespace Bolgatty\WorkFlowBundle\Presenter;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface as BasePresenterInterface;

/**
 * Present changes on numbers
 */
class NumberPresenter extends AbstractProductValuePresenter
{
    /** @var BasePresenterInterface */
    protected $numberPresenter;

    /** @var LocaleResolver */
    protected $localeResolver;

    public function __construct(
        BasePresenterInterface $numberPresenter,
        LocaleResolver $localeResolver
    ) {
        $this->numberPresenter = $numberPresenter;
        $this->localeResolver = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return AttributeTypes::NUMBER === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        $options = ['locale' => $this->localeResolver->getCurrentLocale()];

        return $this->numberPresenter->present($data, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        $options = ['locale' => $this->localeResolver->getCurrentLocale()];

        return $this->numberPresenter->present($change['data'], $options);
    }
}
