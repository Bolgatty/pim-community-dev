<?php
namespace Bolgatty\WorkFlowBundle\Presenter;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface as BasePresenterInterface;

/**
 * Present changes on prices
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class PricesPresenter extends AbstractProductValuePresenter
{
    /** @var BasePresenterInterface */
    protected $pricesPresenter;

    /** @var LocaleResolver */
    protected $localeResolver;

    public function __construct(
        BasePresenterInterface $pricesPresenter,
        LocaleResolver $localeResolver
    ) {
        $this->pricesPresenter = $pricesPresenter;
        $this->localeResolver = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return AttributeTypes::PRICE_COLLECTION === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    public function present($formerData, array $change)
    {
        $value = $this->normalizeData($formerData);
        $change = $this->normalizeChange($change);

        foreach ($value as $currency => $price) {
            if (isset($change[$currency]) && $price === $change[$currency]) {
                unset($change[$currency]);
                unset($value[$currency]);
            }
        }

        foreach ($change as $currency => $price) {
            if ('' === $price) {
                unset($change[$currency]);
            }
        }


        $before = array_values($value);
        $after  = array_values($change);

        return [
            "before" => !empty($before) ? implode("," ,$before): "",
            "after"  => !empty($after) ?  implode(",", $after): "" 
        ];
        
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        $prices = [];
        $options = ['locale' => $this->localeResolver->getCurrentLocale()];
        if (is_array($data)) {
            foreach ($data as $price) {
                $amount = $price->getData();
    
                if (null !== $amount) {
                    $structuredPrice = ['amount' => $amount, 'currency' => $price->getCurrency()];
                    $prices[$price->getCurrency()] = $this->pricesPresenter->present($structuredPrice, $options);
                }
            }
        }

        return $prices;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        $prices = [];
        $options = ['locale' => $this->localeResolver->getCurrentLocale()];

        foreach ($change['data'] as $price) {
            $prices[$price['currency']] = $this->pricesPresenter->present($price, $options);
        }

        return $prices;
    }
}
