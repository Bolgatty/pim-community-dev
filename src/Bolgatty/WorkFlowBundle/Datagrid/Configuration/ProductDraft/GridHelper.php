<?php
namespace Bolgatty\WorkFlowBundle\Datagrid\Configuration\ProductDraft;

use Bolgatty\WorkFlowBundle\Entity\EntityWithValuesDraftInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Helper for product draft datagrid
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class GridHelper
{
    /** @var AuthorizationCheckerInterface  */
    protected $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface       $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker) 
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Returns callback that will disable approve and refuse buttons given product draft status and permissions
     *
     * @return callable
     */
    public function getActionConfigurationClosure()
    {
        return function (ResultRecordInterface $record) {
            $productDraft = $record->getValue('proposal');

            $canReview = true;
            $canDelete = true;

            $toReview   = $productDraft->getStatus() === EntityWithValuesDraftInterface::READY;
            $inProgress = $productDraft->isInProgress();
            $isOwner    = true;
            // $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $productDraft->getEntityWithValue());

            return [
                'approve' => true,
                'refuse'  => true,
                'remove'  => false
            ];
        };
    }

    /**
     * Returns available product draft status choices
     *
     * @return array
     */
    public function getStatusChoices()
    {
        return [
            EntityWithValuesDraftInterface::IN_PROGRESS => 'pimee_workflow.product_draft.status.in_progress',
            EntityWithValuesDraftInterface::READY       => 'pimee_workflow.product_draft.status.ready',
        ];
    }
}
