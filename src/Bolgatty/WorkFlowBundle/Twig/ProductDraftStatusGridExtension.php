<?php
namespace Bolgatty\WorkFlowBundle\Twig;

use Bolgatty\WorkFlowBundle\Entity\EntityWithValuesDraftInterface;
/**
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class ProductDraftStatusGridExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'get_draft_status_grid',
                [$this, 'getDraftStatusGrid'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
                'get_draft_status_tooltip_grid',
                [$this, 'getDraftStatusTooltipGrid'],
                ['is_safe' => ['html']]
            )
        ];
    }

    /**
     * Get the human readable draft status for the grid
     *
     * @param EntityWithValuesDraftInterface $productDraft
     *
     * @return string
     */
    public function getDraftStatusGrid(EntityWithValuesDraftInterface $productDraft)
    {
        // $toReview       = $productDraft->getStatus() === EntityWithValuesDraftInterface::READY;
        $toReview       = EntityWithValuesDraftInterface::READY;
        $canReview      = true;
        $canDelete      = true;
        $canReviewAll   = true;

        if ($toReview) {
            if ($canReviewAll) {
                return 'bolgatty_workflow.product_draft.status.ready';
            }

            if ($canReview) {
                return 'bolgatty_workflow.product_draft.status.can_be_partially_reviewed';
            }

            return 'bolgatty_workflow.product_draft.status.can_not_be_approved';
        }

        if ($canDelete) {
            return 'bolgatty_workflow.product_draft.status.in_progress';
        }

        return 'bolgatty_workflow.product_draft.status.can_not_be_deleted';
    }

    /**
     * Get the human readable draft status tooltip for the grid
     *
     * @param EntityWithValuesDraftInterface $productDraft
     *
     * @return string
     */
    public function getDraftStatusTooltipGrid(EntityWithValuesDraftInterface $productDraft)
    {
        $toReview = $productDraft->getStatus() === EntityWithValuesDraftInterface::READY;
        $canReview      = true;
        $canDelete      = true;
        $canReviewAll   = true;
        if ($toReview) {
            if ($canReviewAll) {
                return '';
            }

            if ($canReview) {
                return 'bolgatty_workflow.product_draft.status_message.can_be_partially_reviewed';
            }

            return 'bolgatty_workflow.product_draft.status_message.can_not_be_approved';
        }

        if ($canDelete) {
            return 'bolgatty_workflow.product_draft.status_message.in_progress';
        }

        return 'bolgatty_workflow.product_draft.status_message.can_not_be_deleted';
    }
}
