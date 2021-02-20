<?php
namespace Bolgatty\WorkFlowBundle\Presenter;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface as BasePresenterInterface;

/**
 * Present changes on date data
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class DatePresenter extends AbstractProductValuePresenter
{
    /** @var BasePresenterInterface */
    protected $datePresenter;

    /** @var LocaleResolver */
    protected $localeResolver;

    public function __construct(
        BasePresenterInterface $datePresenter,
        LocaleResolver $localeResolver
    ) {
        $this->datePresenter = $datePresenter;
        $this->localeResolver = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return AttributeTypes::DATE === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        $options = ['locale' => $this->localeResolver->getCurrentLocale()];

        return $this->datePresenter->present($data, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        if (empty($change['data'])) {
            return '';
        }

        $options = ['locale' => $this->localeResolver->getCurrentLocale()];

        return $this->datePresenter->present($change['data'], $options);
    }
}
