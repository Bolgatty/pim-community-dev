<?php

namespace Bolgatty\WorkFlowBundle\Presenter;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface as BasePresenterInterface;

/**
 * Present change on metric data
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class MetricPresenter extends AbstractProductValuePresenter implements TranslatorAwareInterface
{
    use TranslatorAware;

    /** @var BasePresenterInterface */
    protected $metricPresenter;

    /** @var LocaleResolver */
    protected $localeResolver;

    public function __construct(
        BasePresenterInterface $metricPresenter,
        LocaleResolver $localeResolver
    ) {
        $this->metricPresenter = $metricPresenter;
        $this->localeResolver = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return AttributeTypes::METRIC === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        if (null === $data) {
            return '';
        }

        $options = ['locale' => $this->localeResolver->getCurrentLocale()];
        $structuredMetric = ['amount' => $data->getData(), 'unit' => $data->getUnit()];

        return $this->metricPresenter->present($structuredMetric, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        $options = ['locale' => $this->localeResolver->getCurrentLocale()];

        return $this->metricPresenter->present($change['data'], $options);
    }
}
