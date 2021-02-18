<?php
namespace Bolgatty\WorkFlowBundle\Component\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Bolgatty\WorkFlowBundle\Entity\DraftSource;
use Bolgatty\WorkFlowBundle\Entity\ProductDraft;

/**
 * Product product draft factory
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class ProductDraftFactory implements EntityWithValuesDraftFactory
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function createEntityWithValueDraft($product, $draftSource)
    {
        $fullProduct = $this->productRepository->find($product->getId());

        $productDraft = new ProductDraft();
        $productDraft
            ->setEntityWithValue($fullProduct)
            ->setAuthor($draftSource->getAuthor())
            ->setAuthorLabel($draftSource->getAuthorLabel())
            ->setSource($draftSource->getSource())
            ->setSourceLabel($draftSource->getSourceLabel())
            ->setCreatedAt(new \DateTime());

        return $productDraft;
    }
}
