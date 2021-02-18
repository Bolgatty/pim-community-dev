<?php
namespace Bolgatty\WorkFlowBundle\Component\Merger;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;

/**
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class MergeDataOnProductModel
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

    public function merge($filteredProductModel, $fullProductModel = null)
    {
        if (!$filteredProductModel instanceof ProductModelInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($filteredProductModel), ProductModelInterface::class);
        }

        $filteredProductModel = $this->setParent($filteredProductModel);

        if (null === $fullProductModel) {
            return $filteredProductModel;
        }

        if (!$fullProductModel instanceof ProductModelInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($fullProductModel), ProductModelInterface::class);
        }

        $fullProductModel->setCode($filteredProductModel->getCode());
        $fullProductModel->setRoot($filteredProductModel->getRoot());
        $fullProductModel->setLeft($filteredProductModel->getLeft());
        $fullProductModel->setRight($filteredProductModel->getRight());
        $fullProductModel->setParent($filteredProductModel->getParent());
        $fullProductModel->setLevel($filteredProductModel->getLevel());
        $fullProductModel->setFamilyVariant($filteredProductModel->getFamilyVariant());

        return $fullProductModel;
    }

    /**
     * Set the parent of the product model.
     * If we want to be able to save correctly the product model, we have to find the full parent known by doctrine.
     *
     * @param ProductModelInterface $filteredProductModel
     *
     * @return ProductModelInterface
     */
    private function setParent(ProductModelInterface $filteredProductModel): ProductModelInterface
    {
        if (null === $filteredProductModel->getParent()) {
            return $filteredProductModel;
        }

        $parent = $this->productModelRepository->find($filteredProductModel->getParent()->getId());
        $filteredProductModel->setParent($parent);

        return $filteredProductModel;
    }
}
