<?php
namespace Bolgatty\WorkFlowBundle\Datagrid\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Bolgatty\WorkFlowBundle\Entity\ProductDraft;
use Bolgatty\WorkFlowBundle\Entity\ProductModelDraft;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Proposal product model normalizer for datagrid
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class ProductModelProposalNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var NormalizerInterface */
    private $standardNormalizer;

    /** @var NormalizerInterface */
    private $datagridNormlizer;

    /** @var ValueFactory */
    private $valueFactory;

    /** @var GetAttributes */
    private $getAttributesQuery;

    public function __construct(
        NormalizerInterface $standardNormalizer,
        NormalizerInterface $datagridNormlizer,
        ValueFactory $valueFactory,
        GetAttributes $getAttributesQuery
    ) {
        $this->standardNormalizer = $standardNormalizer;
        $this->datagridNormlizer = $datagridNormlizer;
        $this->valueFactory = $valueFactory;
        $this->getAttributesQuery = $getAttributesQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($proposalModelProduct, $format = null, array $context = []): array
    {
        $data = [];
        $data['changes'] = $this->standardNormalizer->normalize(
            $this->getValueCollectionFromChanges($proposalModelProduct),
            'standard',
            $context
        );
        $data['createdAt'] = $this->datagridNormlizer->normalize($proposalModelProduct->getCreatedAt(), $format, $context);
        $data['product'] = $proposalModelProduct->getEntityWithValue();
        $data['author'] = $proposalModelProduct->getAuthor();
        $data['author_label'] = $proposalModelProduct->getAuthorLabel();
        $data['source'] = $proposalModelProduct->getSource();
        $data['source_label'] = $proposalModelProduct->getSourceLabel();
        $data['status'] = $proposalModelProduct->getStatus();
        $data['proposal'] = $proposalModelProduct;
        $data['search_id'] = $proposalModelProduct->getEntityWithValue()->getCode();
        $data['id'] = 'product_model_draft_' . (string) $proposalModelProduct->getId();
        $data['document_type'] = 'product_model_draft';
        $data['proposal_id'] = $proposalModelProduct->getId();

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductModelDraft && 'datagrid' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * During the fetch of the Draft, the ValueCollectionFactory will remove the empty values. As empty values are
     * filtered in the raw values, deleted values are not rendered properly for the proposal.
     * As the ValueCollectionFactory is used for the Draft too, the $rawValues does not contains empty values anymore.
     * This implies that the proposal are not correctly displayed in the datagrid if you use the $rawValues.
     * So, instead of using the $rawValues, we recalculate the values to display from the $changes field.
     *
     * https://github.com/akeneo/pim-community-dev/issues/10083
     *
     * @param ProductDraft $proposal
     *
     * @return WriteValueCollection
     */
    private function getValueCollectionFromChanges(ProductModelDraft $proposal): WriteValueCollection
    {
        $changes = $proposal->getChanges();
        $valueCollection = new WriteValueCollection();
        foreach ($changes['values'] as $code => $changeset) {
            $attribute = $this->getAttributesQuery->forCode($code);
            foreach ($changeset as $index => $change) {
                if ("draft" != $changes['review_statuses'][$code][$index]['status']){
                    $value = $this->valueFactory->createByCheckingData(
                        $attribute,
                        $change['scope'],
                        $change['locale'],
                        $change['data']
                    );
                    $valueCollection->add($value);
                }
            }
        }

        return $valueCollection;
    }
}