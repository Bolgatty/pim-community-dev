<?php
namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Normalizer;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product draft normalizer
 *
 * @author F Alpe <filips@akeneo.com>
 */
class ProductDraftNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var array */
    protected $supportedFormats = ['internal_api'];

    /**
     * {@inheritdoc}
     */
    public function normalize($productDraft, $format = null, array $context = [])
    {
        
        return [
            'id'      => $productDraft->getId(),
            'author'  => $productDraft->getAuthor(),
            'created' => $productDraft->getCreatedAt(),
            'changes' => $productDraft->getChanges(),
            'status'  => $productDraft->getStatus()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof EntityWithValuesDraftInterface && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
