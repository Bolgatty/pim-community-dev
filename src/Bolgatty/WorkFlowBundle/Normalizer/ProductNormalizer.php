<?php

namespace Bolgatty\WorkFlowBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\MissingRequiredAttributesCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\MissingRequiredAttributesNormalizerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Bolgatty\WorkFlowBundle\Component\Applier\DraftApplierInterface;
use Bolgatty\WorkFlowBundle\Entity\EntityWithValuesDraftInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Bolgatty\WorkFlowBundle\Component\Authorization\PermissionChecker;

/**
 * Product normalizer
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class ProductNormalizer implements NormalizerInterface, SerializerAwareInterface, CacheableSupportsMethodInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var  */
    protected $draftRepository;

    /** @var DraftApplierInterface */
    protected $draftApplier;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var MissingRequiredAttributesCalculator */
    protected $missingRequiredAttributesCalculator;

    /** @var MissingRequiredAttributesNormalizerInterface */
    protected $missingRequiredAttributesNormalizer;

    /** @var PermissionChecker */
    protected $permissionChecker;

    public function __construct(
        NormalizerInterface $normalizer,
        $draftRepository,
        DraftApplierInterface $draftApplier,
        TokenStorageInterface $tokenStorage,
        ProductRepositoryInterface $productRepository,
        MissingRequiredAttributesCalculator $missingRequiredAttributesCalculator,
        MissingRequiredAttributesNormalizerInterface $missingRequiredAttributesNormalizer
    ) {
        $this->normalizer       = $normalizer;
        $this->draftRepository  = $draftRepository;
        $this->draftApplier     = $draftApplier;
        $this->tokenStorage     = $tokenStorage;
        $this->productRepository = $productRepository;
        $this->missingRequiredAttributesCalculator = $missingRequiredAttributesCalculator;
        $this->missingRequiredAttributesNormalizer = $missingRequiredAttributesNormalizer;

    }
    public function setPermissionChecker(PermissionChecker $permissionChecker)
    {
        $this->permissionChecker = $permissionChecker;
    }
    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $id = $product->getId();
        $workingCopy = $this->productRepository->find($id);
        $normalizedWorkingCopy = $this->normalizer->normalize($workingCopy, 'standard', $context);
        $draftStatus = null;
        $user = $this->tokenStorage->getToken()->getUser();

        $isOwner = $this->permissionChecker->isAuthorizeforProposals($user->getRoles());
      

        if (null !== $draft = $this->findDraftForProduct($product)) {
            $draftStatus = $draft->getStatus();
            $this->draftApplier->applyAllChanges($product, $draft);
        }

        $normalizedProduct = $this->normalizer->normalize($product, 'internal_api', $context);

        $canEdit = null;
        $meta = [
            'published'     => null,
            'owner_groups'  => null,
            'is_owner'      => $isOwner ? $isOwner : null,
            'working_copy'  => $normalizedWorkingCopy,
            'draft_status'  => $draftStatus
        ];

        // if a draft is ongoing, we have to recompute the missing required attributes based on the draft values
        if (null !== $draftStatus) {
            $completenesses = $this->missingRequiredAttributesCalculator->fromEntityWithFamily($product);
            $meta['required_missing_attributes'] = $this->missingRequiredAttributesNormalizer->normalize($completenesses);
        } elseif (!$isOwner && !$canEdit) {
            $meta['required_missing_attributes'] = [];
        }

        $normalizedProduct['meta'] = array_merge($normalizedProduct['meta'], $meta);

        return $normalizedProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && $format === 'internal_api';
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Find a product draft for the specified product
     *
     * @param ProductInterface $product
     *
     * @return EntityWithValuesDraftInterface|null
     */
    protected function findDraftForProduct(ProductInterface $product)
    {
        return $this->draftRepository->findUserEntityWithValuesDraft($product, $this->getUsername());
    }

    /**
     * Return the current username
     *
     * @return string
     */
    protected function getUsername()
    {
        return $this->tokenStorage->getToken()->getUsername();
    }

    /**
     * Filters the 'missing required attributes' based on the user's permissions
     */
    private function filterMissingRequiredAttributes(array $requiredMissingAttributes): array
    {
        $filteredRequiredMissingAttributes = [];

        foreach ($requiredMissingAttributes as $index => $missingForChannel) {
            $filteredRequiredMissingAttributes[$index] = [
                'channel' => $missingForChannel['channel'],
            ];

            foreach ($missingForChannel['locales'] as $localeCode => $missingForLocale) {
                $filteredRequiredMissingAttributes[$index]['locales'][$localeCode] = [
                    'missing' => $missingForLocale['missing'],
                ];
            }
        }

        return $filteredRequiredMissingAttributes;
    }
}
