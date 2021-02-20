<?php

declare(strict_types=1);

namespace Bolgatty\WorkFlowBundle\Datagrid\Filter;

use Bolgatty\WorkFlowBundle\Component\Model\EntityWithValuesDraftInterface;
use Bolgatty\WorkFlowBundle\Component\Query\SelectProductIdsByUserAndDraftStatusQueryInterface;
use Bolgatty\WorkFlowBundle\Component\Query\SelectProductModelIdsByUserAndDraftStatusQueryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class DraftStatusFilter extends ChoiceFilter
{
    const WORKING_COPY = 0;
    const IN_PROGRESS = 1;
    const WAITING_FOR_APPROVAL = 2;

    private $selectProductIdsByUserAndDraftStatusQuery;

    private $selectProductModelIdsByUserAndDraftStatusQuery;

    private $userContext;

    public function __construct(
        FormFactoryInterface $formFactory,
        ProductFilterUtility $filterUtility,
        SelectProductIdsByUserAndDraftStatusQueryInterface $selectProductIdsByUserAndDraftStatusQuery,
        SelectProductModelIdsByUserAndDraftStatusQueryInterface $selectProductModelIdsByUserAndDraftStatusQuery,
        UserContext $userContext
    ) {
        parent::__construct($formFactory, $filterUtility);

        $this->selectProductIdsByUserAndDraftStatusQuery = $selectProductIdsByUserAndDraftStatusQuery;
        $this->selectProductModelIdsByUserAndDraftStatusQuery = $selectProductModelIdsByUserAndDraftStatusQuery;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $filterDatasource, $data)
    {
        $filterValue = isset($data['value']) ? $data['value'] : null;
        if (null === $filterValue) {
            return false;
        }

        switch ($filterValue) {
            case self::WORKING_COPY:
                $draftStatuses = [EntityWithValuesDraftInterface::IN_PROGRESS, EntityWithValuesDraftInterface::READY];
                $operator = 'NOT IN';
                break;
            case self::WAITING_FOR_APPROVAL:
                $draftStatuses = [EntityWithValuesDraftInterface::READY];
                $operator = 'IN';
                break;
            case self::IN_PROGRESS:
                $draftStatuses = [EntityWithValuesDraftInterface::IN_PROGRESS];
                $operator = 'IN';
                break;
            default:
                throw new \LogicException('Expected filter value should be between 0 and 2');
        }

        $user = $this->userContext->getUser();
        if (!$user instanceof UserInterface) {
            throw new \Exception('Draft filter is only useable when user is authenticated');
        }

        $productIds = $this->selectProductIdsByUserAndDraftStatusQuery->execute($user->getUsername(), $draftStatuses);
        $productModelIds = $this->selectProductModelIdsByUserAndDraftStatusQuery->execute($user->getUsername(), $draftStatuses);
        $esIds = $this->prepareIdsForEsFilter($productIds, $productModelIds);
        $esIds = empty($esIds) ? ['null'] : $esIds;

        $this->util->applyFilter($filterDatasource, 'id', $operator, $esIds);

        return true;
    }

    private function prepareIdsForEsFilter(array $productIds, array $productModelIds): array
    {
        $esValueIds = [];
        foreach ($productIds as $productId) {
            $esValueIds[] = 'product_' . $productId;
        }
        foreach ($productModelIds as $productModelId) {
            $esValueIds[] = 'product_model_' . $productModelId;
        }

        return $esValueIds;
    }
}