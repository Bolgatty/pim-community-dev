<?php
namespace Bolgatty\WorkFlowBundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductUniqueDataSynchronizer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Bolgatty\WorkFlowBundle\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Bolgatty\WorkFlowBundle\Entity\EntityWithValuesDraftInterface;
use Bolgatty\WorkFlowBundle\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Bolgatty\WorkFlowBundle\Component\Authorization\PermissionChecker;

/**
 * Delegating product saver, depending on context it delegates to other savers to deal with drafts or working copies
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class DelegatingProductSaver implements SaverInterface, BulkSaverInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var EntityWithValuesDraftBuilderInterface */
    protected $entityWithValuesDraftBuilder;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var EntityWithValuesDraftRepositoryInterface */
    protected $productDraftRepo;

    /** @var RemoverInterface */
    protected $productDraftRemover;

    /** @var ProductUniqueDataSynchronizer */
    private $uniqueDataSynchronizer;

    /** @var  */
    private $mergeDataOnProduct;

    /** @var  */
    private $productRepository;

    /** @var */
    private $draftSourceFactory;

    /** @var PermissionChecker */
    protected $permissionChecker;
    
    /**
     * @param ObjectManager $objectManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param EntityWithValuesDraftBuilderInterface $entityWithValuesDraftBuilder
     * @param TokenStorageInterface $tokenStorage
     * @param EntityWithValuesDraftRepositoryInterface $productDraftRepo
     * @param RemoverInterface $productDraftRemover
     * @param ProductUniqueDataSynchronizer $uniqueDataSynchronizer
     * @param                               $mergeDataOnProduct
     * @param                               $productRepository
     * @param                               $draftSourceFactory
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        AuthorizationCheckerInterface $authorizationChecker,
        EntityWithValuesDraftBuilderInterface $entityWithValuesDraftBuilder,
        TokenStorageInterface $tokenStorage,
        EntityWithValuesDraftRepositoryInterface $productDraftRepo,
        RemoverInterface $productDraftRemover,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        $mergeDataOnProduct,
        $productRepository,
        $draftSourceFactory
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->authorizationChecker = $authorizationChecker;
        $this->entityWithValuesDraftBuilder = $entityWithValuesDraftBuilder;
        $this->tokenStorage = $tokenStorage;
        $this->productDraftRepo = $productDraftRepo;
        $this->productDraftRemover = $productDraftRemover;
        $this->uniqueDataSynchronizer = $uniqueDataSynchronizer;
        $this->mergeDataOnProduct = $mergeDataOnProduct;
        $this->productRepository = $productRepository;
        $this->draftSourceFactory = $draftSourceFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @throws AuthenticationCredentialsNotFoundException if not authenticated
     */
    public function save($filteredProduct, array $options = [])
    {
        $this->validateObject($filteredProduct, ProductInterface::class);
        $fullProduct = $this->getFullProduct($filteredProduct);
       
        if ($this->isOwner($fullProduct) || null === $fullProduct->getId()) {
            $this->saveProduct($fullProduct, $options);
        } elseif ($this->canEdit($fullProduct)) {
            $this->saveProductDraft($fullProduct, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $filteredProducts, array $options = [])
    {
        if (empty($filteredProducts)) {
            return;
        }

        $productsToCompute = [];
        $fullProducts = [];
        foreach ($filteredProducts as $filteredProduct) {
            $this->validateObject($filteredProduct, ProductInterface::class);

            $fullProduct = $this->getFullProduct($filteredProduct);
            $fullProducts[] = $fullProduct;

            if ($this->isOwner($fullProduct) || null === $fullProduct->getId()) {
                $productsToCompute[] = $fullProduct;
                $this->saveProduct($fullProduct, $options, false);
            } elseif ($this->canEdit($fullProduct)) {
                $this->saveProductDraft($fullProduct, $options, false);
            }
        }

        $this->objectManager->flush();

        foreach ($productsToCompute as $product) {
            $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($product, $options));
        }

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, new GenericEvent($fullProducts, $options));
    }

    /**
     * Raises an exception when we try to save another object than expected
     *
     * @param object $object
     * @param string $expectedClass
     *
     * @throws \InvalidArgumentException
     */
    protected function validateObject($object, $expectedClass)
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
    public function setPermissionChecker(PermissionChecker $permissionChecker)
    {
        $this->permissionChecker = $permissionChecker;
    }

    /**
     * Is user owner of the product?
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function isOwner(ProductInterface $product)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        
        return $this->permissionChecker->isAuthorizeforProposals($user->getRoles());
    }

    /**
     * Can user edit the product?
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function canEdit(ProductInterface $product)
    {
        return true;

        // return $this->authorizationChecker->isGranted(Attributes::EDIT, $product);
    }

    /**
     * @return string
     */
    protected function getUsername()
    {
        return $this->tokenStorage->getToken()->getUser()->getUsername();
    }

    /**
     * @param ProductInterface $fullProduct
     * @param array            $options
     * @param bool|true        $withFlush
     */
    protected function saveProduct(ProductInterface $fullProduct, array $options, $withFlush = true)
    {
        $options['unitary'] = true;
        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($fullProduct, $options));

        $this->uniqueDataSynchronizer->synchronize($fullProduct);

        $this->objectManager->persist($fullProduct);
        if ($withFlush) {
            $this->objectManager->flush();
            $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($fullProduct, $options));
        }
    }

    /**
     * @param ProductInterface $fullProduct
     * @param array            $options
     * @param bool|true        $withFlush
     */
    protected function saveProductDraft(ProductInterface $fullProduct, array $options, $withFlush = true)
    {
        $productDraft = $this->entityWithValuesDraftBuilder->build(
            $fullProduct,
            $this->draftSourceFactory->createFromUser($this->tokenStorage->getToken()->getUser())
        );
        
        if (null !== $productDraft) {
            $this->validateObject($productDraft, EntityWithValuesDraftInterface::class);
            $options['unitary'] = true;
            $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($productDraft, $options));
            $this->objectManager->persist($productDraft);

            if ($withFlush) {
                $this->objectManager->flush();
                $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($productDraft, $options));
                $this->objectManager->refresh($fullProduct);
            }
        } elseif (null !== $draft = $this->productDraftRepo->findUserEntityWithValuesDraft($fullProduct, $this->getUsername())) {
            $this->productDraftRemover->remove($draft);
        }
    }

    /**
     * $filteredProduct is the product with only granted data.
     * To avoid to lose data, we have to send to the save the full product with all data (included not granted).
     * To do that, we get the product from the DB and merge new data from $filteredProduct into this product.
     *
     * @param ProductInterface $filteredProduct
     *
     * @return ProductInterface
     */
    private function getFullProduct(ProductInterface $filteredProduct): ProductInterface
    {
        if (null === $filteredProduct->getId()) {
            return $this->mergeDataOnProduct->merge($filteredProduct);
        }

        $fullProduct = $this->productRepository->find($filteredProduct->getId());
        
        return $this->mergeDataOnProduct->merge($filteredProduct, $fullProduct);
    }
}
