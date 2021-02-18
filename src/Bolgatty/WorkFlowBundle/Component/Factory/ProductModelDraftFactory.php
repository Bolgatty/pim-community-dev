<?php
namespace Bolgatty\WorkFlowBundle\Component\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Bolgatty\WorkFlowBundle\Entity\DraftSource;
use Bolgatty\WorkFlowBundle\Entity\EntityWithValuesDraftInterface;
use Bolgatty\WorkFlowBundle\Entity\ProductModelDraft;

/**
 * Product model draft factory
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */

class ProductModelDraftFactory implements EntityWithValuesDraftFactory
{
    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /**
     * @param ProductModelRepositoryInterface $productModelRepository
     */
    public function __construct(ProductModelRepositoryInterface $productModelRepository)
    {
        $this->productModelRepository = $productModelRepository;
    }

    public function createEntityWithValueDraft($productModel, $draftSource)
    {
        $fullProductModel = $this->productModelRepository->find($productModel->getId());

        $productModelDraft = new ProductModelDraft();
        $productModelDraft
            ->setEntityWithValue($fullProductModel)
            ->setAuthor($draftSource->getAuthor())
            ->setAuthorLabel($draftSource->getAuthorLabel())
            ->setSource($draftSource->getSource())
            ->setSourceLabel($draftSource->getSourceLabel())
            ->setCreatedAt(new \DateTime());

        return $productModelDraft;
    }
}
