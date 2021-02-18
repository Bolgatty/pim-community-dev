<?php
namespace Bolgatty\WorkFlowBundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Bolgatty\WorkFlowBundle\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Bolgatty\WorkFlowBundle\Component\Factory\PimUserDraftSourceFactory;
use Bolgatty\WorkFlowBundle\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Bolgatty\WorkFlowBundle\Component\Merger\MergeDataOnProductModel;
use Bolgatty\WorkFlowBundle\Component\Authorization\PermissionChecker;


/**
 * Delegating product model saver, depending on context it delegates to other savers to deal with drafts or working copies
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class DelegatingProductModelSaver implements SaverInterface, BulkSaverInterface
{
    /** @var SaverInterface */
    private $productModelSaver;

    /** @var SaverInterface */
    private $productModelDraftSaver;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var EntityWithValuesDraftBuilderInterface */
    private $draftBuilder;

    /** @var RemoverInterface */
    private $productDraftRemover;

    /** @var MergeDataOnProductModel */
    private $mergeDataOnProductModel;

    /** @var IdentifiableObjectRepositoryInterface */
    private $productModelRepository;

    /** @var EntityWithValuesDraftRepositoryInterface */
    private $productModelDraftRepository;

    /** @var ObjectManager */
    private $objectManager;

    /** @var BulkSaverInterface */
    private $bulkProductModelSaver;

    /** @var PimUserDraftSourceFactory */
    private $draftSourceFactory;

    /** @var PermissionChecker */
    protected $permissionChecker;

    public function __construct(
        ObjectManager $objectManager,
        SaverInterface $productModelSaver,
        SaverInterface $productModelDraftSaver,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        EntityWithValuesDraftBuilderInterface $draftBuilder,
        RemoverInterface $productDraftRemover,
        MergeDataOnProductModel $mergeDataOnProductModel,
        ProductModelRepositoryInterface $productModelRepository,
        EntityWithValuesDraftRepositoryInterface $productModelDraftRepository,
        BulkSaverInterface $bulkProductModelSaver,
        PimUserDraftSourceFactory $draftSourceFactory
    ) {
        $this->objectManager = $objectManager;
        $this->productModelSaver = $productModelSaver;
        $this->productModelDraftSaver = $productModelDraftSaver;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->draftBuilder = $draftBuilder;
        $this->productDraftRemover = $productDraftRemover;
        $this->mergeDataOnProductModel = $mergeDataOnProductModel;
        $this->productModelRepository = $productModelRepository;
        $this->productModelDraftRepository = $productModelDraftRepository;
        $this->bulkProductModelSaver = $bulkProductModelSaver;
        $this->draftSourceFactory = $draftSourceFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save($filteredProductModel, array $options = []): void
    {
        if (!$filteredProductModel instanceof ProductModelInterface) {
            throw InvalidObjectException::objectExpected($filteredProductModel, ProductModelInterface::class);
        }

        $fullProductModel = $this->getFullProductModel($filteredProductModel);

        if ($this->isOwner($fullProductModel) || null === $fullProductModel->getId()) {
            $this->productModelSaver->save($fullProductModel, $options);
        } elseif ($this->canEdit($fullProductModel)) {
            $this->saveProductModelDraft($fullProductModel, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $filteredProductModels, array $options = []): void
    {
        if (empty($filteredProductModels)) {
            return;
        }

        $productModelsToCompute = [];
        $fullProductModels = [];
        foreach ($filteredProductModels as $filteredProductModel) {
            $this->validateObject($filteredProductModel, ProductModelInterface::class);

            $fullProductModel = $this->getFullProductModel($filteredProductModel);
            $fullProductModels[] = $fullProductModel;
            
            if ($this->isOwner($fullProductModel) || null === $fullProductModel->getId()) {
                $productModelsToCompute[] = $fullProductModel;
            } elseif ($this->canEdit($fullProductModel)) {
                $this->saveProductModelDraft($fullProductModel, $options);
            }
        }

        if (null !== $this->bulkProductModelSaver) {
            $this->bulkProductModelSaver->saveAll($productModelsToCompute, $options);
        }
        $this->objectManager->flush();
    }
    public function setPermissionChecker(PermissionChecker $permissionChecker)
    {
        $this->permissionChecker = $permissionChecker;
    }

    /**
     * Is user owner of the product model?
     *
     * @param ProductModelInterface $product
     *
     * @return bool
     */
    protected function isOwner(ProductModelInterface $product)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        
        return $this->permissionChecker->isAuthorizeforProposals($user->getRoles());
    }

    /**
     * Can user edit the product model?
     *
     * @param ProductModelInterface $productModel
     *
     * @return bool
     */
    private function canEdit(ProductModelInterface $productModel): bool
    {
        return true;
    }

    /**
     * @param ProductModelInterface $fullProductModel
     * @param array                 $options
     */
    private function saveProductModelDraft(ProductModelInterface $fullProductModel, array $options): void
    {
        $username = $this->tokenStorage->getToken()->getUser()->getUsername();
        $productModelDraft = $this->draftBuilder->build($fullProductModel,
            $this->draftSourceFactory->createFromUser($this->tokenStorage->getToken()->getUser())
        );
        if (null !== $productModelDraft) {
            $this->productModelDraftSaver->save($productModelDraft, $options);
        } elseif (null !== $draft = $this->productModelDraftRepository->findUserEntityWithValuesDraft($fullProductModel, $username)) {
          
            $this->productDraftRemover->remove($draft);
        }
    }

    /**
     * $filteredProductModel is the product model with only granted data.
     * To avoid to lose data, we have to send to the save the full product model with all data (included not granted).
     * To do that, we get the product model from the DB and merge new data from $filteredProductModel into this product model.
     *
     * @param ProductModelInterface $filteredProductModel
     *
     * @return ProductModelInterface
     */
    private function getFullProductModel(ProductModelInterface $filteredProductModel): ProductModelInterface
    {
        if (null === $filteredProductModel->getId()) {
            return $filteredProductModel;
        }

        $fullProductModel = $this->productModelRepository->findOneByIdentifier($filteredProductModel->getCode());

        return $this->mergeDataOnProductModel->merge($filteredProductModel, $fullProductModel);
    }

    /**
     * Raises an exception when we try to save another object than expected
     *
     * @param object $object
     * @param string $expectedClass
     *
     * @throws \InvalidArgumentException
     */
    private function validateObject($object, $expectedClass): void
    {
        if (!$object instanceof $expectedClass) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a %s, "%s" provided',
                    $expectedClass,
                    ClassUtils::getClass($object)
                )
            );
        }
    }
}
