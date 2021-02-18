<?php
namespace Bolgatty\WorkFlowBundle\Datagrid\Filter;

use Bolgatty\WorkFlowBundle\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility as BaseFilterUtility;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Datasource\FilterProductDatasourceAdapterInterface;

/**
 * ProductDraft filter utility
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class ProductDraftFilterUtility extends BaseFilterUtility
{
    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $repository;

    /**
     * Constructor
     *
     * @param EntityWithValuesDraftRepositoryInterface $repository
     */
    public function __construct(EntityWithValuesDraftRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Apply filter
     *
     * @param FilterDatasourceAdapterInterface $ds
     * @param string                           $field
     * @param string                           $operator
     * @param mixed                            $value
     */
    public function applyFilter(FilterDatasourceAdapterInterface $ds, $field, $operator, $value)
    {
        if ($ds instanceof FilterProductDatasourceAdapterInterface) {
            $ds->getProductQueryBuilder()->addFilter($field, $operator, $value);
        } else {
            $this->repository->applyFilter($ds->getQueryBuilder(), $field, $operator, $value);
        }
    }
}
