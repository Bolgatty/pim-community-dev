<?php
namespace Bolgatty\WorkFlowBundle\Datagrid\Filter;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter as OroChoiceFilter;

/**
 * Choice filter for product draft
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class ChoiceFilter extends OroChoiceFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);

        if (!$data) {
            return false;
        }

        $field = $this->get(ProductDraftFilterUtility::DATA_NAME_KEY);
        $operator = $this->getOperator($data['type']);
        $value = $data['value'];

        $this->util->applyFilter($ds, $field, $operator, $value);

        return true;
    }
}
