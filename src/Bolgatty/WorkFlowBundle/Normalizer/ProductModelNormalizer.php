<?php
namespace Bolgatty\WorkFlowBundle\Normalizer;

use Bolgatty\WorkFlowBundle\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Bolgatty\WorkFlowBundle\Component\Applier\DraftApplierInterface;
use Bolgatty\WorkFlowBundle\Component\Authorization\PermissionChecker;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Product model normalizer
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class ProductModelNormalizer implements NormalizerInterface, SerializerAwareInterface, CacheableSupportsMethodInterface
{
    /** @var SerializerInterface */
    private $serializer;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var EntityWithValuesDraftRepositoryInterface */
    private $draftRepository;

    /** @var DraftApplierInterface */
    private $draftApplier;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var IdentifiableObjectRepositoryInterface */
    private $productModelRepository;

    /** @var PermissionChecker */
    protected $permissionChecker;

    public $parentProductModelRepo;

    public function __construct(
        NormalizerInterface $normalizer,
        EntityWithValuesDraftRepositoryInterface $draftRepository,
        DraftApplierInterface $draftApplier,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $productModelRepository
    ) {
        $this->normalizer = $normalizer;
        $this->draftRepository = $draftRepository;
        $this->draftApplier = $draftApplier;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->productModelRepository = $productModelRepository;
    }
    public function setPermissionChecker(PermissionChecker $permissionChecker)
    {
        $this->permissionChecker = $permissionChecker;
    }
    // public function setProductModelRepository($parentProductModelRepo)
    // {
    //     $this->parentProductModelRepo = $parentProductModelRepo;
    // }
    /**
     * {@inheritdoc}
     */
    public function normalize($productModel, $format = null, array $context = [])
    {
        $workingCopy = $this->productModelRepository->findOneByIdentifier($productModel->getCode());
        $normalizedWorkingCopy = $this->normalizer->normalize($workingCopy, 'standard', $context);
        $draftStatus = null;
        
        $user    = $this->tokenStorage->getToken()->getUser();
        $isOwner = $this->permissionChecker->isAuthorizeforProposals($user->getRoles());
      
        $canEdit = true;

        if (!$isOwner && $canEdit) {
            $username = $this->tokenStorage->getToken()->getUsername();
            $draft = $this->draftRepository->findUserEntityWithValuesDraft($productModel, $username);
            if (null !== $draft) {
                $draftStatus = $draft->getStatus();
                $this->draftApplier->applyAllChanges($productModel, $draft);
            }
        }

        // $parentProductModel = $this->parentProductModelRepo->findOneByIdentifier($productModel->getCode());
        $normalizedProductModel = $this->normalizer->normalize($productModel, 'internal_api', $context);
        // dump("parent model :", $normalizedProductModel);
        
        $meta = [
            'is_owner' => $isOwner,
            'working_copy' => $normalizedWorkingCopy,
            'draft_status' => $draftStatus,
        ];
        if (!$isOwner && !$canEdit) {
            $meta['required_missing_attributes'] = [];
        }

        $normalizedProductModel['meta'] = array_merge($normalizedProductModel['meta'], $meta);

        // dump("reached here", $normalizedProductModel);die;
        return $normalizedProductModel;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $this->normalizer->supportsNormalization($data, $format);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return $this->normalizer instanceof CacheableSupportsMethodInterface
            && $this->normalizer->hasCacheableSupportsMethod();
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
