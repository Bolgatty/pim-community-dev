<?php

namespace Bolgatty\WorkFlowBundle\Datagrid\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\AjaxChoiceFilter;

/**
 * Extends ChoiceFilter in order to use a different operator that check an attribute code exists in the values
 * keys of a product draft changes, ensuring that a product contains at least one change on that attribute.
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class AttributeChoiceFilter extends AjaxChoiceFilter
{
    /**
     * {@inheritdoc}
     */
    protected function getOperator($type)
    {
        $operator = parent::getOperator($type);

        if (Operators::IN_LIST === $operator) {
            return Operators::IN_ARRAY_KEYS;
        }

        return $operator;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);

        if (null === $data) {
            return false;
        }

        $field    = $this->get(ProductDraftFilterUtility::DATA_NAME_KEY);
        $operator = $this->getOperator($data['type']);
        $value    = $data['value'];

        $this->util->applyFilter($ds, $field, $operator, $value);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormOptions()
    {
        // return array_merge(
        //     parent::getFormOptions(),
        //     ['choice_url' => 'pimee_workflow_product_draft_rest_attribute_choice']
        // );

        return parent::getFormOptions();
    }
}
